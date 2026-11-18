<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'message'=>'Metode tidak diizinkan']); exit; }
if (!is_file(__DIR__.'/config.php')) { http_response_code(503); echo json_encode(['ok'=>false,'message'=>'RSVP belum dikonfigurasi']); exit; }
$config=require __DIR__.'/config.php';
try {
  $raw=file_get_contents('php://input');
  if (strlen($raw)>4096) throw new InvalidArgumentException('Data terlalu besar');
  $data=json_decode($raw,true,16,JSON_THROW_ON_ERROR);
  $name=trim((string)($data['name']??''));
  $attendance=(string)($data['attendance']??'');
  $phone=trim((string)($data['phone']??''));
  $phoneNormalized=preg_replace('/[\s().-]+/','',$phone);
  $guests=filter_var($data['guests']??null,FILTER_VALIDATE_INT);
  if (!is_string($phoneNormalized) || !preg_match('/^\+?[0-9]{9,15}$/',$phoneNormalized)) throw new InvalidArgumentException('Masukkan nomor telepon yang valid');
  if ($name==='' || mb_strlen($name)>100 || !in_array($attendance,['Hadir','Tidak hadir'],true) || $guests===false || $guests<0 || $guests>5 || ($attendance==='Hadir' && $guests<1) || ($attendance==='Tidak hadir' && $guests!==0)) throw new InvalidArgumentException('Periksa data RSVP');
  $pdo=new PDO('mysql:host='.$config['host'].';dbname='.$config['database'].';charset=utf8mb4',$config['username'],$config['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false]);
  $stmt=$pdo->prepare('INSERT INTO rsvp_responses (guest_name,phone,attendance,guest_count) VALUES (?,?,?,?)');
  $stmt->execute([$name,$phoneNormalized,$attendance,$guests]);
  echo json_encode(['ok'=>true,'message'=>'Konfirmasi berhasil dikirim. Terima kasih!']);
} catch (InvalidArgumentException | JsonException $e) { http_response_code(422); echo json_encode(['ok'=>false,'message'=>$e->getMessage()]); }
catch (Throwable $e) { error_log('RSVP: '.$e->getMessage()); http_response_code(500); echo json_encode(['ok'=>false,'message'=>'Server belum dapat menyimpan konfirmasi.']); }
