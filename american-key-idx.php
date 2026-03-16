<?php
/**
 * Plugin Name: American Key IDX
 * Plugin URI:  https://americankeyaz.com
 * Description: Auto-generates branded Elementor landing pages for active FlexMLS IDX listings.
 * Version:     0.5.0
 * Author:      Niko Mitchell
 * License:     GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * v0.5.0 — Settings page + Spark API authentication.
 */

// ── Custom Post Type ──────────────────────────────────────────────────────────
add_action( 'init', 'ak_idx_register_post_type' );
function ak_idx_register_post_type() {
    register_post_type( 'ak_listing', array(
        'labels' => array(
            'name'          => 'Listings',
            'singular_name' => 'Listing',
        ),
        'public'       => true,
        'show_in_menu' => true,
        'supports'     => array( 'title', 'editor', 'thumbnail' ),
        'rewrite'      => array( 'slug' => 'listings' ),
        'has_archive'  => false,
        'menu_icon'    => 'dashicons-admin-home',
    ) );
}

// ── Meta Fields ───────────────────────────────────────────────────────────────
/**
 * Defines all meta fields the plugin uses.
 * Each key maps to a meta_key stored in wp_postmeta.
 * The underscore prefix hides them from the default Custom Fields UI.
 */
function ak_idx_meta_fields() {
    return array(
        '_ak_mls_id'        => 'MLS Listing ID',
        '_ak_mls_number'    => 'MLS Number (display)',
        '_ak_status'        => 'Listing Status',
        '_ak_price'         => 'List Price',
        '_ak_bedrooms'      => 'Bedrooms',
        '_ak_bathrooms'     => 'Bathrooms',
        '_ak_sqft'          => 'Square Footage',
        '_ak_lot_size'      => 'Lot Size (sqft)',
        '_ak_year_built'    => 'Year Built',
        '_ak_garage'        => 'Garage Spaces',
        '_ak_pool'          => 'Pool (Y/N)',
        '_ak_hoa_fee'       => 'HOA Fee',
        '_ak_property_type' => 'Property Type',
        '_ak_description'   => 'Public Remarks',
        '_ak_agent_name'    => 'Listing Agent Name',
        '_ak_agent_email'   => 'Listing Agent Email',
        '_ak_agent_phone'   => 'Listing Agent Phone',
        '_ak_photos'        => 'Photos (JSON array)',
        '_ak_days_on_market'=> 'Days on Market',
        '_ak_last_synced'   => 'Last Synced',
    );
}

/**
 * Saves all listing meta fields for a given post ID.
 * Accepts an array of field => value pairs.
 * Only saves keys that are in our defined field list.
 *
 * @param int   $post_id  The ak_listing post ID.
 * @param array $data     Associative array of meta_key => value.
 */
function ak_idx_save_meta( $post_id, $data ) {
    $allowed_keys = array_keys( ak_idx_meta_fields() );
    foreach ( $data as $key => $value ) {
        if ( in_array( $key, $allowed_keys, true ) ) {
            update_post_meta( $post_id, $key, $value );
        }
    }
    // Always stamp the sync time.
    update_post_meta( $post_id, '_ak_last_synced', current_time( 'mysql' ) );
}

/**
 * Retrieves all listing meta for a given post ID.
 * Returns an associative array of meta_key => value.
 *
 * @param int $post_id  The ak_listing post ID.
 * @return array
 */
function ak_idx_get_meta( $post_id ) {
    $fields = array();
    foreach ( array_keys( ak_idx_meta_fields() ) as $key ) {
        $fields[ $key ] = get_post_meta( $post_id, $key, true );
    }
    return $fields;
}

// ── Settings Page ─────────────────────────────────────────────────────────────
add_action( 'admin_menu', 'ak_idx_add_settings_page' );
function ak_idx_add_settings_page() {
    add_options_page(
        'American Key IDX Settings',
        'AK IDX',
        'manage_options',
        'ak-idx-settings',
        'ak_idx_render_settings_page'
    );
}

