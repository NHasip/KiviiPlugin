<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="kivii-step-content kivii-confirmation">
    <div class="kivii-confirmation__icon">
        <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
            <circle cx="40" cy="40" r="38" stroke="var(--kivii-success)" stroke-width="3" fill="none"/>
            <path d="M24 40L35 51L56 30" stroke="var(--kivii-success)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>
    <h2 class="kivii-confirmation__title" data-text="confirm_title">Afspraak bevestigd!</h2>
    <p class="kivii-confirmation__message" data-text="confirm_message">Uw afspraak is succesvol ingepland. U ontvangt een bevestiging per e-mail.</p>
    <div class="kivii-confirmation__reference">
        <span data-text="confirm_reference">Referentienummer</span>
        <strong id="kivii-booking-ref">—</strong>
    </div>
    <button type="button" class="kivii-btn kivii-btn--outline" id="kivii-new-booking" data-text="confirm_new">
        Nieuwe afspraak maken
    </button>
</div>
