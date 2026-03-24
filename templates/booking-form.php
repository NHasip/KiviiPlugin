<?php
/**
 * Main booking form wrapper template.
 * Rendered by shortcode [kivii_online_afspraak] or Gutenberg block.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$privacy_url = get_option( 'kivii_general', [] )['privacy_url'] ?? '#';
?>
<div id="kivii-booking-app" class="kivii-booking" data-privacy-url="<?php echo esc_url( $privacy_url ); ?>">

    <!-- Progress bar -->
    <div class="kivii-progress" role="navigation" aria-label="Voortgang">
        <div class="kivii-progress__track">
            <div class="kivii-progress__fill"></div>
        </div>
        <div class="kivii-progress__steps">
            <button class="kivii-progress__step is-active" data-step="1">
                <span class="kivii-progress__dot"><span class="kivii-progress__num">1</span></span>
                <span class="kivii-progress__label" data-text="step_1_label">Autogegevens</span>
            </button>
            <button class="kivii-progress__step" data-step="2">
                <span class="kivii-progress__dot"><span class="kivii-progress__num">2</span></span>
                <span class="kivii-progress__label" data-text="step_2_label">Werkzaamheden</span>
            </button>
            <button class="kivii-progress__step" data-step="3">
                <span class="kivii-progress__dot"><span class="kivii-progress__num">3</span></span>
                <span class="kivii-progress__label" data-text="step_3_label">Tijdstip</span>
            </button>
            <button class="kivii-progress__step" data-step="4">
                <span class="kivii-progress__dot"><span class="kivii-progress__num">4</span></span>
                <span class="kivii-progress__label" data-text="step_4_label">Contactgegevens</span>
            </button>
        </div>
    </div>

    <!-- Main content area -->
    <div class="kivii-layout">

        <!-- Left column: Forms -->
        <div class="kivii-main">

            <!-- Step 1: Vehicle -->
            <div class="kivii-step is-active" data-step="1">
                <?php include KIVII_PLUGIN_DIR . 'templates/steps/step-1-vehicle.php'; ?>
            </div>

            <!-- Step 2: Services -->
            <div class="kivii-step" data-step="2">
                <?php include KIVII_PLUGIN_DIR . 'templates/steps/step-2-services.php'; ?>
            </div>

            <!-- Step 3: Timeslot -->
            <div class="kivii-step" data-step="3">
                <?php include KIVII_PLUGIN_DIR . 'templates/steps/step-3-timeslot.php'; ?>
            </div>

            <!-- Step 4: Contact -->
            <div class="kivii-step" data-step="4">
                <?php include KIVII_PLUGIN_DIR . 'templates/steps/step-4-contact.php'; ?>
            </div>

            <!-- Confirmation -->
            <div class="kivii-step" data-step="confirm">
                <?php include KIVII_PLUGIN_DIR . 'templates/steps/step-confirmation.php'; ?>
            </div>

            <!-- Navigation -->
            <div class="kivii-nav" id="kivii-nav">
                <button type="button" class="kivii-btn kivii-btn--outline" id="kivii-prev" style="display:none;">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span data-text="prev_button">Vorige</span>
                </button>
                <button type="button" class="kivii-btn kivii-btn--primary" id="kivii-next">
                    <span data-text="next_button">Volgende</span>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button type="button" class="kivii-btn kivii-btn--primary" id="kivii-submit" style="display:none;">
                    <span data-text="submit_button">Afspraak inplannen</span>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.3 4.7L6 12L2.7 8.7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
        </div>

        <!-- Right column: Sidebar overview -->
        <aside class="kivii-sidebar">
            <div class="kivii-sidebar__toggle" id="kivii-sidebar-toggle">
                <span data-text="overview_title">Overzicht</span>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div class="kivii-sidebar__content" id="kivii-sidebar-content">
                <h2 class="kivii-sidebar__title" data-text="overview_title">Overzicht</h2>

                <!-- Vehicle overview -->
                <div class="kivii-sidebar__section" id="kivii-overview-vehicle">
                    <div class="kivii-sidebar__header">
                        <span class="kivii-sidebar__icon">🚗</span>
                        <span class="kivii-sidebar__heading" data-text="overview_vehicle">Autogegevens</span>
                        <button class="kivii-sidebar__expand" aria-label="Toggle">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                    <div class="kivii-sidebar__body">
                        <p class="kivii-sidebar__placeholder" data-text="step1_subtitle">Voer uw kenteken en kilometerstand in.</p>
                        <div class="kivii-sidebar__data" style="display:none;">
                            <div class="kivii-sidebar__row"><span>Kenteken</span><strong id="overview-plate">—</strong></div>
                            <div class="kivii-sidebar__row"><span>Km-stand</span><strong id="overview-mileage">—</strong></div>
                        </div>
                    </div>
                </div>

                <!-- Services overview -->
                <div class="kivii-sidebar__section" id="kivii-overview-services">
                    <div class="kivii-sidebar__header">
                        <span class="kivii-sidebar__icon">🛠️</span>
                        <span class="kivii-sidebar__heading" data-text="overview_services">Werkzaamheden</span>
                        <span class="kivii-sidebar__count" id="overview-service-count" style="display:none;">0</span>
                        <button class="kivii-sidebar__expand" aria-label="Toggle">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                    <div class="kivii-sidebar__body">
                        <div id="overview-services-list"></div>
                        <div class="kivii-sidebar__total" id="overview-totals" style="display:none;">
                            <div class="kivii-sidebar__row"><span data-text="total_price_label">Totaal</span><strong id="overview-total-price">€ 0,00</strong></div>
                            <div class="kivii-sidebar__row"><span data-text="total_duration_label">Geschatte duur</span><strong id="overview-total-duration">0 min</strong></div>
                        </div>
                    </div>
                </div>

                <!-- Timeslot overview -->
                <div class="kivii-sidebar__section" id="kivii-overview-timeslot">
                    <div class="kivii-sidebar__header">
                        <span class="kivii-sidebar__icon">📅</span>
                        <span class="kivii-sidebar__heading" data-text="overview_timeslot">Tijdstip</span>
                        <button class="kivii-sidebar__expand" aria-label="Toggle">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                    <div class="kivii-sidebar__body">
                        <p class="kivii-sidebar__placeholder" data-text="step3_subtitle">Selecteer een beschikbare dag.</p>
                        <div class="kivii-sidebar__data" style="display:none;">
                            <div class="kivii-sidebar__row"><span>Datum</span><strong id="overview-date">—</strong></div>
                            <div class="kivii-sidebar__row"><span>Tijd</span><strong id="overview-time">—</strong></div>
                        </div>
                    </div>
                </div>

            </div>
        </aside>
    </div>
</div>
