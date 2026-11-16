/**
 * The Pop Cabin: site-wide behaviour.
 * Every feature is progressive: the pages still work (just less smoothly) without JavaScript.
 */
(function () {
  'use strict';

  var SITE_URL = window.SITE_URL || '';
  var $ = function (sel, root) { return (root || document).querySelector(sel); };
  var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };

  // ---------- Public menu (hamburger) ----------
  var menuBtn = $('#menu-btn');
  var menu = $('#site-menu');
  function setMenu(open) {
    if (!menuBtn || !menu) return;
    menu.hidden = !open;
    menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    menuBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    document.body.classList.toggle('menu-open', open);
  }
  if (menuBtn && menu) {
    menuBtn.addEventListener('click', function () { setMenu(menu.hidden); });
    document.addEventListener('click', function (e) {
      if (!menu.hidden && !menu.contains(e.target) && !menuBtn.contains(e.target)) setMenu(false);
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !menu.hidden) { setMenu(false); menuBtn.focus(); }
    });
  }

  // ---------- Admin menu ----------
  var aBtn = $('#admin-menu-btn');
  var aMenu = $('#admin-menu');
  var aScrim = $('#admin-scrim');
  function setAdminMenu(open) {
    if (!aBtn || !aMenu) return;
    aMenu.hidden = !open;
    if (aScrim) aScrim.hidden = !open;
    aBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
  }
  if (aBtn && aMenu) {
    aBtn.addEventListener('click', function () { setAdminMenu(aMenu.hidden); });
    if (aScrim) aScrim.addEventListener('click', function () { setAdminMenu(false); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !aMenu.hidden) { setAdminMenu(false); aBtn.focus(); }
    });
  }

  // ---------- Dismissible admin banners ----------
  $$('[data-dismiss]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var banner = btn.closest('.admin-banner');
      if (banner) banner.remove();
    });
  });

  // ---------- Carousels with dot pagers ----------
  $$('[data-carousel]').forEach(function (carousel) {
    var track = $('.carousel-track', carousel);
    var dotsBox = $('[data-dots]', carousel);
    if (!track || !dotsBox) return;
    var slides = Array.prototype.slice.call(track.children);
    if (slides.length < 2) { dotsBox.hidden = true; var tab = carousel.querySelector('.frame-tab'); if (tab) tab.hidden = true; return; }

    var dots = slides.map(function (slide, i) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'dot';
      b.setAttribute('aria-label', 'Show slide ' + (i + 1) + ' of ' + slides.length);
      b.addEventListener('click', function () {
        track.scrollTo({ left: slides[i].offsetLeft - track.offsetLeft, behavior: 'smooth' });
      });
      dotsBox.appendChild(b);
      return b;
    });

    var current = -1;
    function update() {
      var i = Math.round(track.scrollLeft / Math.max(1, track.clientWidth));
      i = Math.max(0, Math.min(slides.length - 1, i));
      if (i === current) return;
      current = i;
      dots.forEach(function (d, n) {
        d.classList.toggle('is-active', n === i);
        if (n === i) d.setAttribute('aria-current', 'true'); else d.removeAttribute('aria-current');
      });
    }
    var ticking = false;
    track.addEventListener('scroll', function () {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(function () { ticking = false; update(); });
    }, { passive: true });
    update();
  });

  // ---------- Favorites (kept in this browser only) ----------
  var FAV_KEY = 'popcabin.favorites';
  function readFavs() {
    try { var v = JSON.parse(localStorage.getItem(FAV_KEY) || '[]'); return Array.isArray(v) ? v : []; }
    catch (e) { return []; }
  }
  function writeFavs(list) {
    try { localStorage.setItem(FAV_KEY, JSON.stringify(list)); } catch (e) { /* storage unavailable: toggle still works for this page */ }
  }
  function paintFav(btn, on) {
    btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    var label = btn.getAttribute('aria-label') || '';
    btn.setAttribute('aria-label', on ? label.replace(/^Add /, 'Remove ').replace(/ to favorites$/, ' from favorites')
                                      : label.replace(/^Remove /, 'Add ').replace(/ from favorites$/, ' to favorites'));
  }
  var favs = readFavs();
  $$('[data-fav]').forEach(function (btn) {
    var id = String(btn.getAttribute('data-fav'));
    paintFav(btn, favs.indexOf(id) !== -1);
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var list = readFavs();
      var at = list.indexOf(id);
      if (at === -1) list.push(id); else list.splice(at, 1);
      writeFavs(list);
      $$('[data-fav="' + id + '"]').forEach(function (b) { paintFav(b, at === -1); });
    });
  });

  // ---------- Dibs / Steal / Claim / Cabin Lock tabs ----------
  $$('[data-mechanics]').forEach(function (form) {
    var tabs = $$('.mech-tab', form);
    var action = $('.mech-action', form);
    if (!action) return;
    var inStock = action.getAttribute('data-in-stock') === '1';
    function select(tab) {
      tabs.forEach(function (t) {
        var on = t === tab;
        t.classList.toggle('is-active', on);
        t.setAttribute('aria-selected', on ? 'true' : 'false');
      });
      action.textContent = tab.getAttribute('data-label');
      // Only "Dibs" is wired to the reservation flow; the other states are shown but not active yet.
      action.disabled = !(tab.getAttribute('data-live') === '1' && inStock);
    }
    tabs.forEach(function (t, i) {
      t.addEventListener('click', function () { select(t); });
      t.addEventListener('keydown', function (e) {
        var n = e.key === 'ArrowRight' ? i + 1 : e.key === 'ArrowLeft' ? i - 1 : null;
        if (n === null) return;
        n = (n + tabs.length) % tabs.length;
        tabs[n].focus(); select(tabs[n]); e.preventDefault();
      });
    });
  });

  // ---------- Zoom lightbox ----------
  var zoomBtn = $('#zoom-btn');
  var box = $('#lightbox');
  if (zoomBtn && box && typeof box.showModal === 'function') {
    zoomBtn.addEventListener('click', function () { box.showModal(); });
    var closeBtn = $('#lightbox-close');
    if (closeBtn) closeBtn.addEventListener('click', function () { box.close(); });
    box.addEventListener('click', function (e) { if (e.target === box) box.close(); });
  } else if (zoomBtn) {
    zoomBtn.hidden = true;
  }

  // ---------- Contact form dropzone ----------
  $$('[data-dropzone]').forEach(function (zone) {
    var input = $('input[type="file"]', zone);
    var title = $('[data-dz-title]', zone);
    if (!input || !title) return;
    var original = title.textContent;
    input.addEventListener('change', function () {
      title.textContent = input.files && input.files[0] ? input.files[0].name : original;
    });
    ['dragenter', 'dragover'].forEach(function (ev) {
      zone.addEventListener(ev, function () { zone.classList.add('is-over'); });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
      zone.addEventListener(ev, function () { zone.classList.remove('is-over'); });
    });
  });

  // ---------- Admin photo upload preview ----------
  $$('[data-photo-drop]').forEach(function (zone) {
    var input = $('input[type="file"]', zone);
    var preview = $('[data-photo-preview]', zone);
    var label = $('[data-photo-label]', zone);
    if (!input || !preview) return;
    input.addEventListener('change', function () {
      var file = input.files && input.files[0];
      if (!file) return;
      if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl);
      var url = URL.createObjectURL(file);
      preview.dataset.objectUrl = url;
      preview.src = url;
      preview.hidden = false;
      preview.alt = 'Selected photo';
      zone.classList.add('has-photo');
      if (label) label.textContent = file.name + ' - Replace';
    });
  });

  // ---------- Toast + AJAX add-to-cart ----------
  var toastTimer = null;
  function showToast(message, type, withCartLink) {
    var toast = $('.toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.className = 'toast';
      toast.setAttribute('role', 'status');
      toast.setAttribute('aria-live', 'polite');
      document.body.appendChild(toast);
    }
    toast.textContent = message;
    if (withCartLink) {
      var a = document.createElement('a');
      a.href = SITE_URL + '/cart.php';
      a.textContent = 'View cart';
      toast.appendChild(document.createTextNode(' '));
      toast.appendChild(a);
    }
    toast.className = 'toast toast-' + (type || 'success') + ' show';
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { toast.classList.remove('show'); }, 4500);
  }

  function updateCartBadge(count) {
    $$('.cart-badge').forEach(function (badge) {
      badge.textContent = count;
      badge.style.display = count > 0 ? '' : 'none';
    });
  }

  $$('.add-to-cart-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.fetch || !window.FormData) return; // fall back to a normal POST
      e.preventDefault();

      var button = $('button[type="submit"]', form);
      if (button) button.disabled = true;

      fetch(SITE_URL + '/actions/add_to_cart.php', {
        method: 'POST',
        body: new FormData(form),
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (typeof data.cart_count === 'number') updateCartBadge(data.cart_count);
          showToast(data.message || (data.success ? 'Added to cart.' : 'Something went wrong.'),
                    data.success ? 'success' : 'error', !!data.success);
        })
        .catch(function () { form.submit(); })   // network/parsing problem: let the browser post normally
        .then(function () {
          if (!button) return;
          var active = form.querySelector('.mech-tab.is-active');
          var live = active ? active.getAttribute('data-live') === '1' : true;
          var inStock = button.getAttribute('data-in-stock') !== '0';
          button.disabled = !(live && inStock);
        });
    });
  });
})();
