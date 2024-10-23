<?php
/**
 * Plugin Name: WooCommerce American Express Discount for Amazon Payment Services
 * Plugin URI:  https://github.com/ProgrammerNomad/woocommerce-amex-discount/
 * Description: Apply a discount for American Express cards using Amazon Payment Services.
 * Version:     1.0
 * Author:      Shiv Singh
 * Author URI:  https://github.com/ProgrammerNomad/ 
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woocommerce-amex-discount
 * Domain Path: /languages
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) exit; 

// Check if WooCommerce is active
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    // Enqueue JavaScript for frontend 
    add_action( 'wp_enqueue_scripts', 'enqueue_amex_discount_scripts' );
    function enqueue_amex_discount_scripts() {
        if ( is_checkout() ) { 
            wp_enqueue_script( 'amex-discount-js', plugins_url( 'amex-discount.js', __FILE__ ), array( 'jquery' ), '1.0', true );
            wp_localize_script( 'amex-discount-js', 'amex_discount_params', array(
                'ajax_url' => admin_url( 'admin-ajax.php' )
            ) );
        }
    }

    // AJAX handler to check card type and apply/remove discount
    add_action( 'wp_ajax_check_amex_card', 'check_amex_card_callback' );
    add_action( 'wp_ajax_nopriv_check_amex_card', 'check_amex_card_callback' );
    function check_amex_card_callback(WC_Cart $cart) {
        $card_number = sanitize_text_field( $_POST['card_number'] );

        // Check if the card number starts with "34" or "37" and is 15 digits long
        if ( (substr($card_number, 0, 2) === "34" || substr($card_number, 0, 2) === "37") && strlen($card_number) === 15 ) { 

            
            // (Optional) Use Amazon Payment Services API to tokenize/pre-authorize for more robust validation 
            // ... (API code here) ...

            // Apply the discount using the woocommerce_cart_calculate_fees hook
            add_action('woocommerce_cart_calculate_fees' , 'add_user_discounts'); 

            echo 'runing here';

            echo '<pre>';
            print_r(WC()->cart->get_fees());
           

        } else {
            // If not Amex, remove any existing Amex discount fee
            $fees = WC()->cart->get_fees();
            foreach ( $fees as $key => $fee ) {
                if ( $fee->name == __( 'American Express Discount', 'woocommerce-amex-discount' ) ) {
                    WC()->cart->remove_fee( $key );
                }
            }

            // IMPORTANT: Recalculate totals after removing the fee
            WC()->cart->calculate_totals();  
        }
        wp_die(); 
    }

    // Function to add the discount (defined OUTSIDE check_amex_card_callback)
    function add_user_discounts( WC_Cart $cart ){


      
        // Calculate the amount to reduce (10% of the cart total)
        $discount = $cart->total * 0.1;

        // Check if the fee already exists to avoid duplicates
        $fee_exists = false;
        foreach ( $cart->get_fees() as $fee ) {
            if ( $fee->name == __( 'American Express Discount', 'woocommerce-amex-discount' ) ) {
                $fee_exists = true;
                break;
            }
        }

        if ( ! $fee_exists ) {
            WC()->cart->add_fee(__('American Express Discount', 'woocommerce-amex-discount'), -$discount);
        }

        // IMPORTANT: Recalculate totals after adding the fee
        WC()->cart->calculate_totals(); 
    }

} // End WooCommerce check