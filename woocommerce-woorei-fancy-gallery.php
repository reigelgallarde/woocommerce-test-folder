<?php
/**
 * Plugin Name: WooCommerce WooRei Product Fancy Gallery
 * Description: Make thumbnails in woocommerce product replace the main image instead of opening fancybox.
 * Author: Reigel Gallarde
 * Author URI: http://reigelgallarde.me
 * Version: 0.1
 * Tested up to: 4.1
 *
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package     WooCommerce-WooRei-Event-Manager
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

if (! class_exists('WooCommerceWooReiProductFancyGallery')) :
class WooCommerceWooReiProductFancyGallery {
	
	private $is_lightbox_active = 'yes';
	
	public function __construct(){
		register_activation_hook( __FILE__, array( $this, 'woorei_disable_woocommerce_lightbox' ) );
		add_action( 'woocommerce_init', array( $this, 'init' ) );
		add_action( 'wp_footer', array( $this, 'woorei_fancy_product_gallery_script' ) );
	}
	
	public function woorei_disable_woocommerce_lightbox(){
		update_option('woocommerce_enable_lightbox','no');
	}
	
	public function init(){
		$this->is_lightbox_active = get_option('woocommerce_enable_lightbox','no');
	}
	
	public function woorei_fancy_product_gallery_script() {
		if ( wp_script_is( 'jquery', 'done' ) ) {
			?>
			<script type="text/javascript">
				jQuery(function($){
					$('.product .thumbnails a.zoom').on('click hover',function(){
						$('.product .woocommerce-main-image img')[0].src = $(this).find('img')[0].src;
						return false;
					}).eq(0).click();
				});
			</script>
			<?php
		}
	}
}

new WooCommerceWooReiProductFancyGallery();
endif;
