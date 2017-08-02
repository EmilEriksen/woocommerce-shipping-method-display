<?php
/**
 * Plugin Name: WooCommerce Shipping Method Display
 * Version: 1.0.0
 * Description: Control how shipping methods are displayed.
 * Author: Emil Kjær Eriksen <hello@emileriksen.me>
 * Text Domain: wsmd
 * Domain Path: /languages/
 * License: GPL v3
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require __DIR__ . '/vendor/autoload.php';
}

/**
 * Main plugin function.
 *
 * @return array Plugin container.
 */
// @codingStandardsIgnoreLine
function WSMD() {
    static $container = null;

    if ( ! is_null( $container ) ) {
        return $container;
    }

    $container = [
        'Excluder' => [
            'Admin' => (function() {
                $admin = new WSMD\Excluder\Admin();

                add_action( 'woocommerce_init', array( $admin, 'init' ) );

                return $admin;
            })(),
            'Excluder' => (function() {
                $excluder = new WSMD\Excluder\Excluder();

                add_action( 'woocommerce_init', array( $excluder, 'init' ) );

                return $excluder;
            })(),
        ],
    ];

    return $container;
}

WSMD();
