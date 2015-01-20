<?php
/**
 * Plugin Name: WooCommerce WooRei Dynamic Section Fields
 * Plugin URI: http://stackoverflow.com/questions/27908993/how-to-add-custom-section-in-woocommerce-products-settings-page
 * Description: Product Section Settings with dynamic fields.
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

/**
 * Create the section beneath the products tab
 **/
add_filter( 'woocommerce_get_sections_products', 'woorei_mysettings_add_section' );
function woorei_mysettings_add_section( $sections ) {
	$sections['mysettings'] = __( 'My Settings', 'woorei' );
	return $sections;
}

/**
 * Add settings to the specific section we created before
 */

add_filter( 'woocommerce_get_settings_products', 'woorei_mysettings', 10, 2 );
function woorei_mysettings( $settings, $current_section ) {

	/**
	 * Check the current section is what we want
	 **/

	if ( $current_section == 'mysettings' ) {
		$settings_slider = array();
		// Add Title to the Settings
		$settings_slider[] = array( 'name' => __( 'My Options', 'woorei' ), 'type' => 'title', 'desc' => __( 'The following options are used to configure my options.', 'woorei' ), 'id' => 'mysettings' );
		$settings_slider[] = array( 'type' => 'woorei_dynamic_field_table', 'id' => 'woorei_dynamic_field_table' );
		$settings_slider[] = array( 'type' => 'sectionend', 'id' => 'mysettings' );
		return $settings_slider;
	
	/**
	 * If not, return the standard settings
	 **/

	} else {
		return $settings;
	}

}

add_action('woocommerce_admin_field_woorei_dynamic_field_table','woorei_admin_field_woorei_dynamic_field_table');
function woorei_admin_field_woorei_dynamic_field_table($value){
	
	?>
	<table class="woorei_mysettings wc_input_table sortable widefat">
			<thead>
				<tr>
					<th width="20px"><?php _e( 'Default', 'woorei' ); ?></th>
					<th><?php _e( 'Name', 'woorei' ); ?></th>
					<th><?php _e( 'ID', 'woorei' ); ?></th>
				</tr>
			</thead>
			<tbody id="rates">
				<?php
					$woorei_mysettings = get_option('woorei_mysettings',array());
					foreach ( $woorei_mysettings as $data ) {
						?>
						<tr>
							<td align="center">
								<input type="radio" class="woorei_mysettings_default_radio" name="woorei_mysettings_default_radio" value="" <?php if($data['default'] == 'yes') {echo 'checked="checked"';} ?> />
								<input type="hidden" class="woorei_mysettings_default" name="woorei_mysettings[default][]" value="<?php echo esc_attr( $data['default'] ) ?>" />
							</td>
							<td>
								<input type="text" value="<?php echo esc_attr( $data['name'] ) ?>"  name="woorei_mysettings[name][]" />
							</td>
							<td>
								<input type="text" value="<?php echo esc_attr( $data['id'] ) ?>"  name="woorei_mysettings[id][]" />
							</td>
						</tr>
						<?php
					}
				?>
			</tbody>
			<tfoot>
				<tr>
					<th colspan="10">
						<a href="#" class="button plus insert"><?php _e( 'Insert row', 'woorei' ); ?></a>
						<a href="#" class="button minus remove_item"><?php _e( 'Remove selected row(s)', 'woorei' ); ?></a>
					</th>
				</tr>
			</tfoot>
		</table>
		<script type="text/javascript">
			jQuery( function() {
				jQuery('.woorei_mysettings .remove_item').click(function() {
					var $tbody = jQuery('.woorei_mysettings').find('tbody');
					if ( $tbody.find('tr.current').size() > 0 ) {
						$current = $tbody.find('tr.current');
						$current.remove();
						
					} else {
						alert('<?php echo esc_js( __( 'No row(s) selected', 'woorei' ) ); ?>');
					}
					return false;
				});


				jQuery('.woorei_mysettings .insert').click(function() {
					var $tbody = jQuery('.woorei_mysettings').find('tbody');
					var size = $tbody.find('tr').size();
					var code = '<tr class="new">\
							<td width="20px" align="center">\
								<input type="radio" class="woorei_mysettings_default_radio" name="woorei_mysettings_default_radio" />\
								<input type="hidden" class="woorei_mysettings_default" name="woorei_mysettings[default][]" value="" />\
							</td>\
							<td><input type="text"  name="woorei_mysettings[name][]" /></td>\
							<td><input type="text"  name="woorei_mysettings[id][]" /></td>\
						</tr>';
					if ( $tbody.find('tr.current').size() > 0 ) {
						$tbody.find('tr.current').after( code );
					} else {
						$tbody.append( code );
					}

					return false;
				});
				jQuery('.woorei_mysettings').on('click','.woorei_mysettings_default_radio',function() {
					jQuery('.woorei_mysettings_default').val('');
					jQuery(this).siblings('.woorei_mysettings_default').val('yes');
				});

			});
		</script>
	<?php
	
}

add_action('woocommerce_update_option_woorei_dynamic_field_table','woorei_update_option_woorei_dynamic_field_table');
function woorei_update_option_woorei_dynamic_field_table($value){
	$woorei_mysettings_new = $_POST['woorei_mysettings'];
	$woorei_mysettings = array();
	foreach($woorei_mysettings_new as $fields => $mysettings ){
		foreach( $mysettings as $key => $settings ){
			$woorei_mysettings[$key][$fields] = $settings;
		}
	}
	update_option('woorei_mysettings',$woorei_mysettings);
}
