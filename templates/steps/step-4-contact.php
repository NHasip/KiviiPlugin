<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="kivii-step-content">
    <h2 class="kivii-step__title" data-text="step4_title">Uw contactgegevens</h2>
    <p class="kivii-step__subtitle" data-text="step4_subtitle">Vul uw gegevens in om de afspraak te bevestigen.</p>

    <div class="kivii-form-row">
        <div class="kivii-form-group kivii-form-group--half">
            <label for="kivii-firstname" class="kivii-label" data-text="first_name">Voornaam</label>
            <input type="text" id="kivii-firstname" class="kivii-input" name="first_name" required>
            <span class="kivii-error" id="error-first_name"></span>
        </div>
        <div class="kivii-form-group kivii-form-group--half">
            <label for="kivii-lastname" class="kivii-label" data-text="last_name">Achternaam</label>
            <input type="text" id="kivii-lastname" class="kivii-input" name="last_name" required>
            <span class="kivii-error" id="error-last_name"></span>
        </div>
    </div>

    <div class="kivii-form-row">
        <div class="kivii-form-group kivii-form-group--half">
            <label for="kivii-email" class="kivii-label" data-text="email">E-mailadres</label>
            <input type="email" id="kivii-email" class="kivii-input" name="email" required>
            <span class="kivii-error" id="error-email"></span>
        </div>
        <div class="kivii-form-group kivii-form-group--half">
            <label for="kivii-phone" class="kivii-label" data-text="phone">Telefoonnummer</label>
            <input type="tel" id="kivii-phone" class="kivii-input" name="phone" required>
            <span class="kivii-error" id="error-phone"></span>
        </div>
    </div>

    <div class="kivii-form-row kivii-form-row--address">
        <div class="kivii-form-group kivii-form-group--street">
            <label for="kivii-street" class="kivii-label" data-text="street">Straat</label>
            <input type="text" id="kivii-street" class="kivii-input" name="street" required>
            <span class="kivii-error" id="error-street"></span>
        </div>
        <div class="kivii-form-group kivii-form-group--housenr">
            <label for="kivii-housenumber" class="kivii-label" data-text="house_number">Huisnr.</label>
            <input type="text" id="kivii-housenumber" class="kivii-input" name="house_number" required>
            <span class="kivii-error" id="error-house_number"></span>
        </div>
        <div class="kivii-form-group kivii-form-group--addition">
            <label for="kivii-addition" class="kivii-label" data-text="house_addition">Toev.</label>
            <input type="text" id="kivii-addition" class="kivii-input" name="house_addition">
        </div>
    </div>

    <div class="kivii-form-row">
        <div class="kivii-form-group kivii-form-group--third">
            <label for="kivii-postalcode" class="kivii-label" data-text="postal_code">Postcode</label>
            <input type="text" id="kivii-postalcode" class="kivii-input" name="postal_code" maxlength="7" required>
            <span class="kivii-error" id="error-postal_code"></span>
        </div>
        <div class="kivii-form-group kivii-form-group--twothirds">
            <label for="kivii-city" class="kivii-label" data-text="city">Woonplaats</label>
            <input type="text" id="kivii-city" class="kivii-input" name="city" required>
            <span class="kivii-error" id="error-city"></span>
        </div>
    </div>

    <div class="kivii-form-group">
        <label for="kivii-remarks" class="kivii-label" data-text="remarks">Opmerkingen</label>
        <textarea id="kivii-remarks" class="kivii-input kivii-textarea" name="remarks" rows="3" data-placeholder="remarks_placeholder"></textarea>
    </div>

    <div class="kivii-form-group kivii-form-group--checkbox">
        <label class="kivii-checkbox">
            <input type="checkbox" id="kivii-privacy" name="privacy_accepted" value="1" required>
            <span class="kivii-checkbox__mark"></span>
            <span class="kivii-checkbox__text">
                <span data-text="privacy_label">Ik ga akkoord met de</span>
                <a href="#" id="kivii-privacy-link" target="_blank" data-text="privacy_link_text">voorwaarden</a>
            </span>
        </label>
        <span class="kivii-error" id="error-privacy_accepted"></span>
    </div>
</div>
