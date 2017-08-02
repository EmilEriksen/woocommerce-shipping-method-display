<?php

namespace WSMD\Excluder;

/**
 * Admin settings for excluder.
 */
class Admin {
    /**
     * Shipping methods.
     *
     * @var WC_Shipping_Method[]
     */
    private $shipping_methods = [];

    /**
     * Initialize.
     *
     * @return void
     */
    public function init() {
        global $wpdb;

        // Initialize shipping methods.
        $shipping_method_instance_ids = \wp_cache_get( 'shipping_method_instance_ids', 'wsmd' );

        if ( false === $shipping_method_instance_ids ) {
            $shipping_method_instance_ids = $wpdb->get_results( "SELECT instance_id FROM {$wpdb->prefix}woocommerce_shipping_zone_methods" );

            \wp_cache_set( 'shipping_method_instance_ids', $shipping_method_instance_ids, 'wsmd' );
        }

        if ( empty( $shipping_method_instance_ids ) ) {
            return;
        }

        foreach ( $shipping_method_instance_ids as $row ) {
            $this->shipping_methods[] = \WC_Shipping_Zones::get_shipping_method( $row->instance_id );
        }

        if ( empty( $this->shipping_methods ) ) {
            return;
        }

        // Add filter to shipping methods form fields.
        $shipping_method_class_names = WC()->shipping->get_shipping_method_class_names();

        foreach ( $shipping_method_class_names as $method_id => $class_name ) {
            add_filter( 'woocommerce_shipping_instance_form_fields_' . $method_id, array( $this, 'add_exclusions_field' ) );
        }
    }

    /**
     * Add field for excluding other shipping methods when available.
     *
     * @param array $fields Form fields.
     *
     * @return array
     */
    public function add_exclusions_field( $fields ) : array {
        $shipping_methods = [ 'all' => __( 'All others', 'wsmd' ) ];

        foreach ( $this->shipping_methods as $shipping_method ) {
            $shipping_methods[ $shipping_method->get_instance_id() ] = $shipping_method->get_title() . ' [' . $shipping_method->get_method_title() . ']';
        }

        $fields['excluded_methods'] = [
            'title'   => __( 'Hide when available', 'wsmd' ),
            'type'    => 'multiselect',
            'options' => $shipping_methods,
        ];

        return $fields;
    }
}
