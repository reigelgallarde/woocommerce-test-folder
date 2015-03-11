<?php
/**
 * Plugin Name: Dynamic Pricing Display
 * Plugin URI:  http://reigelgallarde.me/programming/show-product-price-times-selected-quantity-on-woocommecre-product-page/
 * Description: Show Product Price times Selected Quantity on WooCommerce Product Page. This is version 0.2 which integrates with
 *              [WooCommerce Dynamic Pricing & Discounts](http://codecanyon.net/item/woocommerce-dynamic-pricing-discounts/7119279).
 *              version 0.1 can be found on Plugin URI above.
 * Author: Reigel Gallarde
 * Author URI: http://reigelgallarde.me
 * Version: 0.2
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
 

// we are going to hook this on priority 31, so that it would display below add to cart button.
add_action( 'woocommerce_single_product_summary', 'woocommerce_total_product_price', 31 );
function woocommerce_total_product_price() {
    global $woocommerce, $product;
    // let's setup our divs
    echo sprintf('<div id="product_total_price" style="margin-bottom:20px;display:none">%s %s</div>',__('Product Total:','woocommerce'),'<span class="price">'.$product->get_price().'</span>');
    echo sprintf('<div id="cart_total_price" style="margin-bottom:20px;display:none">%s %s</div>',__('Cart Total:','woocommerce'),'<span class="price">'.$product->get_price().'</span>');
    ?>
        <script>
            jQuery(function($){
                var price = <?php echo $product->get_price(); ?>,
                    current_cart_total = <?php echo $woocommerce->cart->cart_contents_total; ?>,
                    currency = '<?php echo get_woocommerce_currency_symbol(); ?>',
					prices_range_discounted = [];
					<?php
						$prices_range_discounted = product_page_pricing_table_data();
						foreach ($prices_range_discounted as $row):
							if ($row['max'] == 2147483647) {
								echo 'var limit_quantity = ' . $row['min'];
							} else {
								echo 'for ( i='.$row['min'].'; i<='.$row['max'].'; i++) {prices_range_discounted[i] = '.preg_replace('/&#([^\s]*);/','',$row['display_price']).';}';
							}
						endforeach;
					?>
 
                $('[name=quantity]').change(function(){
                    if (!(this.value < 1)) {
                        var new_price = (!isNaN(prices_range_discounted[this.value]))?prices_range_discounted[this.value]:price,
						
						product_total = parseFloat(new_price * this.value),
                        cart_total = parseFloat(product_total + current_cart_total);
 
                        $('#product_total_price .price').html( currency + product_total.toFixed(2));
                        $('#cart_total_price .price').html( currency + cart_total.toFixed(2));
                    }
                    $('#product_total_price,#cart_total_price').toggle(!(this.value <= 1));
 
                });
            });
        </script>
    <?php
}

function product_page_pricing_table_data()
{
	
	$RP_WCDPD = RP_WCDPD::get_instance();
	$this_option = $RP_WCDPD->get_options();
	

	if ($this_option['settings']['display_table'] == 'hide' && (!isset($this_option['settings']['display_offers']) || $this_option['settings']['display_offers'] == 'hide')) {
		return;
	}

	global $product;

	if (!$product) {
		return;
	}
	// Load required classes
	require_once RP_WCDPD_PLUGIN_PATH . 'includes/classes/Pricing.php';

	$selected_rule = null;

	// Iterate over pricing rules and use the first one that has this product in conditions (or does not have if condition "not in list")
	if (isset($this_option['pricing']['sets']) && count($this_option['pricing']['sets'])) {
		foreach ($this_option['pricing']['sets'] as $rule_key => $rule) {

			if ($rule['method'] == 'quantity' && $validated_rule = RP_WCDPD_Pricing::validate_rule($rule)) {
				if ($validated_rule['selection_method'] == 'all' && $RP_WCDPD ->user_matches_rule($validated_rule)) {
					$selected_rule = $validated_rule;
					break;
				}
				if ($validated_rule['selection_method'] == 'categories_include' && count(array_intersect($RP_WCDPD ->get_product_categories($product->id), $validated_rule['categories'])) > 0 && $RP_WCDPD ->user_matches_rule($validated_rule)) {
					$selected_rule = $validated_rule;
					break;
				}
				if ($validated_rule['selection_method'] == 'categories_exclude' && count(array_intersect($RP_WCDPD ->get_product_categories($product->id), $validated_rule['categories'])) == 0 && $RP_WCDPD ->user_matches_rule($validated_rule)) {
					$selected_rule = $validated_rule;
					break;
				}
				if ($validated_rule['selection_method'] == 'products_include' && in_array($product->id, $validated_rule['products']) && $RP_WCDPD ->user_matches_rule($validated_rule)) {
					$selected_rule = $validated_rule;
					break;
				}
				if ($validated_rule['selection_method'] == 'products_exclude' && !in_array($product->id, $validated_rule['products']) && $RP_WCDPD ->user_matches_rule($validated_rule)) {
					$selected_rule = $validated_rule;
					break;
				}
			}
		}
	}

	if (is_array($selected_rule)) {

		// Quantity
		if ($selected_rule['method'] == 'quantity' && in_array($this_option['settings']['display_table'], array('modal', 'inline')) && isset($selected_rule['pricing'])) {

			if ($product->product_type == 'variable') {
				$product_variations = $product->get_available_variations();
			}

			// For variable products only - check if prices differ for different variations
			$multiprice_variable_product = false;

			if ($product->product_type == 'variable' && !empty($product_variations)) {
				$last_product_variation = array_slice($product_variations, -1);
				$last_product_variation_object = new WC_Product_Variable($last_product_variation[0]['variation_id']);
				$last_product_variation_price = $last_product_variation_object->get_price();

				foreach ($product_variations as $variation) {
					$variation_object = new WC_Product_Variable($variation['variation_id']);

					if ($variation_object->get_price() != $last_product_variation_price) {
						$multiprice_variable_product = true;
					}
				}
			}

			if ($multiprice_variable_product) {
				$variation_table_data = array();

				foreach ($product_variations as $variation) {
					$variation_product = new WC_Product_Variation($variation['variation_id']);
					$variation_table_data[$variation['variation_id']] = $RP_WCDPD ->pricing_table_calculate_adjusted_prices($selected_rule['pricing'], $variation_product->get_price());
				}
			}
			else {
				if ($product->product_type == 'variable' && !empty($product_variations)) {
					$variation_product = new WC_Product_Variation($last_product_variation[0]['variation_id']);
					$table_data = $RP_WCDPD ->pricing_table_calculate_adjusted_prices($selected_rule['pricing'], $variation_product->get_price());
				}
				else {
					$table_data = $RP_WCDPD ->pricing_table_calculate_adjusted_prices($selected_rule['pricing'], $product->get_price());
				}
			}
		}
		return $table_data;
	}
	return false;
}
