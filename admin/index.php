<?php
declare(strict_types=1);
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Content-Security-Policy: default-src \'none\'; style-src \'self\' \'unsafe-inline\'; form-action \'self\'; base-uri \'none\'');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
$secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_name('undangan_admin_session');
session_set_cookie_params(['lifetime'=>0,'path'=>'/admin','secure'=>$secure,'httponly'=>true,'samesite'=>'Strict']);
session_start();
function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function verifyCsrf(): bool { return isset($_POST['csrf']) && is_string($_POST['csrf']) && hash_equals(csrf(),$_POST['csrf']); }
function db(): PDO {
  $cfg=require dirname(__DIR__).'/api/config.php';
  return new PDO('mysql:host='.$cfg['host'].';dbname='.$cfg['database'].';charset=utf8mb4',$cfg['username'],$cfg['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false]);
}
$authFile=__DIR__.'/.auth.php';
if (!is_file($authFile)) { http_response_code(503); exit('Dashboard belum dikonfigurasi.'); }
$auth=require $authFile;
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!verifyCsrf()) { http_response_code(403); exit('Permintaan tidak valid.'); }
  $action=(string)($_POST['action']??'');
  if ($action==='logout') { $_SESSION=[]; session_regenerate_id(true); header('Location: /admin/'); exit; }
  if ($action==='login') {
    $now=time(); $attempts=(int)($_SESSION['attempts']??0); $lockedUntil=(int)($_SESSION['locked_until']??0);
    if ($now<$lockedUntil) $error='Terlalu banyak percobaan. Coba lagi beberapa menit.';
    else {
      $user=(string)($_POST['username']??''); $pass=(string)($_POST['password']??'');
      if (strlen($user)>100 || strlen($pass)>200) $error='Login tidak valid.';
      elseif (hash_equals($auth['username'],$user) && password_verify($pass,$auth['password_hash'])) {
        session_regenerate_id(true); $_SESSION['authenticated']=true; $_SESSION['attempts']=0; $_SESSION['locked_until']=0;
        header('Location: /admin/'); exit;
      } else {
        $attempts++; $_SESSION['attempts']=$attempts;
        if ($attempts>=5) { $_SESSION['locked_until']=$now+300; $_SESSION['attempts']=0; }
        $error='Username atau password salah.';
      }
    }
  }
}
$loggedIn=($_SESSION['authenticated']??false)===true;
if (!$loggedIn && $_SERVER['REQUEST_METHOD']==='GET') { /* Login page */ }
if ($loggedIn && isset($_GET['export']) && $_GET['export']==='csv') {
  try {
    $pdo=db(); $stmt=$pdo->query('SELECT guest_name, attendance, guest_count, created_at FROM rsvp_responses ORDER BY created_at DESC, id DESC');
    header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="rsvp-undangan-'.date('Y-m-d').'.csv"');
    $out=fopen('php://output','w'); fwrite($out,"\xEF\xBB\xBF"); fputcsv($out,['Nama Tamu','Kehadiran','Jumlah Tamu','Waktu RSVP']);
    while ($row=$stmt->fetch(PDO::FETCH_NUM)) { foreach ($row as &$value) { $value=(string)$value; if (preg_match('/^[\s]*[=+@\-]/u',$value)) $value="'".$value; } unset($value); fputcsv($out,$row); }
    fclose($out); exit;
  } catch (Throwable $e) { error_log('Admin export: '.$e->getMessage()); http_response_code(500); exit('Gagal mengekspor data.'); }
}
$rows=[]; $stats=['responses'=>0,'present'=>0,'absent'=>0,'guests'=>0]; $total=0; $page=1; $q=''; $dbError=false;
if ($loggedIn) {
  try {
    $pdo=db();
    $stats=$pdo->query("SELECT COUNT(*) responses, COALESCE(SUM(attendance='Hadir'),0) present, COALESCE(SUM(attendance='Tidak hadir'),0) absent, COALESCE(SUM(CASE WHEN attendance='Hadir' THEN guest_count ELSE 0 END),0) guests FROM rsvp_responses")->fetch(PDO::FETCH_ASSOC);
    $q=trim((string)($_GET['q']??'')); $q=mb_substr($q,0,100); $page=max(1,min(100000,(int)($_GET['page']??1)));
    $where=$q!==''?' WHERE guest_name LIKE :q':'';
    $countStmt=$pdo->prepare('SELECT COUNT(*) FROM rsvp_responses'.$where);
    if ($q!=='') $countStmt->bindValue(':q','%'.$q.'%'); $countStmt->execute(); $total=(int)$countStmt->fetchColumn();
    $pages=max(1,(int)ceil($total/25)); $page=min($page,$pages);
    $stmt=$pdo->prepare('SELECT id,guest_name,attendance,guest_count,created_at FROM rsvp_responses'.$where.' ORDER BY created_at DESC,id DESC LIMIT 25 OFFSET '.(($page-1)*25));
    if ($q!=='') $stmt->bindValue(':q','%'.$q.'%'); $stmt->execute(); $rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (Throwable $e) { error_log('Admin dashboard: '.$e->getMessage()); $dbError=true; }
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow,noarchive"><title>Dashboard RSVP | Hidup Kedua</title><link rel="stylesheet" href="/admin/style.css"></head><body>
<?php if (!$loggedIn): ?>
<main class="login-wrap"><section class="login-card"><div class="brand">HIDUP KEDUA <span>MANAGEMENT</span></div><div class="eyebrow">PRIVATE ADMIN ACCESS</div><h1>Masuk Dashboard</h1><p class="muted">Pantau konfirmasi kehadiran tamu undangan.</p><?php if ($error!==''): ?><div class="alert" role="alert"><?=h($error)?></div><?php endif; ?><form method="post" action="/admin/"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="login"><label>Username<input name="username" autocomplete="username" required autofocus></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label><button class="primary" type="submit">Masuk ke Dashboard</button></form><p class="footnote">Akses khusus pengelola undangan.</p></section></main>
<?php else: ?>
<header class="topbar"><div><div class="brand">HIDUP KEDUA <span>MANAGEMENT</span></div><div class="top-sub">PANEL ADMINISTRASI UNDANGAN</div></div><form method="post" action="/admin/"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="logout"><button class="logout" type="submit">Keluar <span aria-hidden="true">↗</span></button></form></header>
<main class="dashboard"><div class="intro"><div><div class="eyebrow">GUEST MANAGEMENT / RSVP</div><h1>Dashboard RSVP</h1><p class="muted">Data konfirmasi kehadiran tersimpan langsung di database undangan.</p></div><a class="export" href="/admin/?export=csv">↓ Unduh CSV</a></div>
<?php if ($dbError): ?><div class="alert" role="alert">Data belum dapat dimuat. Periksa konfigurasi database.</div><?php else: ?>
<div class="stats"><article><div class="stat-label">Total Konfirmasi</div><strong><?=number_format((int)$stats['responses'],0,',','.')?></strong><span>Form RSVP masuk</span></article><article><div class="stat-label">Akan Hadir</div><strong><?=number_format((int)$stats['present'],0,',','.')?></strong><span>Konfirmasi hadir</span></article><article><div class="stat-label">Tidak Hadir</div><strong><?=number_format((int)$stats['absent'],0,',','.')?></strong><span>Konfirmasi berhalangan</span></article><article><div class="stat-label">Estimasi Tamu Hadir</div><strong><?=number_format((int)$stats['guests'],0,',','.')?></strong><span>Termasuk pendamping</span></article></div>
<section class="data-card"><div class="data-head"><div><div class="eyebrow">DAFTAR KONFIRMASI</div><h2>Daftar Tamu</h2><p class="muted"><?=number_format($total,0,',','.')?> hasil ditemukan</p></div><form class="search" method="get" action="/admin/"><input type="search" name="q" value="<?=h($q)?>" placeholder="Cari nama tamu..." aria-label="Cari nama tamu"><button type="submit">Cari</button></form></div><div class="table-scroll"><table><thead><tr><th>No.</th><th>Nama Tamu</th><th>Status Kehadiran</th><th>Jumlah Tamu</th><th>Waktu Konfirmasi</th></tr></thead><tbody><?php if (!$rows): ?><tr><td colspan="5" class="empty">Belum ada konfirmasi RSVP<?= $q!==''?' yang cocok dengan pencarian.':'.' ?></td></tr><?php endif; ?><?php foreach ($rows as $i=>$row): ?><tr><td class="num"><?=($page-1)*25+$i+1?></td><td class="guest-name"><?=h((string)$row['guest_name'])?></td><td><span class="pill <?= $row['attendance']==='Hadir'?'yes':'no' ?>"><?=h((string)$row['attendance'])?></span></td><td><?= (int)$row['guest_count'] ?></td><td class="date"><?=h((string)$row['created_at'])?> WIB</td></tr><?php endforeach; ?></tbody></table></div><div class="data-footer"><span>Halaman <?=$page?> dari <?=$pages?></span><div class="pager"><?php if($page>1): ?><a href="?<?=h(http_build_query(['q'=>$q,'page'=>$page-1]))?>">← Sebelumnya</a><?php endif; ?><?php if($page<$pages): ?><a href="?<?=h(http_build_query(['q'=>$q,'page'=>$page+1]))?>">Berikutnya →</a><?php endif; ?></div></div></section>
<?php endif; ?><footer class="admin-footer">© Hidup Kedua Management · Dashboard RSVP</footer></main>
<?php endif; ?></body></html>
