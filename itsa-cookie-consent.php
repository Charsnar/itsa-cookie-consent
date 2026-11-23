<?php
/**
 * Plugin Name: ITSA Cookie Consent
 * Plugin URI:  https://example.com
 * Description: Cookie consent banner branded for ITSA - I Technical Services Australia.
 * Version:     1.0.0
 * Author:      ITSA - I Technical Services Australia
 * License:     GPL-2.0-or-later
 * Text Domain: itsa-cookie-consent
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ITSA_CC_VERSION', '1.0.0' );
define( 'ITSA_CC_URL', plugin_dir_url( __FILE__ ) );

function itsa_cc_enqueue_assets() {
    if ( isset( $_COOKIE['itsa_cookie_consent'] ) ) {
        return;
    }

    wp_enqueue_style(
        'itsa-cookie-consent',
        ITSA_CC_URL . 'assets/itsa-consent.css',
        array(),
        ITSA_CC_VERSION
    );

    wp_enqueue_script(
        'itsa-cookie-consent',
        ITSA_CC_URL . 'assets/itsa-consent.js',
        array(),
        ITSA_CC_VERSION,
        true
    );

    wp_localize_script(
        'itsa-cookie-consent',
        'itsaConsentConfig',
        array(
            'cookieName'    => 'itsa_cookie_consent',
            'expiryDays'    => 180,
            'privacyUrl'    => home_url( '/privacy-policy/' ),
            'brandName'     => 'ITSA - I Technical Services Australia',
        )
    );
}
add_action( 'wp_enqueue_scripts', 'itsa_cc_enqueue_assets' );

function itsa_cc_render_banner() {
    if ( isset( $_COOKIE['itsa_cookie_consent'] ) ) {
        return;
    }

    $privacy_url = esc_url( home_url( '/privacy-policy/' ) );
    ?>
    <div id="itsa-cookie-banner" class="itsa-cc-banner" role="dialog" aria-live="polite" aria-label="Cookie consent">
        <div class="itsa-cc-inner">
            <div class="itsa-cc-text">
                <p class="itsa-cc-title">ITSA - I Technical Services Australia</p>
                <p class="itsa-cc-message">
                    We use cookies to keep this site running and improve your experience. See our
                    <a href="<?php echo $privacy_url; ?>">Privacy Policy</a> for details.
                </p>
            </div>
            <div class="itsa-cc-actions">
                <button type="button" id="itsa-cc-reject" class="itsa-cc-btn itsa-cc-btn-secondary">
                    Essential Only
                </button>
                <button type="button" id="itsa-cc-accept" class="itsa-cc-btn itsa-cc-btn-primary">
                    Accept All
                </button>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'itsa_cc_render_banner' );
