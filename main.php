<?php
/**
 * Plugin Name:       Filter Orders by Payment Method for WooCommerce
 * Plugin URI:        https://github.com/keetup/filterordersbypaymentmethodforwoocommerce
 * Description:       This plugin allows you to filter the WooCommerce order list by Payment Method.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Damian Olivier
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fobpmfwc
 */


function wc_fobpm_add_payment_method_column( $columns ) {
    $new_columns = array();
    foreach ( $columns as $column_name => $column_info ) {
        $new_columns[ $column_name ] = $column_info;
        if ( 'order_total' === $column_name ) {
            $new_columns['order_payment'] = __( 'Payment Method', 'fobpmfwc' );
        }
    }
    return $new_columns;
}
add_filter( 'manage_edit-shop_order_columns', 'wc_fobpm_add_payment_method_column', 20 );


function wc_fobpm_add_payment_method_column_content( $column ) {
    global $post;
    if ( 'order_payment' === $column ) {
    $order = wc_get_order( $post->ID );
        echo wc_fobpm_get_paymentmethod_title($order->payment_method);
    }
}

function wc_fobpm_get_paymentmethod_title($slug) {
    if($slug) {
        $gateways = WC()->payment_gateways->payment_gateways();
        foreach ( $gateways as $id => $gateway ) { 
            if($gateway->id == $slug) {
                return $gateway->get_method_title();
            }
        }
    }
    return;
}

add_action( 'manage_shop_order_posts_custom_column', 'wc_fobpm_add_payment_method_column_content' );


// Add payment method bulk filter for orders
function wc_fobpm_add_filter_by_payment_method_orders() {
    global $typenow;
    if ( 'shop_order' === $typenow ) {
        // get all payment methods
        $gateways = WC()->payment_gateways->payment_gateways();
        ?>
        <select name="_shop_order_payment_method" id="dropdown_shop_order_payment_method">
            <option value=""><?php esc_html_e( 'All Payment Methods', 'fobpmfwc' ); ?></option>
            <?php foreach ( $gateways as $id => $gateway ) : ?>
            <?php if($gateway->enabled == "yes") { ?>
            <option value="<?php echo esc_attr( $id ); ?>" <?php echo esc_attr( isset( $_GET['_shop_order_payment_method'] ) ? selected( $id, $_GET['_shop_order_payment_method'], false ) : '' ); ?>>
                <?php echo esc_html( $gateway->get_method_title() ); ?>
            </option>
            <?php } ?>
            <?php endforeach; ?>
        </select>
        <?php
    }
}
add_action( 'restrict_manage_posts', 'wc_fobpm_add_filter_by_payment_method_orders', 99 );

 // Process bulk filter order for payment method

function wc_fobpm_add_filter_by_payment_method_orders_query( $vars ) {
    global $typenow;
    if ( 'shop_order' === $typenow && isset( $_GET['_shop_order_payment_method'] ) ) {
        $vars['meta_key']   = '_payment_method';
        $vars['meta_value'] = wc_clean( $_GET['_shop_order_payment_method'] );
    }
    return $vars;
}
add_filter( 'request', 'wc_fobpm_add_filter_by_payment_method_orders_query', 99 ); 
