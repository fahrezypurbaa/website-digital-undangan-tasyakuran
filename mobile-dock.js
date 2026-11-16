'use strict';
(()=>{
 const links=[...document.querySelectorAll('.mobile-dock a')];
 const sections=links.map(link=>document.getElementById(link.dataset.section)).filter(Boolean);
 const dock=document.querySelector('.mobile-dock');
 if(!dock)return;
 function update(){
   const offset=window.innerHeight*.36;
   let active=sections[0]?.id;
   for(const section of sections){if(section.getBoundingClientRect().top<=offset)active=section.id}
   links.forEach(link=>{if(link.dataset.section===active)link.setAttribute('aria-current','page');else link.removeAttribute('aria-current')});
 }
 let raf=0;
 const schedule=()=>{if(!raf)raf=requestAnimationFrame(()=>{raf=0;update()})};
 window.addEventListener('scroll',schedule,{passive:true});
 window.addEventListener('resize',schedule,{passive:true});
 links.forEach(link=>link.addEventListener('click',()=>{links.forEach(item=>item.removeAttribute('aria-current'));link.setAttribute('aria-current','page')}));
 update();
})();
