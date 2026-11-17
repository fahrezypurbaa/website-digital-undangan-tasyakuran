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
    // Decorative corner filigree, floating pearls and glimmering stars.
    for (let i = 0; i < 2; i++) {
      const floral = document.createElement('i');
      floral.className = `wedding-floral-silhouette wedding-floral-${i ? 'right' : 'left'}`;
      layer.appendChild(floral);
    }
    for (let i = 0; i < 18; i++) {
      const star = document.createElement('i');
      star.className = i % 3 === 0 ? 'wedding-sparkle wedding-sparkle-star' : 'wedding-sparkle';
      star.style.cssText = `--x:${(i * 29 + sectionIndex * 7) % 95}%;--y:${(i * 43 + sectionIndex * 17) % 91}%;--size:${i % 3 === 0 ? 7 : 3 + i % 3 * 2}px;--delay:-${i * .77}s;--duration:${3 + i % 4}s`;
      layer.appendChild(star);
    }
    section.prepend(layer);
  });
})();
