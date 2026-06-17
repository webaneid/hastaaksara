// ─── Header: opaque on scroll ────────────────────────────
(function () {
  var header  = document.querySelector('[data-header]');
  if ( !header ) return;

  var isFront = header.dataset.front === '1';

  function update() {
    // Header selalu merah di semua halaman — hanya shadow yang berubah saat scroll
    header.classList.toggle( 'shadow-md', window.scrollY > 60 );
  }

  window.addEventListener( 'scroll', update, { passive: true } );
  update();
})();

// ─── Hamburger menu ──────────────────────────────────────
(function () {
  const btn     = document.querySelector('[data-hamburger]');
  const menu    = document.querySelector('[data-mobile-menu]');
  const closeBtn = document.querySelector('[data-close-menu]');
  const links   = document.querySelectorAll('[data-menu-link]');

  if ( !btn || !menu ) return;

  function openMenu() {
    menu.classList.remove('translate-x-full');
    btn.setAttribute('aria-expanded', 'true');
    menu.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeMenu() {
    menu.classList.add('translate-x-full');
    btn.setAttribute('aria-expanded', 'false');
    menu.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  btn.addEventListener('click', openMenu);
  if ( closeBtn ) closeBtn.addEventListener('click', closeMenu);
  links.forEach(function (link) { link.addEventListener('click', closeMenu); });

  document.addEventListener('keydown', function (e) {
    if ( e.key === 'Escape' ) closeMenu();
  });
})();

// ─── Tab navigation (single-font.php) ────────────────────
(function () {
  const triggers = document.querySelectorAll('[data-tab-trigger]');
  const panels   = document.querySelectorAll('[data-tab-panel]');

  if ( !triggers.length ) return;

  function activate(id) {
    triggers.forEach(function (t) {
      const active = t.dataset.tab === id;
      t.classList.toggle('border-dark',        active);
      t.classList.toggle('text-dark',          active);
      t.classList.toggle('border-transparent', !active);
      t.classList.toggle('text-dark/40',       !active);
    });
    panels.forEach(function (p) {
      p.classList.toggle('hidden', p.dataset.tabPanel !== id);
    });
  }

  triggers.forEach(function (t) {
    t.addEventListener('click', function () { activate(t.dataset.tab); });
  });

  // Buka tab dari URL hash (#specimen, #preview, #download)
  var hash = window.location.hash.replace('#', '');
  var valid = Array.from(triggers).some(function (t) { return t.dataset.tab === hash; });
  activate(valid ? hash : triggers[0].dataset.tab);
})();
