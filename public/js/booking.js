/* booking.js — the booking page (/book).
 *
 * Flow: pick a stay length -> tap a check-in day -> the stay is drawn on the calendar
 * (check-in "In", check-out "Out", days between tinted) -> add details -> confirm.
 *
 * Rules enforced here (and again on the server in BookingAvailability::ensureAvailable):
 *  - every day of the stay, including the check-out day, needs a free slot;
 *  - a stay that runs into a fully booked day is shown as "Not available" and cannot be confirmed;
 *  - check-in days that cannot fit the shortest package are dimmed and cannot be picked.
 *
 * Dates are plain "YYYY-MM-DD" strings everywhere, so time zones never shift a day.
 */
(function () {
  'use strict';

  var root = document.getElementById('booking-app');
  if (!root) return;

  root.classList.remove('no-js');
  var cfg = JSON.parse(root.dataset.config);
  var form = document.getElementById('bk-form');

  function $(id) { return document.getElementById(id); }
  var el = {
    grid: $('bk-grid'), title: $('bk-month-title'), prev: $('bk-prev'), next: $('bk-next'),
    loadError: $('bk-load-error'), retry: $('bk-retry'),
    custom: $('bk-custom'), customInput: $('bk-custom-days'), customHint: $('bk-custom-hint'),
    sumLength: $('sum-length'), sumBadge: $('sum-badge'), sumIn: $('sum-in'), sumOut: $('sum-out'), sumMsg: $('sum-msg'),
    checkIn: $('bk-check-in'), days: $('bk-days'),
    submit: $('bk-submit'), formError: $('bk-form-error'), details: $('details'), name: $('bk-name'),
    steps: $('bk-steps'), done: $('bk-done'), again: $('bk-again'),
    bar: $('bk-bar'), barTitle: $('bar-title'), barSub: $('bar-sub'), barBtn: $('bar-btn'),
    back: $('bk-back'), lengthNote: $('bk-length-note'), sumDest: $('sum-dest'),
    trips: $('bk-trips'), tripsEmpty: $('bk-trips-empty'), customTrip: $('bk-trip-custom')
  };
  var tripGroups = Array.prototype.slice.call(root.querySelectorAll('.bk-trip-group'));
  var stepBtns = Array.prototype.slice.call(root.querySelectorAll('[data-step]'));

  /* ---------- dates ---------- */
  function pad(n) { return n < 10 ? '0' + n : String(n); }
  function parse(s) { var p = s.split('-'); return new Date(Date.UTC(+p[0], +p[1] - 1, +p[2])); }
  function fmt(d) { return d.getUTCFullYear() + '-' + pad(d.getUTCMonth() + 1) + '-' + pad(d.getUTCDate()); }
  function add(s, n) { var d = parse(s); d.setUTCDate(d.getUTCDate() + n); return fmt(d); }
  function monthOf(s) { return s.slice(0, 7); }
  function firstOf(s) { return s.slice(0, 7) + '-01'; }
  function lastOf(s) { var d = parse(firstOf(s)); d.setUTCMonth(d.getUTCMonth() + 1, 0); return fmt(d); }
  function addMonths(first, n) { var d = parse(first); d.setUTCMonth(d.getUTCMonth() + n, 1); return fmt(d); }
  function plural(n, w) { return n + ' ' + w + (n === 1 ? '' : 's'); }
  function lengthLabel(n) { return plural(n, 'day') + ', ' + plural(n - 1, 'night'); }

  var fMonth = new Intl.DateTimeFormat('en-US', { timeZone: 'UTC', month: 'long', year: 'numeric' });
  var fLong = new Intl.DateTimeFormat('en-US', { timeZone: 'UTC', weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
  var fMed = new Intl.DateTimeFormat('en-US', { timeZone: 'UTC', weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
  var fShort = new Intl.DateTimeFormat('en-US', { timeZone: 'UTC', month: 'short', day: 'numeric' });
  function short(s) { return fShort.format(parse(s)); }
  function listDates(arr) {
    var names = arr.map(short);
    if (names.length < 2) return names.join('');
    return names.slice(0, -1).join(', ') + ' and ' + names[names.length - 1];
  }

  /* ---------- state ---------- */
  var state = {
    view: firstOf(cfg.earliest),
    avail: {},          // "YYYY-MM-DD" -> slots left
    loaded: {},         // "YYYY-MM" -> Promise (availability already requested)
    pkg: 3, custom: false, customDays: cfg.maxPackageDays + 1,
    nation: null, trip: null, locked: false,      // trip: itinerary id, "custom", or null
    checkIn: null, hover: null, focusDate: null, sumToken: 0
  };
  var cells = [];

  /* ---------- availability ---------- */
  function ensureLoaded(key) {
    if (state.loaded[key]) return state.loaded[key];
    var first = key + '-01';
    var from = add(first, -7);
    var to = add(lastOf(first), cfg.maxCustomDays + 7);   // far enough to judge stays that cross into next month
    var p = fetch(cfg.availabilityUrl + '?from=' + from + '&to=' + to, {
      headers: { 'Accept': 'application/json' }, credentials: 'same-origin'
    }).then(function (r) {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    }).then(function (j) {
      Object.keys(j.data).forEach(function (k) { state.avail[k] = j.data[k]; });
    });
    state.loaded[key] = p;
    p.catch(function () { delete state.loaded[key]; });
    return p;
  }

  // closed (outside the booking window) | unknown (not loaded yet) | full | limited | available
  function dayStatus(d) {
    if (d < cfg.earliest || d > cfg.latest) return 'closed';
    var r = state.avail[d];
    if (r === undefined) return 'unknown';
    if (r <= 0) return 'full';
    return r <= cfg.limitedThreshold ? 'limited' : 'available';
  }
  function isFree(d) { var s = dayStatus(d); return s === 'available' || s === 'limited'; }

  // Can a stay of the shortest package start on this day?
  function canStart(d) {
    for (var i = 0; i < cfg.minDays; i++) { if (!isFree(add(d, i))) return false; }
    return true;
  }

  /* ---------- the stay ---------- */
  function stayDays() {
    if (!state.custom) return state.pkg;
    var n = state.customDays;
    return (n > cfg.maxPackageDays && n <= cfg.maxCustomDays) ? n : null;
  }

  // Judge a stay of n days starting on `start`: which days are fully booked / outside the window?
  function evalStay(start, n) {
    var res = { start: start, end: add(start, n - 1), days: n, full: [], outside: [], unknown: false, blockedSet: {} };
    for (var i = 0; i < n; i++) {
      var d = add(start, i), s = dayStatus(d);
      if (s === 'full') { res.full.push(d); res.blockedSet[d] = true; }
      else if (s === 'closed') { res.outside.push(d); res.blockedSet[d] = true; }
      else if (s === 'unknown') { res.unknown = true; }
    }
    res.ok = !res.full.length && !res.outside.length && !res.unknown;
    return res;
  }

  function currentEval() {
    var n = stayDays();
    return (state.checkIn && n) ? evalStay(state.checkIn, n) : null;
  }

  /* ---------- calendar ---------- */
  function buildGrid() {
    var first = state.view;
    var dow = parse(first).getUTCDay();
    var dim = parse(lastOf(first)).getUTCDate();
    var total = Math.ceil((dow + dim) / 7) * 7;
    var start = add(first, -dow);

    el.grid.textContent = '';
    cells = [];
    for (var i = 0; i < total; i++) {
      var d = add(start, i);
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'bk-day';
      b.dataset.date = d;
      b.dataset.outside = monthOf(d) === monthOf(first) ? '' : '1';
      b.tabIndex = -1;
      b.innerHTML = '<span class="bk-num"></span><span class="bk-dot" hidden></span><span class="bk-tag" hidden></span>';
      b.firstChild.textContent = Number(d.slice(8));
      el.grid.appendChild(b);
      cells.push(b);
    }
    el.title.textContent = fMonth.format(parse(first));
    el.prev.disabled = monthOf(first) <= monthOf(cfg.earliest);
    el.next.disabled = monthOf(first) >= monthOf(cfg.latest);
  }

  function cellFor(d) {
    for (var i = 0; i < cells.length; i++) { if (cells[i].dataset.date === d && cells[i].dataset.outside !== '1') return cells[i]; }
    return null;
  }

  function pickTabDate() {
    if (state.focusDate && cellFor(state.focusDate)) return state.focusDate;
    if (state.checkIn && cellFor(state.checkIn)) return state.checkIn;
    for (var i = 0; i < cells.length; i++) {
      if (cells[i].dataset.outside !== '1' && canStart(cells[i].dataset.date)) return cells[i].dataset.date;
    }
    for (var j = 0; j < cells.length; j++) { if (cells[j].dataset.outside !== '1') return cells[j].dataset.date; }
    return null;
  }

  function paint() {
    var n = stayDays();
    var ev = (state.checkIn && n) ? evalStay(state.checkIn, n) : null;
    var pv = (state.hover && n && state.hover !== state.checkIn && canStart(state.hover)) ? evalStay(state.hover, n) : null;
    var tabDate = pickTabDate();

    cells.forEach(function (b) {
      var d = b.dataset.date, outside = b.dataset.outside === '1', st = dayStatus(d);
      var cls = ['bk-day'];
      var selectable = !outside && canStart(d);
      var role = '';

      if (outside) cls.push('is-outside');
      if (st === 'closed') cls.push('is-closed');
      else if (st === 'full') cls.push('is-full');
      else if (st === 'limited') cls.push('is-limited');
      if (d === cfg.today) cls.push('is-today');
      if (!outside && !selectable && (st === 'available' || st === 'limited')) cls.push('is-dim');

      if (ev && d >= ev.start && d <= ev.end) {
        cls.push('is-range');
        role = 'in your stay';
        if (d === ev.start) { cls.push('is-in'); role = 'check-in'; }
        if (d === ev.end) { cls.push('is-out'); role = 'check-out'; }
        if (!ev.ok) { cls.push('is-bad'); if (ev.blockedSet[d]) cls.push('is-blocked'); }
      } else if (pv && d >= pv.start && d <= pv.end) {
        cls.push('is-preview');
      }
      b.className = cls.join(' ');

      var dot = b.children[1], tag = b.children[2];
      var showDot = !outside && (st === 'available' || st === 'limited' || st === 'full');
      dot.hidden = !showDot;
      if (showDot) dot.className = 'bk-dot ' + (st === 'full' ? 'bk-dot--full' : st === 'limited' ? 'bk-dot--warn' : 'bk-dot--ok');
      var tagText = cls.indexOf('is-in') > -1 ? 'In' : cls.indexOf('is-out') > -1 ? 'Out' : '';
      tag.hidden = !tagText;
      tag.textContent = tagText;

      var left = state.avail[d];
      var statusText = st === 'full' ? 'fully booked'
        : st === 'limited' ? plural(left, 'slot') + ' left'
        : st === 'available' ? 'open'
        : st === 'unknown' ? 'loading' : 'not available';
      b.setAttribute('aria-label', fLong.format(parse(d)) + ', ' + statusText + (role ? ', ' + role : ''));
      b.setAttribute('aria-disabled', selectable ? 'false' : 'true');
      if (state.checkIn === d) b.setAttribute('aria-pressed', 'true'); else b.removeAttribute('aria-pressed');
      if (!selectable && !outside && cls.indexOf('is-dim') > -1) b.title = 'A ' + cfg.minDays + '-day stay doesn\u2019t fit here';
      else b.removeAttribute('title');
      b.tabIndex = (!outside && d === tabDate) ? 0 : -1;
    });
  }

  function showMonth(first) {
    state.view = first;
    var key = monthOf(first);
    el.loadError.hidden = true;
    el.grid.setAttribute('aria-busy', 'true');
    buildGrid();
    paint();
    ensureLoaded(key).then(function () {
      if (monthOf(state.view) !== key) return;
      el.grid.setAttribute('aria-busy', 'false');
      paint();
      renderSummary();
    }).catch(function () {
      if (monthOf(state.view) !== key) return;
      el.loadError.hidden = false;
    });
  }

  function selectCheckIn(d) {
    state.checkIn = d;
    state.focusDate = d;
    paint();
    renderSummary();
  }

  function focusDate(d) {
    if (monthOf(d) < monthOf(cfg.earliest) || monthOf(d) > monthOf(cfg.latest)) return;
    state.focusDate = d;
    if (monthOf(d) !== monthOf(state.view)) showMonth(firstOf(d)); else paint();
    var c = cellFor(d);
    if (c) c.focus();
  }

  el.grid.addEventListener('click', function (e) {
    var b = e.target.closest('.bk-day');
    if (!b || b.dataset.outside === '1') return;
    if (!canStart(b.dataset.date)) return;
    selectCheckIn(b.dataset.date);
  });

  if (window.matchMedia && window.matchMedia('(hover:hover)').matches) {
    el.grid.addEventListener('mouseover', function (e) {
      var b = e.target.closest('.bk-day');
      var d = (b && b.dataset.outside !== '1') ? b.dataset.date : null;
      if (d !== state.hover) { state.hover = d; paint(); }
    });
    el.grid.addEventListener('mouseleave', function () {
      if (state.hover) { state.hover = null; paint(); }
    });
  }

  el.grid.addEventListener('keydown', function (e) {
    var b = e.target.closest('.bk-day');
    if (!b) return;
    var step = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 }[e.key];
    if (step === undefined) return;
    e.preventDefault();
    focusDate(add(b.dataset.date, step));
  });

  el.prev.addEventListener('click', function () { showMonth(addMonths(state.view, -1)); });
  el.next.addEventListener('click', function () { showMonth(addMonths(state.view, 1)); });
  el.retry.addEventListener('click', function () { showMonth(state.view); });

  /* ---------- stay length ---------- */
  function syncCustom() {
    if (!el.custom) return;
    var off = !state.custom || state.locked;
    el.custom.classList.toggle('is-on', !off);
    el.customInput.disabled = off;
    stepBtns.forEach(function (b) { b.disabled = off; });
    var bad = state.custom && stayDays() === null;
    el.customHint.classList.toggle('is-error', bad);
    el.customHint.textContent = bad
      ? 'Enter ' + (cfg.maxPackageDays + 1) + ' to ' + cfg.maxCustomDays + ' days.'
      : 'Over ' + cfg.maxPackageDays + ' days is confirmed by our team (' + (cfg.maxPackageDays + 1) + ' to ' + cfg.maxCustomDays + ' days).';
  }

  function readStay() {
    var v = form.elements.stay.value;
    if (v === 'custom') { state.custom = true; }
    else { state.custom = false; state.pkg = parseInt(v, 10); }
    syncCustom();
  }

  // Used by the "shorten" suggestion: pick k days, as a package or as a custom length.
  function setStay(k) {
    var radios = form.elements.stay;
    var target = k <= cfg.maxPackageDays ? String(k) : 'custom';
    for (var i = 0; i < radios.length; i++) { radios[i].checked = radios[i].value === target; }
    if (target === 'custom') { el.customInput.value = k; state.customDays = k; }
    readStay();
    paint();
    renderSummary();
  }

  form.addEventListener('change', function (e) {
    if (e.target.name !== 'stay') return;
    readStay();
    if (state.custom && el.customInput) el.customInput.focus();
    paint();
    renderSummary();
  });

  if (el.customInput) {
    el.customInput.addEventListener('input', function () {
      state.customDays = parseInt(el.customInput.value, 10);
      syncCustom();
      paint();
      renderSummary();
    });
    stepBtns.forEach(function (b) {
      b.addEventListener('click', function () {
        var cur = parseInt(el.customInput.value, 10);
        if (isNaN(cur)) cur = cfg.maxPackageDays;
        var next = Math.min(cfg.maxCustomDays, Math.max(cfg.maxPackageDays + 1, cur + parseInt(b.dataset.step, 10)));
        el.customInput.value = next;
        state.customDays = next;
        syncCustom();
        paint();
        renderSummary();
      });
    });
  }

  /* ---------- where to: nation + itinerary ---------- */
  function checkedValue(name) {
    var r = form.querySelector('input[name="' + name + '"]:checked');
    return r || null;
  }
  function nationName(slug) {
    var r = form.querySelector('input[name="nation"][value="' + slug + '"]');
    return r ? r.dataset.name : '';
  }

  // A real itinerary has a fixed length, so the stay-length options follow it. "Custom route" frees them again.
  function lockStay(days) {
    state.locked = true;
    setStay(days);
    var target = days <= cfg.maxPackageDays ? String(days) : 'custom';
    var radios = form.elements.stay;
    for (var i = 0; i < radios.length; i++) { radios[i].disabled = radios[i].value !== target; }
    syncCustom();
    el.lengthNote.textContent = 'Set by your itinerary (' + plural(days, 'day') + ')';
  }
  function unlockStay() {
    state.locked = false;
    var radios = form.elements.stay;
    for (var i = 0; i < radios.length; i++) { radios[i].disabled = false; }
    syncCustom();
    el.lengthNote.textContent = el.lengthNote.dataset.default;
  }

  function applyWhere(scrollToTrips) {
    var n = checkedValue('nation');
    var previous = state.nation;
    state.nation = n ? n.value : null;

    tripGroups.forEach(function (g) { g.hidden = !(state.nation && g.dataset.nation === state.nation); });
    el.tripsEmpty.hidden = !!state.nation;
    el.customTrip.hidden = !state.nation;

    var t = checkedValue('itinerary');
    if (t && t.value !== 'custom' && t.dataset.nation !== state.nation) { t.checked = false; t = null; }   // belongs to another nation
    state.trip = t ? t.value : null;

    if (t && t.value !== 'custom') lockStay(parseInt(t.dataset.days, 10));
    else unlockStay();

    renderSummary();

    // On phones the itineraries sit below the nation tiles, so bring them into view after a new nation is picked.
    if (scrollToTrips && state.nation && state.nation !== previous && window.matchMedia('(max-width:900px)').matches) {
      el.trips.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  function destLabel() {
    if (!state.nation) return '\u2014';
    var t = checkedValue('itinerary');
    return nationName(state.nation) + (t ? '\n' + (t.value === 'custom' ? 'Custom route' : t.dataset.title) : '');
  }

  form.addEventListener('change', function (e) {
    if (e.target.name === 'nation') applyWhere(true);
    else if (e.target.name === 'itinerary') applyWhere(false);
  });

  /* ---------- back button ---------- */
  // Go back to wherever the traveller came from (a nation page, the home page...); otherwise the link goes home.
  if (el.back) {
    el.back.addEventListener('click', function (e) {
      try {
        if (document.referrer && new URL(document.referrer).origin === window.location.origin && window.history.length > 1) {
          e.preventDefault();
          window.history.back();
        }
      } catch (err) { /* fall through to the normal link */ }
    });
  }

  /* ---------- summary (side card + mobile bar) ---------- */
  function setBadge(kind, text) {
    el.sumBadge.className = 'bk-badge bk-badge--' + kind;
    el.sumBadge.textContent = text;
  }

  function makeChip(main, sub, onClick) {
    var b = document.createElement('button');
    b.type = 'button';
    b.className = 'bk-chip' + (onClick ? '' : ' bk-chip--wait');
    var m = document.createElement('span');
    m.textContent = main;
    b.appendChild(m);
    if (sub) { var s = document.createElement('small'); s.textContent = sub; b.appendChild(s); }
    if (onClick) b.addEventListener('click', onClick); else b.disabled = true;
    return b;
  }

  // First start day after `after` where a stay of n days fits entirely (loads later months as needed).
  function findNextOpening(after, n) {
    var limit = add(after, 90);
    if (limit > cfg.latest) limit = cfg.latest;
    var keys = {};
    for (var d = add(after, 1); d <= add(limit, n); d = add(d, 7)) { keys[monthOf(d)] = true; }
    keys[monthOf(add(limit, n))] = true;
    return Promise.all(Object.keys(keys).map(ensureLoaded)).then(function () {
      for (var s = add(after, 1); s <= limit; s = add(s, 1)) {
        if (evalStay(s, n).ok) return s;
      }
      return null;
    }).catch(function () { return null; });
  }

  function renderBlocked(ev, n, tok) {
    var p = document.createElement('p');
    var parts = [];
    if (ev.full.length) parts.push(listDates(ev.full) + (ev.full.length === 1 ? ' is' : ' are') + ' fully booked.');
    if (ev.outside.length) parts.push('Some of these days are outside our booking window.');
    p.textContent = parts.join(' ');
    el.sumMsg.appendChild(p);

    var box = document.createElement('div');
    box.className = 'bk-fix';
    el.sumMsg.appendChild(box);

    // 1) a shorter stay from the same check-in day
    var k = 0;
    while (k < n && isFree(add(ev.start, k))) k++;
    if (k >= cfg.minDays && k < n) {
      box.appendChild(makeChip('Shorten to ' + plural(k, 'day'), short(ev.start) + ' to ' + short(add(ev.start, k - 1)), function () { setStay(k); }));
    }

    // 2) the next time the same length fits
    var wait = makeChip('Looking for the next opening\u2026', '', null);
    box.appendChild(wait);
    findNextOpening(ev.start, n).then(function (s) {
      if (tok !== state.sumToken) return;
      if (!s) { wait.remove(); return; }
      var chip = makeChip('Next opening: ' + short(s) + ' to ' + short(add(s, n - 1)), 'same length', function () {
        if (monthOf(s) !== monthOf(state.view)) showMonth(firstOf(s));
        selectCheckIn(s);
      });
      wait.replaceWith(chip);
    });
  }

  function renderSummary() {
    var n = stayDays();
    var ev = currentEval();
    var tok = ++state.sumToken;

    el.sumLength.textContent = n ? lengthLabel(n) : 'Enter number of days';
    el.sumIn.textContent = state.checkIn ? fMed.format(parse(state.checkIn)) : '\u2014';
    el.sumOut.textContent = (state.checkIn && n) ? fMed.format(parse(add(state.checkIn, n - 1))) : '\u2014';
    el.sumMsg.textContent = '';
    el.sumDest.textContent = destLabel();

    // The destination comes first: no nation or itinerary yet means nothing can be confirmed.
    var destMsg = !state.nation ? 'Choose a destination' : (!state.trip ? 'Choose an itinerary' : '');
    var barSub = destMsg || 'Choose a check-in day', barBad = false, canContinue = false;

    if (!n) {
      setBadge('idle', 'Enter ' + (cfg.maxPackageDays + 1) + ' to ' + cfg.maxCustomDays + ' days');
      barSub = 'Enter a valid number of days';
    } else if (!ev) {
      setBadge('idle', destMsg || 'Choose a check-in day');
    } else if (ev.unknown) {
      setBadge('idle', 'Checking availability\u2026');
      barSub = 'Checking availability\u2026';
    } else if (!ev.ok) {
      setBadge('bad', 'Not available');
      renderBlocked(ev, n, tok);
      barSub = 'Not available for these dates';
      barBad = true;
    } else if (destMsg) {
      setBadge('idle', destMsg);
      barSub = destMsg;
    } else if (n > cfg.maxPackageDays) {
      setBadge('info', 'Confirmed by our team');
      barSub = short(ev.start) + ' to ' + short(ev.end) + ' \u00b7 team confirms';
      canContinue = true;
    } else {
      setBadge('ok', 'Dates available');
      barSub = short(ev.start) + ' to ' + short(ev.end);
      canContinue = true;
    }

    el.submit.disabled = !canContinue;
    el.barTitle.textContent = n ? lengthLabel(n) : 'Custom stay';
    el.barSub.textContent = barSub;
    el.barSub.classList.toggle('is-bad', barBad);
    // Blocked stay: the bar's button leads to the "shorten / next opening" options in the summary card.
    var mode = canContinue ? 'details' : (barBad ? 'options' : '');
    el.barBtn.dataset.mode = mode;
    el.barBtn.textContent = mode === 'options' ? 'See options' : 'Continue';
    el.barBtn.disabled = mode === '';
  }

  /* ---------- mobile bar ---------- */
  el.barBtn.addEventListener('click', function () {
    if (el.barBtn.dataset.mode === 'options') {
      el.sumMsg.closest('section').scrollIntoView({ behavior: 'smooth', block: 'start' });
      return;
    }
    el.details.scrollIntoView({ behavior: 'smooth', block: 'start' });
    window.setTimeout(function () { el.name.focus({ preventScroll: true }); }, 450);
  });

  if ('IntersectionObserver' in window) {
    // The details card has its own Confirm button, so the bar steps aside while it is on screen.
    new IntersectionObserver(function (entries) {
      if (!el.done.hidden) return;
      el.bar.hidden = entries[0].intersectionRatio >= 0.25;
    }, { threshold: [0, 0.25, 0.5] }).observe(el.details);
  }

  /* ---------- submit ---------- */
  function clearErrors() {
    Array.prototype.forEach.call(form.querySelectorAll('[data-error-for]'), function (s) {
      s.textContent = '';
      var input = form.elements[s.dataset.errorFor];
      if (input) { input.removeAttribute('aria-invalid'); input.removeAttribute('aria-describedby'); }
    });
    el.formError.hidden = true;
    el.formError.textContent = '';
  }
  function formError(msg) {
    el.formError.textContent = msg;
    el.formError.hidden = false;
  }
  function setBusy(on) {
    el.submit.dataset.busy = on ? '1' : '';
    el.submit.disabled = on || !(currentEval() && currentEval().ok);
    el.submit.textContent = on ? 'Confirming\u2026' : 'Confirm booking';
  }

  function refreshAvailability() {
    state.avail = {};
    state.loaded = {};
    showMonth(state.view);
  }

  function showErrors(errors) {
    var dateMsg = null, firstInvalid = null;
    Object.keys(errors).forEach(function (field) {
      var slot = form.querySelector('[data-error-for="' + field + '"]');
      if (slot) {
        slot.textContent = errors[field][0];
        var input = form.elements[field];
        if (input) {   // link the message to its field so screen readers announce it
          input.setAttribute('aria-invalid', 'true');
          input.setAttribute('aria-describedby', slot.id);
          firstInvalid = firstInvalid || input;
        }
      } else dateMsg = dateMsg || errors[field][0];
    });
    if (firstInvalid) firstInvalid.focus();
    if (dateMsg) {
      formError(dateMsg);
      refreshAvailability();   // the calendar was out of date: reload it so the blocked days show
    }
  }

  function showDone(b) {
    var first = String(b.name || '').split(' ')[0];
    $('done-lead').textContent = (first ? 'Thanks, ' + first + '. ' : '') + (b.status === 'pending'
      ? 'Our team will confirm your stay and reach out using the details you gave.'
      : 'Your stay is confirmed. Keep this reference handy.');
    $('done-ref').textContent = b.reference;
    $('done-dest').textContent = b.nation + '\n' + b.itinerary_title;
    $('done-length').textContent = lengthLabel(b.days);
    $('done-in').textContent = fMed.format(parse(b.check_in));
    $('done-out').textContent = fMed.format(parse(b.check_out));
    el.steps.hidden = true;
    el.bar.hidden = true;
    el.done.hidden = false;
    el.done.focus();
    el.done.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  // The page may have been open longer than the session lifetime, so the form token can be stale (HTTP 419).
  // Fetch a fresh token and retry once, keeping everything the traveller typed.
  function refreshToken() {
    return fetch(cfg.csrfUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin', cache: 'no-store' })
      .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function (j) { form.elements._token.value = j.token; });
  }

  function send(isRetry) {
    return fetch(form.action, {
      method: 'POST',
      body: new FormData(form),
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    }).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (body) { return { ok: r.ok, status: r.status, body: body }; });
    }).then(function (res) {
      if (res.status === 419 && !isRetry) {
        return refreshToken().then(function () { return send(true); });
      }
      return res;
    });
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var ev = currentEval();
    var n = stayDays();
    if (!ev || !ev.ok) return;
    if (el.submit.dataset.busy === '1') return;   // ignore double clicks / double taps

    clearErrors();
    el.checkIn.value = state.checkIn;
    el.days.value = n;
    setBusy(true);

    send(false).then(function (res) {
      setBusy(false);
      if (res.ok) showDone(res.body);
      else if (res.status === 422 && res.body.errors) showErrors(res.body.errors);
      else if (res.status === 419) formError('Your session expired and we couldn\u2019t renew it. Reload the page and choose your dates again.');
      else if (res.status === 429) formError('Too many attempts. Please wait a minute and try again.');
      else if (res.status === 503 && res.body.message) formError(res.body.message);
      else formError('Something went wrong on our side. Please try again.');
    }).catch(function () {
      setBusy(false);
      formError('We couldn\u2019t reach the server. Check your connection and try again.');
    });
  });

  el.again.addEventListener('click', function () {
    form.reset();
    clearErrors();
    state.checkIn = null;
    state.hover = null;
    state.focusDate = null;
    readStay();
    applyWhere(false);
    el.done.hidden = true;
    el.steps.hidden = false;
    el.bar.hidden = false;
    state.avail = {};
    state.loaded = {};
    showMonth(firstOf(cfg.earliest));
    renderSummary();
    root.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });

  /* ---------- go ---------- */
  readStay();
  applyWhere(false);   // picks up a nation/itinerary chosen via /book?itinerary=... and locks the stay length to it
  showMonth(state.view);
  renderSummary();
})();
