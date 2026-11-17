'use strict';
const $=s=>document.querySelector(s);
const address='Taman Sari Persada Raya, Blok M No. 8, Kelurahan Jatibening, Kecamatan Pondok Gede, Kota Bekasi';
const date=new Date('2026-11-22T09:00:00+07:00');
const timeline=[
{time:'09.00–10.00 WIB',icon:'✧',title:'Persiapan',desc:'60 menit'},
{time:'10.00–10.05 WIB',icon:'✦',title:'Pembukaan oleh MC',desc:'5 menit'},
{time:'10.05–10.10 WIB',icon:'❧',title:'Pembacaan Ayat Suci Al-Qur’an',desc:'5 menit'},
{time:'10.10–10.20 WIB',icon:'♡',title:'Sambutan Tuan Rumah',desc:'Bapak Ilham Mendrofa · 10 menit'},
{time:'10.20–10.25 WIB',icon:'✧',title:'Sambutan dari Pihak Tamu',desc:'5 menit'},
{time:'10.25–11.00 WIB',icon:'❧',title:'Ceramah dan Nasihat Pernikahan',desc:'35 menit'},
{time:'11.00–11.05 WIB',icon:'♡',title:'Doa Bersama',desc:'5 menit'},
{time:'11.05–11.08 WIB',icon:'✦',title:'Penutup',desc:'3 menit'},
{time:'Setelah acara',icon:'♧',title:'Dokumentasi dan Makan Siang',desc:'Silaturahmi dan menikmati hidangan'}
];
$('#timeline').innerHTML=timeline.map(x=>'<article class="timeline-item"><div class="timeline-dot"></div><time>'+x.time+'</time><h3>'+x.title+'</h3><p>'+x.desc+'</p></article>').join('');
function tick(){let diff=Math.max(0,date-Date.now());let values=[Math.floor(diff/86400000),Math.floor(diff/3600000)%24,Math.floor(diff/60000)%60,Math.floor(diff/1000)%60];['days','hours','minutes','seconds'].forEach((k,i)=>$('#'+k).textContent=String(values[i]).padStart(2,'0'))}tick();setInterval(tick,1000);
const opening=$('#opening');function openInvite(){opening.classList.add('closed');document.body.classList.remove('locked');sessionStorage.setItem('inviteOpened','1');setTimeout(()=>opening.setAttribute('aria-hidden','true'),1000)}$('#openInvite').addEventListener('click',openInvite);if(sessionStorage.getItem('inviteOpened')==='1')openInvite();
$('#menuToggle').addEventListener('click',()=>{let nav=$('#nav');let open=nav.classList.toggle('open');$('#menuToggle').setAttribute('aria-expanded',String(open))});document.querySelectorAll('#nav a').forEach(a=>a.addEventListener('click',()=>{$('#nav').classList.remove('open');$('#menuToggle').setAttribute('aria-expanded','false')}));
const observer=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');observer.unobserve(e.target)}}),{threshold:.09});document.querySelectorAll('.reveal').forEach(el=>observer.observe(el));
let toastTimer;function toast(msg){let t=$('#toast');t.textContent=msg;t.classList.add('visible');clearTimeout(toastTimer);toastTimer=setTimeout(()=>t.classList.remove('visible'),3200)}
$('#copyAddress').addEventListener('click',async()=>{try{await navigator.clipboard.writeText(address);toast('Alamat berhasil disalin')}catch{toast('Salin alamat dari bagian detail acara')}});
$('#addCalendar').addEventListener('click',()=>{const text='Tasyakuran Pernikahan Dipta & Frengky';const url='https://calendar.google.com/calendar/render?action=TEMPLATE&text='+encodeURIComponent(text)+'&dates=20261122T020000Z/20261122T060000Z&details='+encodeURIComponent('Tasyakuran Pernikahan Dipta & Frengky')+'&location='+encodeURIComponent(address);window.open(url,'_blank','noopener,noreferrer')});
const audio=$('#music');const musicButton=$('#musicButton');audio.src='./assets/background-music.mp3';fetch(audio.src,{method:'HEAD'}).then(r=>{if(r.ok)musicButton.hidden=false}).catch(()=>{});musicButton.addEventListener('click',async()=>{if(audio.paused){try{await audio.play();musicButton.textContent='♫';musicButton.setAttribute('aria-label','Matikan musik')}catch{toast('Musik tidak dapat diputar')}}else{audio.pause();musicButton.textContent='♪';musicButton.setAttribute('aria-label','Putar musik')}});
