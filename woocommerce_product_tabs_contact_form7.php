<?php
/**
 * Plugin Name: WooCommerce plus Contact Form 7
 * Plugin URI:  http://reigelgallarde.me/plugins/how-to-add-inquiry-tab-to-your-product-in-woocommerce-using-contact-form-7
 * Description: Additional Tab Product Inquiry.
 * Author: Reigel Gallarde
 * Author URI: http://reigelgallarde.me
 * Version: 0.1
 * Tested up to: 4.1
 *
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package     
 * @author      Reigel Gallarde
 * @category    Plugin
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */
 
 
 
/**
 * Exit if accessed directly
 **/
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Check if WooCommerce is active
 **/
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}


add_filter('woocommerce_product_tabs','woocommerce_product_tabs_contact_form7',10,1);
function woocommerce_product_tabs_contact_form7($tabs){
	
	$tabs['contact_form7'] = array(
		'title'    => __( 'Enquiry', 'woocommerce' ),
		'priority' => 20,
		'callback' => 'woocommerce_product_contact_form7_tab'
	);
	
	return $tabs;
}

function woocommerce_product_contact_form7_tab(){
	echo do_shortcode('[contact-form-7 id="1430" title="Contact form 1"]');
}