add_action( 'admin_init', 'ak_idx_register_settings' );
function ak_idx_register_settings() {
    register_setting( 'ak_idx_options', 'ak_idx_api_key', array(
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    register_setting( 'ak_idx_options', 'ak_idx_api_secret', array(
        'sanitize_callback' => 'sanitize_text_field',
    ) );
}

function ak_idx_render_settings_page() {
    $key    = get_option( 'ak_idx_api_key', '' );
    $secret = get_option( 'ak_idx_api_secret', '' );
    $token  = get_transient( 'ak_idx_spark_token' );
    ?>
    <div class="wrap">
        <h1>American Key IDX — Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'ak_idx_options' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="ak_idx_api_key">Spark API Key</label></th>
                    <td>
                        <input type="text" id="ak_idx_api_key" name="ak_idx_api_key"
                               value="<?php echo esc_attr( $key ); ?>"
                               class="regular-text" autocomplete="off">
                        <p class="description">Your FBS Activation Key (e.g. em132_key_1)</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="ak_idx_api_secret">Spark API Secret</label></th>
                    <td>
                        <input type="password" id="ak_idx_api_secret" name="ak_idx_api_secret"
                               value="<?php echo esc_attr( $secret ); ?>"
                               class="regular-text" autocomplete="off">
                        <p class="description">Your FBS Secret Code from the activation email</p>
                    </td>
                </tr>
            </table>
            <?php submit_button( 'Save Credentials' ); ?>
        </form>

        <hr>
        <h2>Connection Status</h2>
        <?php if ( $token ) : ?>
            <p>✅ <strong>Connected.</strong> Active session token stored.</p>
        <?php elseif ( $key && $secret ) : ?>
            <p>⚠️ Credentials saved but no active token yet. Use the Test button below.</p>
        <?php else : ?>
            <p>❌ No credentials saved yet.</p>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="ak_idx_test_connection">
            <?php wp_nonce_field( 'ak_idx_test_connection' ); ?>
            <?php submit_button( 'Test API Connection', 'secondary' ); ?>
        </form>
    </div>
    <?php
}

// Handle test connection form submission
add_action( 'admin_post_ak_idx_test_connection', 'ak_idx_handle_test_connection' );
function ak_idx_handle_test_connection() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized' );
    }
    check_admin_referer( 'ak_idx_test_connection' );

    // Force a fresh token request
    delete_transient( 'ak_idx_spark_token' );
    $token = ak_idx_get_token();

    if ( $token ) {
        // Try a real listings call
        $listings = ak_idx_fetch_listings();
        if ( is_wp_error( $listings ) ) {
            $msg = 'token_ok_listings_failed&error=' . urlencode( $listings->get_error_message() );
        } else {
            $msg = 'success&count=' . count( $listings );
        }
    } else {
        $msg = 'token_failed';
    }

    wp_redirect( admin_url( 'options-general.php?page=ak-idx-settings&test=' . $msg ) );
    exit;
}

// ── Spark API Authentication ───────────────────────────────────────────────────
/**
 * Gets a valid Spark API session token.
 * Spark API auth: POST /v1/session with ApiKey + ApiSig (MD5 of secret+ApiKey+key)
 * Token cached as transient for 55 minutes (idle timeout is 60 min).
 *
 * @return string|false
 */
function ak_idx_get_token() {
    $cached = get_transient( 'ak_idx_spark_token' );
    if ( $cached ) {
        return $cached;
    }

    $api_key    = get_option( 'ak_idx_api_key', '' );
    $api_secret = get_option( 'ak_idx_api_secret', '' );

    if ( empty( $api_key ) || empty( $api_secret ) ) {
        return false;
    }

    // Signature for session: MD5( secret + "ApiKey" + key )
    $api_sig = md5( $api_secret . 'ApiKey' . $api_key );

    $url = 'https://sparkapi.com/v1/session?ApiKey=' . urlencode( $api_key ) . '&ApiSig=' . $api_sig;

    $response = wp_remote_post( $url, array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'User-Agent'   => 'AmericanKeyIDX/0.5.0',
        ),
        'body'    => '',
        'timeout' => 20,
    ) );

    if ( is_wp_error( $response ) ) {
        return false;
    }

    $body  = json_decode( wp_remote_retrieve_body( $response ), true );
    $token = $body['D']['Results'][0]['AuthToken'] ?? false;

    if ( $token ) {
        // Cache for 55 minutes — idle timeout is 60 min
        set_transient( 'ak_idx_spark_token', $token, 55 * MINUTE_IN_SECONDS );
    }

    return $token;
}

