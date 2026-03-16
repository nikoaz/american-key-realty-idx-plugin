<?php
/**
 * Plugin Name: American Key IDX
 * Plugin URI:  https://americankeyaz.com
 * Description: Auto-generates branded Elementor landing pages for active FlexMLS IDX listings.
 * Version:     0.3.0
 * Author:      Niko Mitchell
 * License:     GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * v0.3.0 — Adds post meta storage for listing fields.
 */

// ── Custom Post Type ────────────────────────────────────────────────────────────────────────────────
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

// ── Meta Fields ───────────────────────────────────────────────────────────────────────────────────
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
    update_post_meta( $post_id, '_ak_last_synced', current_time( 'mysql' ) );
}

/**
 * Retrieves all listing meta for a given post ID.
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

// ── Activation / Deactivation ───────────────────────────────────────────────────────────────────
register_activation_hook( __FILE__, 'ak_idx_activate' );
function ak_idx_activate() {
    ak_idx_register_post_type();
    flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'ak_idx_deactivate' );
function ak_idx_deactivate() {
    flush_rewrite_rules();
}

// ── Admin Notice ──────────────────────────────────────────────────────────────────────────────────
add_action( 'admin_notices', 'ak_idx_admin_notice' );
function ak_idx_admin_notice() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $field_count = count( ak_idx_meta_fields() );
    echo '<div class="notice notice-success"><p><strong>American Key IDX v0.3.0</strong> is active — ' . $field_count . ' listing meta fields registered.</p></div>';
}
