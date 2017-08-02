<?php

namespace WSMD\Excluder;

/**
 * Hides specified shipping methods when a method is available.
 */
class Excluder {
    /**
     * Initialize.
     *
     * @return void
     */
    public function init() {
        add_filter( 'woocommerce_package_rates', array( $this, 'hide_excluded_shipping_methods' ), 100 );
    }

    /**
     * Hides specified shipping methods when a method is available.
     *
     * @param array $rates Shipping rates.
     *
     * @return array
     */
    public function hide_excluded_shipping_methods( $rates ) : array {
        $exclusions = [];

        foreach ( $rates as $rate_id => $rate ) {
            $instance_id = $this->get_rate_instance_id( $rate_id );

            if ( -1 === $instance_id ) {
                continue;
            }

            $shipping_method = \WC_Shipping_Zones::get_shipping_method( $instance_id );

            if ( ! $shipping_method ) {
                continue;
            }

            $excluded = $shipping_method->get_option( 'excluded_methods' );

            if ( empty( $excluded ) ) {
                continue;
            }

            if ( in_array( 'all', $excluded, true ) ) {
                $rates = [];
                $rates[ $rate_id ] = $rate;

                break;
            }

            $exclusions = array_merge( $exclusions, array_map( 'absint', $excluded ) );
        }

        foreach ( $rates as $rate_id => $rate ) {
            $instance_id = $this->get_rate_instance_id( $rate_id );

            if ( in_array( $instance_id, $exclusions, true ) ) {
                unset( $rates[ $rate_id ] );
            }
        }

        return $rates;
    }

    /**
     * Get an instance id from a rate id.
     *
     * @param string $rate_id The rate id.
     *
     * @return int
     */
    private function get_rate_instance_id( $rate_id ) : int {
        $rate_id_fragments = explode( ':', $rate_id );

        if ( ! isset( $rate_id_fragments[1] ) ) {
            return -1;
        }

        return absint( $rate_id_fragments[1] );
    }
}
