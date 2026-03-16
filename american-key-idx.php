<?php
/**
 * Plugin Name: American Key IDX
 * Plugin URI:  https://americankeyaz.com
 * Description: Auto-generates branded Elementor landing pages for active FlexMLS IDX listings.
 * Version:     0.1.0
 * Author:      Niko Mitchell
 * License:     GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * v0.1.0 — Minimal scaffold. Registers plugin, confirms activation, does nothing else.
 * All further features will be added incrementally and verified before each next step.
 */

// Register activation hook — confirms plugin loads without errors.
register_activation_hook( __FILE__, 'ak_idx_activate' );
function ak_idx_activate() {
    // Nothing yet — placeholder for future setup tasks (flush rewrite rules, etc.)
}

// Register deactivation hook.
register_deactivation_hook( __FILE__, 'ak_idx_deactivate' );
function ak_idx_deactivate() {
    // Nothing yet — placeholder for future cleanup tasks.
}

// Temporary admin notice to confirm the plugin is active and loaded correctly.
add_action( 'admin_notices', 'ak_idx_admin_notice' );
function ak_idx_admin_notice() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    echo '<div class="notice notice-success"><p><strong>American Key IDX v0.1.0</strong> is active and loaded correctly.</p></div>';
}
