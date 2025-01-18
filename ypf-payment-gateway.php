<?php
/**
 * Plugin Name: YPF Payment Gateway
 * Description: A custom WooCommerce payment gateway that marks orders as completed upon payment.
 * Version: 1.0
 * Author: [Your Name]
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Add the gateway to WooCommerce
add_filter( 'woocommerce_payment_gateways', 'ypf_add_payment_gateway' );

function ypf_add_payment_gateway( $gateways ) {
    $gateways[] = 'WC_Gateway_YPF';
    return $gateways;
}

// Initialize the YPF Payment Gateway class
add_action( 'plugins_loaded', 'ypf_init_payment_gateway' );

function ypf_init_payment_gateway() {
    class WC_Gateway_YPF extends WC_Payment_Gateway {

        public function __construct() {
            $this->id                 = 'ypf_payment';
            $this->method_title       = __( 'YPF Payment Gateway', 'ypf-payment' );
            $this->method_description = __( 'A custom payment gateway that completes the order immediately.', 'ypf-payment' );
            $this->has_fields         = false;

            // Load settings fields
            $this->init_form_fields();
            $this->init_settings();

            // Get settings
            $this->title       = $this->get_option( 'title' );
            $this->description = $this->get_option( 'description' );

            // Save admin options
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
        }

        // Define settings fields
        public function init_form_fields() {
            $this->form_fields = [
                'enabled' => [
                    'title'   => __( 'Enable/Disable', 'ypf-payment' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable YPF Payment Gateway', 'ypf-payment' ),
                    'default' => 'yes',
                ],
                'title' => [
                    'title'       => __( 'Title', 'ypf-payment' ),
                    'type'        => 'text',
                    'description' => __( 'This controls the title seen during checkout.', 'ypf-payment' ),
                    'default'     => __( 'YPF Payment', 'ypf-payment' ),
                    'desc_tip'    => true,
                ],
                'description' => [
                    'title'       => __( 'Description', 'ypf-payment' ),
                    'type'        => 'textarea',
                    'description' => __( 'This controls the description seen during checkout.', 'ypf-payment' ),
                    'default'     => __( 'Pay using YPF Payment Gateway. The order will be completed immediately.', 'ypf-payment' ),
                    'desc_tip'    => true,
                ],
            ];
        }

        // Process the payment
        public function process_payment( $order_id ) {
            $order = wc_get_order( $order_id );

            // Mark order as completed
            $order->update_status( 'completed', __( 'Order marked as completed by YPF Payment Gateway.', 'ypf-payment' ) );

            // Reduce stock levels
            wc_reduce_stock_levels( $order_id );

            // Return success response
            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order ),
            ];
        }
    }
}
