/**
 * Kivii Online Afspraak – Frontend JavaScript
 * Booking flow: Vehicle → Services → Timeslot → Contact → Confirmation
 */
(function() {
    'use strict';

    const app = document.getElementById('kivii-booking-app');
    if (!app) return;

    // ── State ──────────────────────────────
    const state = {
        step: 1,
        maxSteps: 4,
        data: {
            license_plate: '',
            mileage: '',
            selected_services: [],
            is_drop_off: false,
            appointment_date: '',
            appointment_time: '',
            drop_off_time: '',
            first_name: '',
            last_name: '',
            email: '',
            phone: '',
            street: '',
            house_number: '',
            house_addition: '',
            postal_code: '',
            city: '',
            remarks: '',
            privacy_accepted: false,
        },
        services: [],
        calendar: { month: new Date().getMonth() + 1, year: new Date().getFullYear() },
        availableDays: {},
        availableSlots: [],
    };

    // ── Config from WP ─────────────────────
    const cfg = window.kiviiData || {};
    const apiBase = cfg.restUrl || '/wp-json/kiviiweb/v1';
    const nonce = cfg.nonce || '';
    const lang = cfg.lang || cfg.language || 'nl';
    const texts = cfg.texts || {};
    const dropOffTimes = cfg.dropOffTimes || ['09:00','13:00'];

    // ── Helper: API calls ──────────────────
    async function apiFetch(endpoint, options = {}) {
        const url = `${apiBase}${endpoint}`;
        const headers = { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' };
        try {
            const resp = await fetch(url, { headers, ...options });
            return await resp.json();
        } catch (err) {
            console.error('Kivii API error:', err);
            return { success: false, message: 'Netwerkfout' };
        }
    }

    // ── Helper: Text translation ───────────
    function t(key, fallback) {
        return texts[key] || fallback || key;
    }

    function applyTexts() {
        app.querySelectorAll('[data-text]').forEach(el => {
            const key = el.getAttribute('data-text');
            if (texts[key]) el.textContent = texts[key];
        });
    }

    // ── Helper: Format ─────────────────────
    function formatPrice(price) {
        return '€ ' + parseFloat(price).toFixed(2).replace('.', ',');
    }

    function serviceField(item, key) {
        return item[`${key}_${lang}`] || item[`${key}_nl`] || '';
    }

    function getServiceById(id) {
        for (const category of state.services) {
            const match = (category.services || []).find(service => service.id === id);
            if (match) {
                return match;
            }
        }

        return null;
    }

    function getServicePriceDisplay(service) {
        const priceLabel = serviceField(service, 'price_label');

        if (priceLabel) {
            return priceLabel;
        }

        if (parseFloat(service.price) > 0) {
            return formatPrice(service.price);
        }

        return t('price_on_request', 'Op aanvraag');
    }

    function getServiceDurationLabel(service) {
        const minutes = parseInt(service.duration_minutes, 10) || 0;
        return minutes > 0 ? `${minutes} min` : t('duration_on_request', 'In overleg');
    }

    function isSingleSelectCategory(category) {
        const categoryName = serviceField(category, 'name').trim().toLowerCase();
        return categoryName === 'onderhoud' || categoryName === 'maintenance';
    }

    function formatDate(dateStr) {
        const d = new Date(dateStr + 'T00:00:00');
        const days = lang === 'nl'
            ? ['zondag','maandag','dinsdag','woensdag','donderdag','vrijdag','zaterdag']
            : ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        const months = lang === 'nl'
            ? ['januari','februari','maart','april','mei','juni','juli','augustus','september','oktober','november','december']
            : ['January','February','March','April','May','June','July','August','September','October','November','December'];
        return `${days[d.getDay()]} ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
    }

    // ── Step Navigation ────────────────────
    const steps = app.querySelectorAll('.kivii-step');
    const progSteps = app.querySelectorAll('.kivii-progress__step');
    const progFill = app.querySelector('.kivii-progress__fill');
    const prevBtn = document.getElementById('kivii-prev');
    const nextBtn = document.getElementById('kivii-next');
    const submitBtn = document.getElementById('kivii-submit');
    const navEl = document.getElementById('kivii-nav');

    function goToStep(n) {
        if (n < 1 || n > state.maxSteps + 1) return;
        state.step = n;

        // Steps
        steps.forEach(s => s.classList.toggle('is-active', parseInt(s.dataset.step) === n || (n > state.maxSteps && s.dataset.step === 'confirm')));

        // Progress
        progSteps.forEach(s => {
            const sn = parseInt(s.dataset.step);
            s.classList.toggle('is-active', sn === n);
            s.classList.toggle('is-completed', sn < n);
        });
        progFill.style.width = Math.min(100, (n / state.maxSteps) * 100) + '%';

        // Nav buttons
        prevBtn.style.display = n > 1 && n <= state.maxSteps ? '' : 'none';
        nextBtn.style.display = n < state.maxSteps ? '' : 'none';
        submitBtn.style.display = n === state.maxSteps ? '' : 'none';
        navEl.style.display = n > state.maxSteps ? 'none' : '';

        // Step-specific actions
        if (n === 2) loadServices();
        if (n === 3) loadCalendar();

        updateSidebar();
        app.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    prevBtn.addEventListener('click', () => goToStep(state.step - 1));
    nextBtn.addEventListener('click', () => { if (validateStep(state.step)) goToStep(state.step + 1); });
    submitBtn.addEventListener('click', submitBooking);

    // Allow clicking progress steps
    progSteps.forEach(s => {
        s.addEventListener('click', () => {
            const target = parseInt(s.dataset.step);
            if (target < state.step) goToStep(target);
        });
    });

    // ── Validation ─────────────────────────
    function clearErrors() {
        app.querySelectorAll('.kivii-error').forEach(e => e.textContent = '');
        app.querySelectorAll('.has-error').forEach(e => e.classList.remove('has-error'));
    }

    function showError(field, msg) {
        const el = document.getElementById('error-' + field);
        if (el) el.textContent = msg;
        const input = app.querySelector(`[name="${field}"]`);
        if (input) input.classList.add('has-error');
    }

    function validateStep(n) {
        clearErrors();
        let valid = true;

        if (n === 1) {
            const plate = document.getElementById('kivii-plate').value.trim();
            const mileage = document.getElementById('kivii-mileage').value;
            if (!plate || plate.length < 6) { showError('license_plate', t('error_plate', 'Voer een geldig kenteken in.')); valid = false; }
            if (!mileage || parseInt(mileage) < 0) { showError('mileage', t('error_mileage', 'Voer een geldige km-stand in.')); valid = false; }
            if (valid) {
                state.data.license_plate = plate.toUpperCase();
                state.data.mileage = parseInt(mileage);
            }
        }

        if (n === 2) {
            collectSelectedServices();
            if (state.data.selected_services.length === 0) {
                showError('services', t('error_services', 'Selecteer minimaal één werkzaamheid.'));
                valid = false;
            }
        }

        if (n === 3) {
            if (!state.data.appointment_date) { showError('appointment_date', t('error_date', 'Selecteer een datum.')); valid = false; }
            if (!state.data.is_drop_off && !state.data.appointment_time) { showError('appointment_time', t('error_time', 'Selecteer een tijdstip.')); valid = false; }
            if (state.data.is_drop_off && !state.data.drop_off_time) { showError('drop_off_time', t('error_drop_off', 'Selecteer een brengmoment.')); valid = false; }
        }

        if (n === 4) {
            const fields = {
                first_name: { el: 'kivii-firstname', msg: t('error_firstname', 'Voornaam is verplicht.') },
                last_name: { el: 'kivii-lastname', msg: t('error_lastname', 'Achternaam is verplicht.') },
                email: { el: 'kivii-email', msg: t('error_email', 'Voer een geldig e-mailadres in.'), validator: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) },
                phone: { el: 'kivii-phone', msg: t('error_phone', 'Voer een geldig telefoonnummer in.'), validator: v => v.replace(/\D/g,'').length >= 10 },
                street: { el: 'kivii-street', msg: t('error_street', 'Straat is verplicht.') },
                house_number: { el: 'kivii-housenumber', msg: t('error_housenumber', 'Huisnummer is verplicht.') },
                postal_code: { el: 'kivii-postalcode', msg: t('error_postal', 'Voer een geldige postcode in.') },
                city: { el: 'kivii-city', msg: t('error_city', 'Woonplaats is verplicht.') },
            };

            for (const [key, f] of Object.entries(fields)) {
                const val = document.getElementById(f.el).value.trim();
                if (!val || (f.validator && !f.validator(val))) {
                    showError(key, f.msg);
                    valid = false;
                } else {
                    state.data[key] = val;
                }
            }

            state.data.house_addition = document.getElementById('kivii-addition').value.trim();
            state.data.remarks = document.getElementById('kivii-remarks').value.trim();

            if (!document.getElementById('kivii-privacy').checked) {
                showError('privacy_accepted', t('error_privacy', 'U dient akkoord te gaan met de voorwaarden.'));
                valid = false;
            }
            state.data.privacy_accepted = document.getElementById('kivii-privacy').checked;
        }

        return valid;
    }

    // ── Step 2: Services ───────────────────
    async function loadServices() {
        if (state.services.length > 0) return; // Already loaded
        const container = document.getElementById('kivii-services-container');
        container.innerHTML = '<div class="kivii-loading">' + t('loading', 'Laden...') + '</div>';

        const resp = await apiFetch('/services');
        if (resp.success && resp.data) {
            state.services = resp.data;
            renderServices();
        } else {
            container.innerHTML = '<p style="color:var(--kivii-error)">Kon werkzaamheden niet laden.</p>';
        }
    }

    function renderServices() {
        const container = document.getElementById('kivii-services-container');
        let html = '';

        for (const cat of state.services) {
            const categoryTitle = serviceField(cat, 'name');
            const categoryDescription = serviceField(cat, 'description');
            const singleSelect = isSingleSelectCategory(cat);

            html += `<div class="kivii-service-category" data-selection-mode="${singleSelect ? 'single' : 'multiple'}">`;
            html += `<h3 class="kivii-service-category__title">${escHtml(categoryTitle)}</h3>`;
            if (categoryDescription) {
                html += `<p class="kivii-service-category__desc">${escHtml(categoryDescription)}</p>`;
            }

            for (const svc of (cat.services || [])) {
                const isSelected = state.data.selected_services.includes(svc.id);
                const title = serviceField(svc, 'title');
                const desc = serviceField(svc, 'description');
                const priceDisplay = getServicePriceDisplay(svc);
                const durationDisplay = getServiceDurationLabel(svc);

                html += `
                <div class="kivii-service-card${isSelected ? ' is-selected' : ''}" data-id="${svc.id}">
                    <div class="kivii-service-card__check">
                        ${isSelected ? '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M11.5 3.5L5.5 10.5L2.5 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' : ''}
                    </div>
                    <div class="kivii-service-card__info">
                        <div class="kivii-service-card__title">${escHtml(title)}</div>
                        ${desc ? `<div class="kivii-service-card__desc">${escHtml(desc)}</div>` : ''}
                    </div>
                    <div class="kivii-service-card__meta">
                        <div class="kivii-service-card__price">${escHtml(priceDisplay)}</div>
                        <div class="kivii-service-card__duration">${escHtml(durationDisplay)}</div>
                    </div>
                </div>`;
            }
            html += '</div>';
        }

        container.innerHTML = html;

        // Click handlers
        container.querySelectorAll('.kivii-service-card').forEach(card => {
            card.addEventListener('click', () => toggleService(card));
        });

        updateTotals();
    }

    function toggleService(card) {
        const category = card.closest('.kivii-service-category');
        const isSingleSelect = category?.dataset.selectionMode === 'single';
        const wasSelected = card.classList.contains('is-selected');

        if (isSingleSelect && !wasSelected) {
            category.querySelectorAll('.kivii-service-card.is-selected').forEach(otherCard => {
                otherCard.classList.remove('is-selected');
                const otherCheck = otherCard.querySelector('.kivii-service-card__check');
                if (otherCheck) {
                    otherCheck.innerHTML = '';
                }
            });
        }

        card.classList.toggle('is-selected');
        const check = card.querySelector('.kivii-service-card__check');
        if (card.classList.contains('is-selected')) {
            check.innerHTML = '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M11.5 3.5L5.5 10.5L2.5 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        } else {
            check.innerHTML = '';
        }
        collectSelectedServices();
        updateTotals();
        updateSidebar();
    }

    function collectSelectedServices() {
        state.data.selected_services = [];
        document.querySelectorAll('.kivii-service-card.is-selected').forEach(c => {
            state.data.selected_services.push(parseInt(c.dataset.id));
        });
    }

    function getSelectedDetails() {
        return state.data.selected_services
            .map(id => getServiceById(id))
            .filter(Boolean)
            .map(service => {
                const priceLabel = serviceField(service, 'price_label');

                return {
                    id: parseInt(service.id, 10),
                    price: parseFloat(service.price || 0),
                    duration: parseInt(service.duration_minutes || 0, 10),
                    title: serviceField(service, 'title'),
                    priceDisplay: getServicePriceDisplay(service),
                    hasFixedPrice: parseFloat(service.price || 0) > 0,
                    hasFromPrice: priceLabel.toLowerCase().startsWith('vanaf') || priceLabel.toLowerCase().startsWith('from'),
                };
            });
    }

    function updateTotals() {
        const selected = getSelectedDetails();
        const totalPrice = selected.reduce((s, i) => s + i.price, 0);
        const totalDuration = selected.reduce((s, i) => s + i.duration, 0);

        const priceEl = document.getElementById('kivii-total-price');
        const durEl = document.getElementById('kivii-total-duration');
        if (priceEl) {
            if (selected.length === 0) {
                priceEl.textContent = formatPrice(0);
            } else if (selected.some(item => !item.hasFixedPrice && !item.hasFromPrice)) {
                priceEl.textContent = t('price_on_request', 'Op aanvraag');
            } else if (selected.some(item => item.hasFromPrice)) {
                priceEl.textContent = `${lang === 'en' ? 'From' : 'Vanaf'} ${formatPrice(totalPrice)}`;
            } else {
                priceEl.textContent = formatPrice(totalPrice);
            }
        }

        if (durEl) {
            durEl.textContent = totalDuration > 0 ? `${totalDuration} min` : t('duration_on_request', 'In overleg');
        }
    }

    // ── Step 3: Calendar ───────────────────
    async function loadCalendar() {
        const totalDuration = getSelectedDetails().reduce((s, i) => s + i.duration, 0);
        const grid = document.getElementById('kivii-cal-grid');
        grid.innerHTML = '<div class="kivii-loading" style="grid-column:1/-1">' + t('loading', 'Laden...') + '</div>';

        const resp = await apiFetch(`/availability/days?month=${state.calendar.month}&year=${state.calendar.year}&duration=${totalDuration}`);
        if (resp.success) {
            state.availableDays = {};
            (resp.data.days || []).forEach(d => { state.availableDays[d.date] = d; });
        }

        renderCalendar();
    }

    function renderCalendar() {
        const monthNames = lang === 'nl'
            ? ['Januari','Februari','Maart','April','Mei','Juni','Juli','Augustus','September','Oktober','November','December']
            : ['January','February','March','April','May','June','July','August','September','October','November','December'];

        document.getElementById('kivii-cal-title').textContent = `${monthNames[state.calendar.month - 1]} ${state.calendar.year}`;

        const firstDay = new Date(state.calendar.year, state.calendar.month - 1, 1);
        const lastDay = new Date(state.calendar.year, state.calendar.month, 0);
        const startIdx = (firstDay.getDay() + 6) % 7; // Monday = 0
        const today = new Date(); today.setHours(0,0,0,0);

        const grid = document.getElementById('kivii-cal-grid');
        let html = '';

        // Empty cells
        for (let i = 0; i < startIdx; i++) html += '<button class="kivii-calendar__day is-empty" disabled></button>';

        for (let d = 1; d <= lastDay.getDate(); d++) {
            const dateStr = `${state.calendar.year}-${String(state.calendar.month).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const date = new Date(state.calendar.year, state.calendar.month - 1, d);
            const dayInfo = state.availableDays[dateStr];
            const isToday = date.getTime() === today.getTime();
            const isPast = date < today;
            const isWeekend = date.getDay() === 0 || date.getDay() === 6;

            let cls = 'kivii-calendar__day';
            if (isToday) cls += ' is-today';
            if (isPast || isWeekend) cls += ' is-disabled';
            if (dateStr === state.data.appointment_date) cls += ' is-selected';

            if (dayInfo && !isPast && !isWeekend) {
                if (dayInfo.status === 'available') cls += ' has-availability';
                else if (dayInfo.status === 'limited') cls += ' has-limited';
                else if (dayInfo.status === 'full') cls += ' has-full is-disabled';
            } else if (!isPast && !isWeekend && !dayInfo) {
                cls += ' is-disabled'; // No data = not available
            }

            html += `<button class="${cls}" data-date="${dateStr}" ${cls.includes('is-disabled') ? 'disabled' : ''}>${d}</button>`;
        }

        grid.innerHTML = html;

        // Click handlers
        grid.querySelectorAll('.kivii-calendar__day:not(.is-disabled):not(.is-empty)').forEach(btn => {
            btn.addEventListener('click', () => selectDate(btn));
        });
    }

    async function selectDate(btn) {
        // Remove previous selection
        app.querySelectorAll('.kivii-calendar__day.is-selected').forEach(el => el.classList.remove('is-selected'));
        btn.classList.add('is-selected');
        state.data.appointment_date = btn.dataset.date;
        state.data.appointment_time = '';
        state.data.drop_off_time = '';

        const isDropOff = document.querySelector('input[name="is_drop_off"]:checked')?.value === '1';
        state.data.is_drop_off = isDropOff;

        if (isDropOff) {
            renderDropOffTimes();
        } else {
            await loadTimeSlots();
        }

        updateSidebar();
    }

    async function loadTimeSlots() {
        const slotsContainer = document.getElementById('kivii-timeslots');
        const dropOffContainer = document.getElementById('kivii-dropoff-times');
        dropOffContainer.style.display = 'none';
        slotsContainer.style.display = '';

        const grid = document.getElementById('kivii-slots-grid');
        grid.innerHTML = '<div class="kivii-loading">' + t('loading', 'Laden...') + '</div>';

        const totalDuration = getSelectedDetails().reduce((s, i) => s + i.duration, 0);
        const resp = await apiFetch(`/availability/slots?date=${state.data.appointment_date}&duration=${totalDuration}`);

        if (resp.success) {
            state.availableSlots = resp.data.slots || [];
            renderTimeSlots();
        } else {
            grid.innerHTML = '<p>Geen tijdsloten beschikbaar.</p>';
        }
    }

    function renderTimeSlots() {
        const grid = document.getElementById('kivii-slots-grid');
        if (state.availableSlots.length === 0) {
            grid.innerHTML = '<p>' + t('no_slots', 'Geen tijdsloten beschikbaar op deze dag.') + '</p>';
            return;
        }

        grid.innerHTML = state.availableSlots.map(slot => {
            const time = typeof slot === 'string' ? slot : slot.time;
            const isSelected = time === state.data.appointment_time;
            return `<button class="kivii-timeslot${isSelected ? ' is-selected' : ''}" data-time="${time}">${time}</button>`;
        }).join('');

        grid.querySelectorAll('.kivii-timeslot').forEach(btn => {
            btn.addEventListener('click', () => {
                grid.querySelectorAll('.kivii-timeslot.is-selected').forEach(el => el.classList.remove('is-selected'));
                btn.classList.add('is-selected');
                state.data.appointment_time = btn.dataset.time;
                updateSidebar();
            });
        });
    }

    function renderDropOffTimes() {
        const slotsContainer = document.getElementById('kivii-timeslots');
        const dropOffContainer = document.getElementById('kivii-dropoff-times');
        slotsContainer.style.display = 'none';
        dropOffContainer.style.display = '';

        const grid = document.getElementById('kivii-dropoff-grid');
        grid.innerHTML = dropOffTimes.map(time => {
            const isSelected = time === state.data.drop_off_time;
            return `<button class="kivii-timeslot${isSelected ? ' is-selected' : ''}" data-time="${time}">${time}</button>`;
        }).join('');

        grid.querySelectorAll('.kivii-timeslot').forEach(btn => {
            btn.addEventListener('click', () => {
                grid.querySelectorAll('.kivii-timeslot.is-selected').forEach(el => el.classList.remove('is-selected'));
                btn.classList.add('is-selected');
                state.data.drop_off_time = btn.dataset.time;
                updateSidebar();
            });
        });
    }

    // Drop-off toggle
    document.querySelectorAll('input[name="is_drop_off"]').forEach(radio => {
        radio.addEventListener('change', () => {
            state.data.is_drop_off = radio.value === '1';
            state.data.appointment_time = '';
            state.data.drop_off_time = '';
            if (state.data.appointment_date) {
                if (state.data.is_drop_off) renderDropOffTimes();
                else loadTimeSlots();
            }
        });
    });

    // Calendar nav
    document.getElementById('kivii-cal-prev')?.addEventListener('click', () => {
        state.calendar.month--;
        if (state.calendar.month < 1) { state.calendar.month = 12; state.calendar.year--; }
        loadCalendar();
    });

    document.getElementById('kivii-cal-next')?.addEventListener('click', () => {
        state.calendar.month++;
        if (state.calendar.month > 12) { state.calendar.month = 1; state.calendar.year++; }
        loadCalendar();
    });

    // ── Sidebar ────────────────────────────
    function updateSidebar() {
        // Vehicle
        const plateEl = document.getElementById('overview-plate');
        const mileageEl = document.getElementById('overview-mileage');
        const vehData = app.querySelector('#kivii-overview-vehicle .kivii-sidebar__data');
        const vehPlaceholder = app.querySelector('#kivii-overview-vehicle .kivii-sidebar__placeholder');

        if (state.data.license_plate) {
            if (plateEl) plateEl.textContent = state.data.license_plate;
            if (mileageEl) mileageEl.textContent = (state.data.mileage || 0).toLocaleString('nl-NL') + ' km';
            if (vehData) vehData.style.display = '';
            if (vehPlaceholder) vehPlaceholder.style.display = 'none';
        }

        // Services
        const listEl = document.getElementById('overview-services-list');
        const countEl = document.getElementById('overview-service-count');
        const totalsEl = document.getElementById('overview-totals');
        const selected = getSelectedDetails();

        if (listEl) {
            if (selected.length > 0) {
                listEl.innerHTML = selected.map(s =>
                    `<div class="kivii-sidebar__row kivii-sidebar__row--service"><span>${escHtml(s.title)}</span><strong>${escHtml(s.priceDisplay)}</strong></div>`
                ).join('');
            } else {
                listEl.innerHTML = '';
            }
        }

        if (countEl) {
            countEl.textContent = selected.length;
            countEl.style.display = selected.length > 0 ? '' : 'none';
        }

        if (totalsEl) {
            const total = selected.reduce((s, i) => s + i.price, 0);
            const dur = selected.reduce((s, i) => s + i.duration, 0);
            document.getElementById('overview-total-price').textContent =
                selected.some(item => !item.hasFixedPrice && !item.hasFromPrice)
                    ? t('price_on_request', 'Op aanvraag')
                    : selected.some(item => item.hasFromPrice)
                        ? `${lang === 'en' ? 'From' : 'Vanaf'} ${formatPrice(total)}`
                        : formatPrice(total);
            document.getElementById('overview-total-duration').textContent = dur > 0 ? `${dur} min` : t('duration_on_request', 'In overleg');
            totalsEl.style.display = selected.length > 0 ? '' : 'none';
        }

        // Timeslot
        const dateEl = document.getElementById('overview-date');
        const timeEl = document.getElementById('overview-time');
        const tsData = app.querySelector('#kivii-overview-timeslot .kivii-sidebar__data');
        const tsPlaceholder = app.querySelector('#kivii-overview-timeslot .kivii-sidebar__placeholder');

        if (state.data.appointment_date) {
            if (dateEl) dateEl.textContent = formatDate(state.data.appointment_date);
            const timeStr = state.data.is_drop_off
                ? (state.data.drop_off_time || '—') + ' (' + t('drop_off_label', 'achterlaten') + ')'
                : (state.data.appointment_time || '—');
            if (timeEl) timeEl.textContent = timeStr;
            if (tsData) tsData.style.display = '';
            if (tsPlaceholder) tsPlaceholder.style.display = 'none';
        }
    }

    // Mobile sidebar toggle
    document.getElementById('kivii-sidebar-toggle')?.addEventListener('click', () => {
        app.querySelector('.kivii-sidebar')?.classList.toggle('is-open');
    });

    // ── Submit Booking ─────────────────────
    async function submitBooking() {
        if (!validateStep(4)) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="kivii-loading" style="padding:0">' + t('submitting', 'Bezig met inplannen...') + '</span>';

        const selected = getSelectedDetails();
        const payload = {
            ...state.data,
            services: state.data.selected_services,
            total_price: selected.reduce((s, i) => s + i.price, 0),
            total_duration: selected.reduce((s, i) => s + i.duration, 0),
            language: lang,
        };

        const resp = await apiFetch('/booking', {
            method: 'POST',
            body: JSON.stringify(payload),
        });

        if (resp.success) {
            document.getElementById('kivii-booking-ref').textContent = resp.reference || '—';
            goToStep(state.maxSteps + 1);
        } else {
            if (resp.errors) {
                clearErrors();
                for (const [field, msg] of Object.entries(resp.errors)) {
                    showError(field, msg);
                }
                // Go to first step with error
                const errorStepMap = { license_plate: 1, mileage: 1, services: 2, appointment_date: 3, appointment_time: 3, drop_off_time: 3 };
                for (const key of Object.keys(resp.errors)) {
                    if (errorStepMap[key]) { goToStep(errorStepMap[key]); break; }
                }
            } else {
                alert(resp.message || t('error_general', 'Er is een fout opgetreden. Probeer het opnieuw.'));
            }
        }

        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span data-text="submit_button">' + t('submit_button', 'Afspraak inplannen') + '</span><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.3 4.7L6 12L2.7 8.7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    }

    // New booking button
    document.getElementById('kivii-new-booking')?.addEventListener('click', () => {
        // Reset state
        state.data = { license_plate: '', mileage: '', selected_services: [], is_drop_off: false, appointment_date: '', appointment_time: '', drop_off_time: '', first_name: '', last_name: '', email: '', phone: '', street: '', house_number: '', house_addition: '', postal_code: '', city: '', remarks: '', privacy_accepted: false };
        state.services = [];
        // Reset form inputs
        app.querySelectorAll('input:not([type="radio"]):not([type="checkbox"]), textarea').forEach(el => el.value = '');
        app.querySelectorAll('input[type="checkbox"]').forEach(el => el.checked = false);
        goToStep(1);
    });

    // Privacy link
    const privacyLink = document.getElementById('kivii-privacy-link');
    if (privacyLink && app.dataset.privacyUrl) {
        privacyLink.href = app.dataset.privacyUrl;
    }

    // ── Utility ────────────────────────────
    function escHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ── Init ───────────────────────────────
    applyTexts();
    goToStep(1);

})();
