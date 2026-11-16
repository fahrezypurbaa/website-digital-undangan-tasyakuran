'use strict';
(()=>{
const reduced=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const cover=document.getElementById('opening');
const seal=document.getElementById('openInvite');
let openingStarted=false;
seal.addEventListener('click',event=>{
  if(openingStarted)return;
  openingStarted=true;
  event.stopImmediatePropagation();
  seal.disabled=true;
  if(reduced){openInvite();return}
  cover.classList.add('is-unsealing');
  window.setTimeout(()=>{openInvite();document.getElementById('home')?.scrollIntoView({block:'start',behavior:'instant'})},1450);
},true);
const progress=document.getElementById('scrollProgress');
let frame=0;
function updateProgress(){frame=0;const max=document.documentElement.scrollHeight-window.innerHeight;progress.style.width=(max>0?Math.min(100,Math.max(0,scrollY/max*100)):0)+'%'}
window.addEventListener('scroll',()=>{if(!frame)frame=requestAnimationFrame(updateProgress)},{passive:true});
window.addEventListener('resize',updateProgress,{passive:true});updateProgress();
const figures=[...document.querySelectorAll('#gallery .gallery-art')];
const dialog=document.getElementById('photoDialog');
const full=document.getElementById('photoFull');
const label=document.getElementById('photoLabel');
let selected=0;
function showPhoto(index){selected=(index+figures.length)%figures.length;const figure=figures[selected];const img=figure.querySelector('img');full.src=img.currentSrc||img.src;full.alt=img.alt;label.textContent=figure.querySelector('figcaption strong')?.textContent||''}
figures.forEach((figure,index)=>{
 figure.tabIndex=0;figure.setAttribute('role','button');figure.setAttribute('aria-label','Perbesar foto: '+(figure.querySelector('figcaption strong')?.textContent||'Foto'));
 const open=()=>{showPhoto(index);if(typeof dialog.showModal==='function')dialog.showModal()};
 figure.addEventListener('click',open);
 figure.addEventListener('keydown',event=>{if(event.key==='Enter'||event.key===' '){event.preventDefault();open()}});
});
document.getElementById('photoClose').addEventListener('click',()=>dialog.close());
document.getElementById('photoPrev').addEventListener('click',()=>showPhoto(selected-1));
document.getElementById('photoNext').addEventListener('click',()=>showPhoto(selected+1));
dialog.addEventListener('click',event=>{if(event.target===dialog)dialog.close()});
dialog.addEventListener('keydown',event=>{if(event.key==='ArrowRight')showPhoto(selected+1);if(event.key==='ArrowLeft')showPhoto(selected-1)});
dialog.addEventListener('close',()=>{full.removeAttribute('src')});
})();
