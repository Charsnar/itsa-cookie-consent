<?php
/**
 * Plugin Name: ITSA Cookie Consent
 * Plugin URI:  https://itsaustralia.com.au
 * Description: Granular cookie consent, script blocking (GA4/GTM), and admin controls branded for ITSA - I Technical Services Australia.
 * Version:     2.0.0
 * Author:      ITSA - I Technical Services Australia
 * Author URI:  https://itsaustralia.com.au
 * License:     GPL-2.0-or-later
 * Text Domain: itsa-cookie-consent
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ITSA_CC_VERSION', '2.0.0' );
define( 'ITSA_CC_PATH', plugin_dir_path( __FILE__ ) );
define( 'ITSA_CC_URL', plugin_dir_url( __FILE__ ) );

require_once ITSA_CC_PATH . 'includes/admin-settings.php';

/**
 * Default plugin options.
 */
function itsa_cc_get_options() {
    $defaults = array(
        'banner_title'   => 'ITSA - I Technical Services Australia',
        'banner_message' => 'We use cookies to improve site performance, analyze traffic, and support marketing efforts. Choose your preferences below.',
        'privacy_url'    => home_url( '/privacy-policy/' ),
        'primary_color'  => '#0284c7',
        'ga4_id'         => '',
        'gtm_id'         => '',
        'expiry_days'    => 180,
    );

    $saved = get_option( 'itsa_cc_settings', array() );
    return wp_parse_args( $saved, $defaults );
}

/**
 * Enqueue frontend styles and scripts.
 */
function itsa_cc_enqueue_assets() {
    $options = itsa_cc_get_options();

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
            'cookieName' => 'itsa_cookie_consent',
            'expiryDays' => absint( $options['expiry_days'] ),
            'ga4Id'      => sanitize_text_field( $options['ga4_id'] ),
            'gtmId'      => sanitize_text_field( $options['gtm_id'] ),
        )
    );
}
add_action( 'wp_enqueue_scripts', 'itsa_cc_enqueue_assets' );

/**
 * Output blocked GA4 / GTM templates in <head> until consent is verified.
 */
function itsa_cc_output_blocked_scripts() {
    $options = itsa_cc_get_options();
    $ga4_id  = sanitize_text_field( $options['ga4_id'] );
    $gtm_id  = sanitize_text_field( $options['gtm_id'] );

    if ( ! empty( $ga4_id ) ) : ?>
        <script type="text/plain" data-itsa-category="analytics" data-itsa-src="https://googletagmanager.com<?php echo esc_attr( $ga4_id ); ?>"></script>
        <script type="text/plain" data-itsa-category="analytics">
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?php echo esc_js( $ga4_id ); ?>');
        </script>
    <?php endif;

    if ( ! empty( $gtm_id ) ) : ?>
        <script type="text/plain" data-itsa-category="marketing">
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://googletagmanager.com;
            })(window,document,'script','dataLayer','<?php echo esc_js( $gtm_id ); ?>');
        </script>
    <?php endif;
}
add_action( 'wp_head', 'itsa_cc_output_blocked_scripts', 1 );

/**
 * Render the consent banner and granular preferences modal.
 */
function itsa_cc_render_banner() {
    $options       = itsa_cc_get_options();
    $primary_color = esc_attr( $options['primary_color'] );
    ?>
    <style>
        :root { --itsa-primary: <?php echo $primary_color; ?>; }
    </style>

    <!-- Floating Re-open Button (visible after initial choice) -->
    <button type="button" id="itsa-cc-reopen" class="itsa-cc-reopen" hidden aria-label="Manage Cookie Preferences">
        Cookie Settings
    </button>

    <!-- Main Cookie Banner -->
    <div id="itsa-cookie-banner" class="itsa-cc-banner" role="dialog" aria-live="polite" aria-label="Cookie consent" hidden>
        <div class="itsa-cc-inner">
            <div class="itsa-cc-text">
                <p class="itsa-cc-title"><?php echo esc_html( $options['banner_title'] ); ?></p>
                <p class="itsa-cc-message">
                    <?php echo esc_html( $options['banner_message'] ); ?>
                    <?php if ( ! empty( $options['privacy_url'] ) ) : ?>
                        <a href="<?php echo esc_url( $options['privacy_url'] ); ?>">Privacy Policy</a>
                    <?php endif; ?>
                </p>
            </div>
            <div class="itsa-cc-actions">
                <button type="button" id="itsa-cc-customize" class="itsa-cc-btn itsa-cc-btn-outline">
                    Customize
                </button>
                <button type="button" id="itsa-cc-reject" class="itsa-cc-btn itsa-cc-btn-secondary">
                    Essential Only
                </button>
                <button type="button" id="itsa-cc-accept" class="itsa-cc-btn itsa-cc-btn-primary">
                    Accept All
                </button>
            </div>
        </div>
    </div>

    <!-- Granular Preferences Modal -->
    <div id="itsa-cc-modal" class="itsa-cc-modal" role="dialog" aria-modal="true" aria-labelledby="itsa-modal-heading" hidden>
        <div class="itsa-cc-modal-box">
            <div class="itsa-cc-modal-header">
                <h2 id="itsa-modal-heading"><?php echo esc_html( $options['banner_title'] ); ?> — Cookie Preferences</h2>
                <button type="button" id="itsa-cc-close-modal" class="itsa-cc-close" aria-label="Close">&times;</button>
            </div>

            <div class="itsa-cc-modal-body">
                <div class="itsa-cc-category">
                    <div class="itsa-cc-cat-header">
                        <strong>Essential Cookies</strong>
                        <span class="itsa-cc-badge">Always Active</span>
                    </div>
                    <p>Required for core website functionality, security, and saving your consent preferences.</p>
                </div>

                <div class="itsa-cc-category">
                    <div class="itsa-cc-cat-header">
                        <label for="itsa-cat-analytics"><strong>Analytics Cookies (Google Analytics)</strong></label>
                        <input type="checkbox" id="itsa-cat-analytics">
                    </div>
                    <p>Helps us understand how visitors interact with our website by collecting anonymous usage data.</p>
                </div>

                <div class="itsa-cc-category">
                    <div class="itsa-cc-cat-header">
                        <label for="itsa-cat-marketing"><strong>Marketing &amp; Tag Manager (GTM)</strong></label>
                        <input type="checkbox" id="itsa-cat-marketing">
                    </div>
                    <p>Used to deliver relevant advertisements and manage third-party marketing tags.</p>
                </div>
            </div>

            <div class="itsa-cc-modal-footer">
                <button type="button" id="itsa-cc-save-prefs" class="itsa-cc-btn itsa-cc-btn-primary">
                    Save Preferences
                </button>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'itsa_cc_render_banner' );
