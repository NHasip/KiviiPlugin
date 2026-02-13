<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="kivii-step-content">
    <h2 class="kivii-step__title" data-text="step1_title">Uw autogegevens</h2>
    <p class="kivii-step__subtitle" data-text="step1_subtitle">Voer uw kenteken en kilometerstand in.</p>

    <div class="kivii-form-group">
        <label for="kivii-plate" class="kivii-label" data-text="license_plate_label">Kenteken</label>
        <div class="kivii-plate-input">
            <span class="kivii-plate-input__prefix">NL</span>
            <input type="text"
                   id="kivii-plate"
                   class="kivii-input kivii-plate-input__field"
                   name="license_plate"
                   placeholder="XX-999-X"
                   autocomplete="off"
                   maxlength="10"
                   required>
        </div>
        <span class="kivii-error" id="error-license_plate"></span>
        <!-- RDW lookup placeholder: prepared for future extension -->
        <div id="kivii-rdw-data" class="kivii-rdw-data" style="display:none;"></div>
    </div>

    <div class="kivii-form-group">
        <label for="kivii-mileage" class="kivii-label" data-text="mileage_label">Km-stand</label>
        <div class="kivii-input-suffix">
            <input type="number"
                   id="kivii-mileage"
                   class="kivii-input"
                   name="mileage"
                   placeholder="85000"
                   min="0"
                   max="999999"
                   required>
            <span class="kivii-input-suffix__text">KM</span>
        </div>
        <span class="kivii-error" id="error-mileage"></span>
    </div>
</div>
