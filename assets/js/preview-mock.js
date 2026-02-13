/**
 * Kivii Preview – Mock JS
 * Self-contained booking flow with mock data (no WordPress needed).
 */
(function () {
    'use strict';

    const app = document.getElementById('kivii-booking-app');
    if (!app) return;

    // ── Mock Services Data ─────────────────
    const mockServices = [
        {
            name_nl: 'Onderhoud', name_en: 'Maintenance', services: [
                { id: 1, title_nl: 'Kleine beurt', title_en: 'Basic service', description_nl: 'Olie verversen, filters, vloeistoffen', description_en: 'Oil change, filters, fluids', price: 149.95, duration_minutes: 45, is_addon: false },
                { id: 2, title_nl: 'Grote beurt', title_en: 'Full service', description_nl: 'Complete controle + onderhoud', description_en: 'Full inspection + maintenance', price: 289.95, duration_minutes: 120, is_addon: false },
                { id: 3, title_nl: 'Airco controle', title_en: 'AC check', description_nl: 'Koelmiddel bijvullen + lektest', description_en: 'Coolant refill + leak test', price: 79.50, duration_minutes: 30, is_addon: true },
            ]
        },
        {
            name_nl: 'Banden', name_en: 'Tires', services: [
                { id: 4, title_nl: 'Banden wisselen (4x)', title_en: 'Tire change (4x)', description_nl: 'Seizoensbanden wisselen', description_en: 'Seasonal tire swap', price: 49.95, duration_minutes: 30, is_addon: false },
                { id: 5, title_nl: 'Uitlijnen', title_en: 'Wheel alignment', description_nl: '4-wiel uitlijning', description_en: '4-wheel alignment', price: 69.95, duration_minutes: 45, is_addon: true },
            ]
        },
        {
            name_nl: 'APK & Keuring', name_en: 'MOT & Inspection', services: [
                { id: 6, title_nl: 'APK Keuring', title_en: 'MOT Test', description_nl: 'Wettelijk verplichte keuring', description_en: 'Mandatory vehicle inspection', price: 34.95, duration_minutes: 30, is_addon: false },
                { id: 7, title_nl: 'Remmen controle', title_en: 'Brake inspection', description_nl: 'Schijven, blokken en vloeistof', description_en: 'Discs, pads and fluid', price: 29.95, duration_minutes: 20, is_addon: true },
            ]
        },
    ];

    const dropOffTimes = ['08:00', '09:00', '10:00', '13:00'];
    const mockSlots = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00'];

    // ── State ──────────────────────────────
    const state = {
        step: 1, maxSteps: 4,
        data: { license_plate: '', mileage: '', selected_services: [], is_drop_off: false, appointment_date: '', appointment_time: '', drop_off_time: '', first_name: '', last_name: '', email: '', phone: '', street: '', house_number: '', house_addition: '', postal_code: '', city: '', remarks: '', privacy_accepted: false },
        calendar: { month: new Date().getMonth() + 1, year: new Date().getFullYear() },
    };

    // ── Helpers ─────────────────────────────
    const lang = () => document.getElementById('lang-switch')?.value || 'nl';
    const lk = (obj, key) => obj[key + '_' + lang()] || obj[key + '_nl'] || '';
    const fp = p => '€ ' + parseFloat(p).toFixed(2).replace('.', ',');
    const esc = s => { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; };

    function fmtDate(ds) {
        const d = new Date(ds + 'T00:00:00');
        const dn = lang() === 'nl' ? ['zo', 'ma', 'di', 'wo', 'do', 'vr', 'za'] : ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const mn = lang() === 'nl' ? ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'] : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return `${dn[d.getDay()]} ${d.getDate()} ${mn[d.getMonth()]} ${d.getFullYear()}`;
    }

    // ── Navigation ─────────────────────────
    const steps = app.querySelectorAll('.kivii-step');
    const progSteps = app.querySelectorAll('.kivii-progress__step');
    const progFill = app.querySelector('.kivii-progress__fill');
    const prevBtn = document.getElementById('kivii-prev');
    const nextBtn = document.getElementById('kivii-next');
    const submitBtn = document.getElementById('kivii-submit');
    const navEl = document.getElementById('kivii-nav');

    function goToStep(n) {
        state.step = n;
        steps.forEach(s => s.classList.toggle('is-active', parseInt(s.dataset.step) === n || (n > state.maxSteps && s.dataset.step === 'confirm')));
        progSteps.forEach(s => { const sn = parseInt(s.dataset.step); s.classList.toggle('is-active', sn === n); s.classList.toggle('is-completed', sn < n); });
        progFill.style.width = Math.min(100, (n / state.maxSteps) * 100) + '%';
        prevBtn.style.display = n > 1 && n <= state.maxSteps ? '' : 'none';
        nextBtn.style.display = n < state.maxSteps ? '' : 'none';
        submitBtn.style.display = n === state.maxSteps ? '' : 'none';
        navEl.style.display = n > state.maxSteps ? 'none' : '';
        if (n === 2) renderServices();
        if (n === 3) renderCalendar();
        updateSidebar();
        app.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    prevBtn.addEventListener('click', () => goToStep(state.step - 1));
    nextBtn.addEventListener('click', () => { if (validateStep(state.step)) goToStep(state.step + 1); });
    submitBtn.addEventListener('click', doSubmit);
    progSteps.forEach(s => s.addEventListener('click', () => { const t = parseInt(s.dataset.step); if (t < state.step) goToStep(t); }));

    // ── Validation ─────────────────────────
    function clearErrors() { app.querySelectorAll('.kivii-error').forEach(e => e.textContent = ''); app.querySelectorAll('.has-error').forEach(e => e.classList.remove('has-error')); }
    function showErr(f, m) { const e = document.getElementById('error-' + f); if (e) e.textContent = m; const i = app.querySelector(`[name="${f}"]`); if (i) i.classList.add('has-error'); }

    function validateStep(n) {
        clearErrors(); let ok = true;
        if (n === 1) {
            const p = document.getElementById('kivii-plate').value.trim();
            const m = document.getElementById('kivii-mileage').value;
            if (!p || p.length < 6) { showErr('license_plate', 'Voer een geldig kenteken in.'); ok = false; }
            if (!m || parseInt(m) < 0) { showErr('mileage', 'Voer een geldige km-stand in.'); ok = false; }
            if (ok) { state.data.license_plate = p.toUpperCase(); state.data.mileage = parseInt(m); }
        }
        if (n === 2) {
            collectSelected();
            if (state.data.selected_services.length === 0) { showErr('services', 'Selecteer minimaal één werkzaamheid.'); ok = false; }
        }
        if (n === 3) {
            if (!state.data.appointment_date) { showErr('appointment_date', 'Selecteer een datum.'); ok = false; }
            if (!state.data.is_drop_off && !state.data.appointment_time) { showErr('appointment_time', 'Selecteer een tijdstip.'); ok = false; }
            if (state.data.is_drop_off && !state.data.drop_off_time) { showErr('drop_off_time', 'Selecteer een brengmoment.'); ok = false; }
        }
        if (n === 4) {
            const flds = { first_name: 'kivii-firstname', last_name: 'kivii-lastname', email: 'kivii-email', phone: 'kivii-phone', street: 'kivii-street', house_number: 'kivii-housenumber', postal_code: 'kivii-postalcode', city: 'kivii-city' };
            for (const [k, id] of Object.entries(flds)) {
                const v = document.getElementById(id).value.trim();
                if (!v) { showErr(k, 'Dit veld is verplicht.'); ok = false; } else state.data[k] = v;
            }
            if (state.data.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(state.data.email)) { showErr('email', 'Ongeldig e-mailadres.'); ok = false; }
            if (!document.getElementById('kivii-privacy').checked) { showErr('privacy_accepted', 'U dient akkoord te gaan.'); ok = false; }
            state.data.house_addition = document.getElementById('kivii-addition').value.trim();
            state.data.remarks = document.getElementById('kivii-remarks').value.trim();
        }
        return ok;
    }

    // ── Step 2: Services ───────────────────
    let servicesRendered = false;

    function renderServices() {
        if (servicesRendered) return;
        servicesRendered = true;
        const c = document.getElementById('kivii-services-container');
        let html = '';
        for (const cat of mockServices) {
            html += `<div class="kivii-service-category"><h3 class="kivii-service-category__title">${esc(lk(cat, 'name'))}</h3>`;
            for (const s of cat.services) {
                const addon = s.is_addon ? '<span class="kivii-service-card__addon-tag">Add-on</span>' : '';
                html += `<div class="kivii-service-card" data-id="${s.id}" data-price="${s.price}" data-duration="${s.duration_minutes}">
                    <div class="kivii-service-card__check"></div>
                    <div class="kivii-service-card__info"><div class="kivii-service-card__title">${esc(lk(s, 'title'))}${addon}</div><div class="kivii-service-card__desc">${esc(lk(s, 'description'))}</div></div>
                    <div class="kivii-service-card__meta"><div class="kivii-service-card__price">${fp(s.price)}</div><div class="kivii-service-card__duration">${s.duration_minutes} min</div></div>
                </div>`;
            }
            html += '</div>';
        }
        c.innerHTML = html;
        c.querySelectorAll('.kivii-service-card').forEach(card => card.addEventListener('click', () => toggleSvc(card)));
    }

    function toggleSvc(card) {
        card.classList.toggle('is-selected');
        const chk = card.querySelector('.kivii-service-card__check');
        chk.innerHTML = card.classList.contains('is-selected') ? '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M11.5 3.5L5.5 10.5L2.5 7.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' : '';
        collectSelected(); updateTotals(); updateSidebar();
    }

    function collectSelected() { state.data.selected_services = [...document.querySelectorAll('.kivii-service-card.is-selected')].map(c => parseInt(c.dataset.id)); }

    function getSelDetails() {
        return [...document.querySelectorAll('.kivii-service-card.is-selected')].map(c => ({
            id: parseInt(c.dataset.id), price: parseFloat(c.dataset.price), duration: parseInt(c.dataset.duration),
            title: c.querySelector('.kivii-service-card__title')?.textContent?.replace('Add-on', '').trim() || ''
        }));
    }

    function updateTotals() {
        const sel = getSelDetails();
        const tp = sel.reduce((s, i) => s + i.price, 0);
        const td = sel.reduce((s, i) => s + i.duration, 0);
        const pe = document.getElementById('kivii-total-price');
        const de = document.getElementById('kivii-total-duration');
        if (pe) pe.textContent = fp(tp);
        if (de) de.textContent = td + ' min';
    }

    // ── Step 3: Calendar ───────────────────
    function renderCalendar() {
        const mn = lang() === 'nl' ? ['Januari', 'Februari', 'Maart', 'April', 'Mei', 'Juni', 'Juli', 'Augustus', 'September', 'Oktober', 'November', 'December'] : ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        document.getElementById('kivii-cal-title').textContent = `${mn[state.calendar.month - 1]} ${state.calendar.year}`;
        const fd = new Date(state.calendar.year, state.calendar.month - 1, 1);
        const ld = new Date(state.calendar.year, state.calendar.month, 0);
        const si = (fd.getDay() + 6) % 7;
        const today = new Date(); today.setHours(0, 0, 0, 0);
        const grid = document.getElementById('kivii-cal-grid');
        let html = '';
        for (let i = 0; i < si; i++) html += '<button class="kivii-calendar__day is-empty" disabled></button>';
        for (let d = 1; d <= ld.getDate(); d++) {
            const ds = `${state.calendar.year}-${String(state.calendar.month).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            const dt = new Date(state.calendar.year, state.calendar.month - 1, d);
            const past = dt < today, wknd = dt.getDay() === 0 || dt.getDay() === 6;
            let cls = 'kivii-calendar__day';
            if (dt.getTime() === today.getTime()) cls += ' is-today';
            if (past || wknd) cls += ' is-disabled';
            if (ds === state.data.appointment_date) cls += ' is-selected';
            if (!past && !wknd) {
                const r = Math.random();
                cls += r > .3 ? ' has-availability' : r > .1 ? ' has-limited' : ' has-full is-disabled';
            }
            html += `<button class="${cls}" data-date="${ds}" ${cls.includes('is-disabled') ? 'disabled' : ''}>${d}</button>`;
        }
        grid.innerHTML = html;
        grid.querySelectorAll('.kivii-calendar__day:not(.is-disabled):not(.is-empty)').forEach(b => b.addEventListener('click', () => selectDate(b)));
    }

    function selectDate(btn) {
        app.querySelectorAll('.kivii-calendar__day.is-selected').forEach(e => e.classList.remove('is-selected'));
        btn.classList.add('is-selected');
        state.data.appointment_date = btn.dataset.date;
        state.data.appointment_time = '';
        state.data.drop_off_time = '';
        state.data.is_drop_off = document.querySelector('input[name="is_drop_off"]:checked')?.value === '1';
        if (state.data.is_drop_off) renderDropOff(); else renderSlots();
        updateSidebar();
    }

    function renderSlots() {
        document.getElementById('kivii-dropoff-times').style.display = 'none';
        const c = document.getElementById('kivii-timeslots'); c.style.display = '';
        const g = document.getElementById('kivii-slots-grid');
        g.innerHTML = mockSlots.map(t => `<button class="kivii-timeslot" data-time="${t}">${t}</button>`).join('');
        g.querySelectorAll('.kivii-timeslot').forEach(b => b.addEventListener('click', () => {
            g.querySelectorAll('.is-selected').forEach(e => e.classList.remove('is-selected'));
            b.classList.add('is-selected');
            state.data.appointment_time = b.dataset.time;
            updateSidebar();
        }));
    }

    function renderDropOff() {
        document.getElementById('kivii-timeslots').style.display = 'none';
        const c = document.getElementById('kivii-dropoff-times'); c.style.display = '';
        const g = document.getElementById('kivii-dropoff-grid');
        g.innerHTML = dropOffTimes.map(t => `<button class="kivii-timeslot" data-time="${t}">${t}</button>`).join('');
        g.querySelectorAll('.kivii-timeslot').forEach(b => b.addEventListener('click', () => {
            g.querySelectorAll('.is-selected').forEach(e => e.classList.remove('is-selected'));
            b.classList.add('is-selected');
            state.data.drop_off_time = b.dataset.time;
            updateSidebar();
        }));
    }

    document.querySelectorAll('input[name="is_drop_off"]').forEach(r => r.addEventListener('change', () => {
        state.data.is_drop_off = r.value === '1';
        state.data.appointment_time = ''; state.data.drop_off_time = '';
        if (state.data.appointment_date) { if (state.data.is_drop_off) renderDropOff(); else renderSlots(); }
    }));

    document.getElementById('kivii-cal-prev')?.addEventListener('click', () => { state.calendar.month--; if (state.calendar.month < 1) { state.calendar.month = 12; state.calendar.year--; } renderCalendar(); });
    document.getElementById('kivii-cal-next')?.addEventListener('click', () => { state.calendar.month++; if (state.calendar.month > 12) { state.calendar.month = 1; state.calendar.year++; } renderCalendar(); });

    // ── Sidebar ────────────────────────────
    function updateSidebar() {
        const pe = document.getElementById('overview-plate'), me = document.getElementById('overview-mileage');
        const vd = app.querySelector('#kivii-overview-vehicle .kivii-sidebar__data'), vp = app.querySelector('#kivii-overview-vehicle .kivii-sidebar__placeholder');
        if (state.data.license_plate) { if (pe) pe.textContent = state.data.license_plate; if (me) me.textContent = (state.data.mileage || 0).toLocaleString('nl-NL') + ' km'; if (vd) vd.style.display = ''; if (vp) vp.style.display = 'none'; }

        const sel = getSelDetails();
        const sl = document.getElementById('overview-services-list'), sc = document.getElementById('overview-service-count'), st = document.getElementById('overview-totals');
        if (sl) sl.innerHTML = sel.map(s => `<div class="kivii-sidebar__row"><span>${esc(s.title)}</span><strong>${fp(s.price)}</strong></div>`).join('');
        if (sc) { sc.textContent = sel.length; sc.style.display = sel.length > 0 ? '' : 'none'; }
        if (st && sel.length > 0) { st.style.display = ''; document.getElementById('overview-total-price').textContent = fp(sel.reduce((s, i) => s + i.price, 0)); document.getElementById('overview-total-duration').textContent = sel.reduce((s, i) => s + i.duration, 0) + ' min'; } else if (st) st.style.display = 'none';

        const de = document.getElementById('overview-date'), te = document.getElementById('overview-time');
        const td = app.querySelector('#kivii-overview-timeslot .kivii-sidebar__data'), tp = app.querySelector('#kivii-overview-timeslot .kivii-sidebar__placeholder');
        if (state.data.appointment_date) {
            if (de) de.textContent = fmtDate(state.data.appointment_date);
            const ts = state.data.is_drop_off ? (state.data.drop_off_time || '—') + ' (achterlaten)' : (state.data.appointment_time || '—');
            if (te) te.textContent = ts;
            if (td) td.style.display = ''; if (tp) tp.style.display = 'none';
        }
    }

    document.getElementById('kivii-sidebar-toggle')?.addEventListener('click', () => app.querySelector('.kivii-sidebar')?.classList.toggle('is-open'));

    // ── Submit ──────────────────────────────
    function doSubmit() {
        if (!validateStep(4)) return;
        submitBtn.disabled = true; submitBtn.textContent = 'Bezig...';
        setTimeout(() => {
            const ref = 'KIV-' + Math.random().toString(36).substr(2, 8).toUpperCase();
            document.getElementById('kivii-booking-ref').textContent = ref;
            goToStep(state.maxSteps + 1);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span>Afspraak inplannen</span>';
        }, 1500);
    }

    document.getElementById('kivii-new-booking')?.addEventListener('click', () => {
        state.data = { license_plate: '', mileage: '', selected_services: [], is_drop_off: false, appointment_date: '', appointment_time: '', drop_off_time: '', first_name: '', last_name: '', email: '', phone: '', street: '', house_number: '', house_addition: '', postal_code: '', city: '', remarks: '', privacy_accepted: false };
        servicesRendered = false;
        app.querySelectorAll('input:not([type="radio"]):not([type="checkbox"]), textarea').forEach(e => e.value = '');
        app.querySelectorAll('input[type="checkbox"]').forEach(e => e.checked = false);
        app.querySelectorAll('.kivii-service-card').forEach(c => { c.classList.remove('is-selected'); c.querySelector('.kivii-service-card__check').innerHTML = ''; });
        goToStep(1);
    });

    // ── Language switch ────────────────────
    document.getElementById('lang-switch')?.addEventListener('change', () => { servicesRendered = false; if (state.step === 2) renderServices(); if (state.step === 3) renderCalendar(); });

    // ── Init ───────────────────────────────
    goToStep(1);
})();
