<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function itsa_cc_add_admin_menu() {
    add_options_page(
        'ITSA Cookie Consent Settings',
        'ITSA Cookie Consent',
        'manage_options',
        'itsa-cookie-consent',
        'itsa_cc_render_settings_page'
    );
}
add_action( 'admin_menu', 'itsa_cc_add_admin_menu' );

function itsa_cc_register_settings() {
    register_setting(
        'itsa_cc_settings_group',
        'itsa_cc_settings',
        'itsa_cc_sanitize_settings'
    );
}
add_action( 'admin_init', 'itsa_cc_register_settings' );

function itsa_cc_sanitize_settings( $input ) {
    $sanitized = array();

    $sanitized['banner_title']   = sanitize_text_field( $input['banner_title'] ?? '' );
    $sanitized['banner_message'] = sanitize_textarea_field( $input['banner_message'] ?? '' );
    $sanitized['privacy_url']    = esc_url_raw( $input['privacy_url'] ?? '' );
    $sanitized['primary_color']  = sanitize_hex_color( $input['primary_color'] ?? '#0284c7' );
    $sanitized['ga4_id']         = sanitize_text_field( $input['ga4_id'] ?? '' );
    $sanitized['gtm_id']         = sanitize_text_field( $input['gtm_id'] ?? '' );
    $sanitized['expiry_days']    = max( 1, absint( $input['expiry_days'] ?? 180 ) );

    return $sanitized;
}

function itsa_cc_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $options = itsa_cc_get_options();
    ?>
    <div class="wrap">
        <h1>ITSA - I Technical Services Australia — Cookie Consent Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'itsa_cc_settings_group' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="itsa_banner_title">Banner Heading</label></th>
                    <td>
                        <input type="text" id="itsa_banner_title" name="itsa_cc_settings[banner_title]"
                               value="<?php echo esc_attr( $options['banner_title'] ); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="itsa_banner_message">Banner Message</label></th>
                    <td>
                        <textarea id="itsa_banner_message" name="itsa_cc_settings[banner_message]"
                                  rows="3" class="large-text"><?php echo esc_textarea( $options['banner_message'] ); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="itsa_privacy_url">Privacy Policy URL</label></th>
                    <td>
                        <input type="url" id="itsa_privacy_url" name="itsa_cc_settings[privacy_url]"
                               value="<?php echo esc_attr( $options['privacy_url'] ); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="itsa_primary_color">Primary Brand Color</label></th>
                    <td>
                        <input type="color" id="itsa_primary_color" name="itsa_cc_settings[primary_color]"
                               value="<?php echo esc_attr( $options['primary_color'] ); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="itsa_ga4_id">Google Analytics 4 ID</label></th>
                    <td>
                        <input type="text" id="itsa_ga4_id" name="itsa_cc_settings[ga4_id]"
                               placeholder="G-XXXXXXXXXX"
                               value="<?php echo esc_attr( $options['ga4_id'] ); ?>" class="regular-text">
                        <p class="description">Blocked until the visitor enables <strong>Analytics</strong> cookies.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="itsa_gtm_id">Google Tag Manager ID</label></th>
                    <td>
                        <input type="text" id="itsa_gtm_id" name="itsa_cc_settings[gtm_id]"
                               placeholder="GTM-XXXXXXX"
                               value="<?php echo esc_attr( $options['gtm_id'] ); ?>" class="regular-text">
                        <p class="description">Blocked until the visitor enables <strong>Marketing</strong> cookies.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="itsa_expiry_days">Consent Expiry (Days)</label></th>
                    <td>
                        <input type="number" id="itsa_expiry_days" name="itsa_cc_settings[expiry_days]"
                               min="1" max="365"
                               value="<?php echo esc_attr( $options['expiry_days'] ); ?>" class="small-text">
                    </td>
                </tr>
            </table>
            <?php submit_button( 'Save ITSA Consent Settings' ); ?>
        </form>
    </div>
    <?php
}
