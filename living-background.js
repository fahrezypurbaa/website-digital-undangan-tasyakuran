// Decorative background only; no interaction, layout changes, or external dependencies.
(() => {
  const targets = document.querySelectorAll('main > .hero, #rundown, #gallery, #couple, #details');
  targets.forEach((section, sectionIndex) => {
    const layer = document.createElement('div');
    layer.className = 'wedding-motion-layer';
    layer.setAttribute('aria-hidden', 'true');
    for (let i = 0; i < 11; i++) {
      const petal = document.createElement('i');
      petal.className = 'wedding-petal';
      petal.style.cssText = `--x:${(i * 37 + sectionIndex * 13) % 96}%;--size:${10 + i % 4 * 4}px;--duration:${11 + i % 5 * 2}s;--delay:-${i * 2.9 + sectionIndex}s;--drift:${i % 2 ? -85 : 90}px`;
      layer.appendChild(petal);
    }
    for (let i = 0; i < 2; i++) {
      const glow = document.createElement('i');
      glow.className = 'wedding-glow';
      glow.style.cssText = `--x:${i ? 65 : -8}%;--y:${i ? 48 : 6}%;--delay:-${i * 5}s`;
      layer.appendChild(glow);
    }
    section.prepend(layer);
  });
})();
