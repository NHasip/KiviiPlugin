<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="kivii-step-content">
    <h2 class="kivii-step__title" data-text="step2_title">Selecteer uw werkzaamheden</h2>
    <p class="kivii-step__subtitle" data-text="step2_subtitle">Kies de werkzaamheden die aan uw auto uitgevoerd moeten worden.</p>

    <div id="kivii-services-container">
        <!-- Dynamically rendered by JS from kiviiData.services -->
        <div class="kivii-loading" data-text="loading">Laden...</div>
    </div>

    <span class="kivii-error" id="error-services"></span>

    <div class="kivii-totals">
        <div class="kivii-totals__row">
            <span data-text="total_duration_label">Geschatte duur</span>
            <strong id="kivii-total-duration">0 min</strong>
        </div>
        <div class="kivii-totals__row kivii-totals__row--primary">
            <span data-text="total_price_label">Totaal</span>
            <strong id="kivii-total-price">€ 0,00</strong>
        </div>
    </div>
</div>
