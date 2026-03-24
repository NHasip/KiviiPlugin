<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="kivii-step-content">
    <h2 class="kivii-step__title" data-text="step3_title">Plan uw tijdstip</h2>
    <p class="kivii-step__subtitle" data-text="step3_subtitle">Selecteer een beschikbare dag.</p>

    <!-- Drop-off question -->
    <div class="kivii-form-group kivii-drop-off-question">
        <p class="kivii-label" data-text="drop_off_question">Laat u de auto achter?</p>
        <div class="kivii-toggle-group">
            <label class="kivii-toggle">
                <input type="radio" name="is_drop_off" value="1" id="kivii-drop-off-yes">
                <span class="kivii-toggle__label" data-text="drop_off_yes">Ja, ik laat de auto achter</span>
            </label>
            <label class="kivii-toggle">
                <input type="radio" name="is_drop_off" value="0" id="kivii-drop-off-no">
                <span class="kivii-toggle__label" data-text="drop_off_no">Nee, ik wacht op de auto</span>
            </label>
        </div>
        <span class="kivii-error" id="error-is_drop_off"></span>
    </div>

    <!-- Calendar -->
    <div class="kivii-calendar" id="kivii-calendar" style="display:none;">
        <div class="kivii-calendar__header">
            <button type="button" class="kivii-calendar__nav" id="kivii-cal-prev" aria-label="Vorige maand">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <h3 class="kivii-calendar__month" id="kivii-cal-title">Februari 2026</h3>
            <button type="button" class="kivii-calendar__nav" id="kivii-cal-next" aria-label="Volgende maand">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M7.5 5L12.5 10L7.5 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
        <div class="kivii-calendar__weekdays">
            <span>Ma</span><span>Di</span><span>Wo</span><span>Do</span><span>Vr</span><span>Za</span><span>Zo</span>
        </div>
        <div class="kivii-calendar__grid" id="kivii-cal-grid">
            <!-- Rendered by JS -->
        </div>
        <div class="kivii-calendar__legend">
            <span class="kivii-calendar__legend-item"><span class="kivii-dot kivii-dot--available"></span> <span data-text="day_available">Beschikbaar</span></span>
            <span class="kivii-calendar__legend-item"><span class="kivii-dot kivii-dot--almost"></span> <span data-text="day_almost_full">Bijna vol</span></span>
            <span class="kivii-calendar__legend-item"><span class="kivii-dot kivii-dot--full"></span> <span data-text="day_full">Vol</span></span>
        </div>
        <p class="kivii-calendar__note" id="kivii-cal-note" data-text="calendar_note">Wij houden altijd ruimte vrij voor spoedgevallen.</p>
    </div>

    <span class="kivii-error" id="error-appointment_date"></span>

    <!-- Time slots (shown when NOT drop-off) -->
    <div class="kivii-timeslots" id="kivii-timeslots" style="display:none;">
        <h3 class="kivii-timeslots__title" data-text="select_time">Kies een tijdstip</h3>
        <div class="kivii-timeslots__grid" id="kivii-slots-grid">
            <!-- Rendered by JS -->
        </div>
        <span class="kivii-error" id="error-appointment_time"></span>
    </div>

    <!-- Drop-off times (shown when IS drop-off) -->
    <div class="kivii-dropoff-times" id="kivii-dropoff-times" style="display:none;">
        <h3 class="kivii-timeslots__title" data-text="select_drop_off_time">Kies een brengmoment</h3>
        <div class="kivii-timeslots__grid" id="kivii-dropoff-grid">
            <!-- Rendered by JS from kiviiData.dropOffTimes -->
        </div>
        <span class="kivii-error" id="error-drop_off_time"></span>
    </div>
</div>