// ── Spark API Listings Fetch ──────────────────────────────────────────────────
/**
 * Fetches active listings from the Spark API.
 * Signature: MD5( secret + ApiKey + key + ServicePath + path + params alphabetically )
 *
 * @return array|WP_Error
 */
function ak_idx_fetch_listings() {
    $token      = ak_idx_get_token();
    $api_key    = get_option( 'ak_idx_api_key', '' );
    $api_secret = get_option( 'ak_idx_api_secret', '' );

    if ( ! $token ) {
        return new WP_Error( 'no_token', 'Could not obtain a Spark API session token. Check your credentials in Settings > AK IDX.' );
    }

    $service_path = '/v1/listings';

    // Params sorted alphabetically — AuthToken always included
    $params = array(
        'AuthToken' => $token,
        '_filter'   => "MlsStatus Eq 'Active'",
        '_limit'    => '25',
    );
    ksort( $params );

    // Signature: secret + ApiKey + key + ServicePath + path + param1value1...
    $sig_string = $api_secret . 'ApiKey' . $api_key . 'ServicePath' . $service_path;
    foreach ( $params as $k => $v ) {
        $sig_string .= $k . $v;
    }
    $api_sig = md5( $sig_string );

    $url = 'https://sparkapi.com' . $service_path . '?' . http_build_query( $params ) . '&ApiSig=' . $api_sig;

    $response = wp_remote_get( $url, array(
        'headers' => array( 'User-Agent' => 'AmericanKeyIDX/0.5.0' ),
        'timeout' => 20,
    ) );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( $code === 401 ) {
        delete_transient( 'ak_idx_spark_token' );
        return new WP_Error( 'token_expired', 'Session token expired. Please test the connection again.' );
    }

    if ( $code !== 200 ) {
        $message = $body['D']['Message'] ?? wp_remote_retrieve_body( $response );
        return new WP_Error( 'api_error', 'Spark API HTTP ' . $code . ': ' . $message );
    }

    return $body['D']['Results'] ?? array();
}

// ── Admin Notice ──────────────────────────────────────────────────────────────
add_action( 'admin_notices', 'ak_idx_admin_notice' );
function ak_idx_admin_notice() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    echo '<div class="notice notice-success is-dismissible"><p><strong>American Key IDX v0.5.0</strong> is active.</p></div>';

    // Show test results if redirected back from test
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'ak-idx-settings' && isset( $_GET['test'] ) ) {
        $test = sanitize_text_field( $_GET['test'] );
        if ( strpos( $test, 'success' ) === 0 ) {
            $count = isset( $_GET['count'] ) ? (int) $_GET['count'] : 0;
            echo '<div class="notice notice-success is-dismissible"><p>✅ <strong>AK IDX:</strong> Connected! ' . $count . ' active listing(s) returned from Spark API.</p></div>';
        } elseif ( $test === 'token_failed' ) {
            echo '<div class="notice notice-error is-dismissible"><p>❌ <strong>AK IDX:</strong> Authentication failed. Check your API key and secret in <a href="' . admin_url( 'options-general.php?page=ak-idx-settings' ) . '">Settings > AK IDX</a>.</p></div>';
        } elseif ( strpos( $test, 'token_ok_listings_failed' ) === 0 ) {
            $error = isset( $_GET['error'] ) ? sanitize_text_field( urldecode( $_GET['error'] ) ) : 'Unknown error';
            echo '<div class="notice notice-warning is-dismissible"><p>⚠️ <strong>AK IDX:</strong> Token obtained but listings call failed: ' . esc_html( $error ) . '</p></div>';
        }
    }
}

// Settings link on plugins page
add_filter( 'plugin_action_links_american-key-realty-idx-plugin/american-key-idx.php', 'ak_idx_action_links' );
function ak_idx_action_links( $links ) {
    $settings_url = admin_url( 'options-general.php?page=ak-idx-settings' );
    array_unshift( $links, '<a href="' . esc_url( $settings_url ) . '">Settings</a>' );
    return $links;
}
register_activation_hook( __FILE__, 'ak_idx_activate' );
function ak_idx_activate() {
    ak_idx_register_post_type();
    flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'ak_idx_deactivate' );
function ak_idx_deactivate() {
    flush_rewrite_rules();
}


