<?php
/**
* @package Equitel For WooCommerce
* @version 1.6
* @author Mauko Maunde
**/
/*
Plugin Name: Equitel For WooCommerce
Plugin URI: http://wordpress.org/plugins/wc-equitel/
Description: This plugin extends WooCommerce functionality to integrate Equitel for making payments, checking account balance transaction status and reversals. It also adds Kenyan Counties to the WooCommerce states list.
Author: Mauko Maunde
Version: 0.1
Author URI: https://mauko.co.ke/
*/

require_once( 'Equitel.php' );
$equitel_options = get_option( 'woocommerce_equitel_settings');
if ($equitel_options['live'] == 'yes') {
	$live = true;
} else {
	$live = false;
}

$equitel = new \Equity\Equitel( $equitel_options['key'], $equitel_options['secret'], $equitel_options['id'], $live );

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

add_action( 'plugins_loaded', 'wc_equitel_gateway_init', 11 );

function wc_equitel_add_to_gateways( $gateways )
{
	$gateways[] = 'WC_Gateway_Equitel';
	return $gateways;
}

add_filter( 'woocommerce_payment_gateways', 'wc_equitel_add_to_gateways' );

add_filter( 'woocommerce_states', 'equitel_woocommerce_states' );

function equitel_woocommerce_states( $states )
{

  $states['KE'] = array(
	'BAR' => 'Baringo',
	'BMT' => 'Bomet',
	'BGM' => 'Bungoma',
	'BSA' => 'Busia',
	'EGM' => 'Elgeyo/Marakwet',
	'EBU' => 'Embu',
	'GSA' => 'Garissa',
	'HMA' => 'Homa Bay',
	'ISL' => 'Isiolo',
	'KAJ' => 'Kajiado',
	'KAK' => 'Kakamega',
	'KCO' => 'Kericho',
	'KBU' => 'Kiambu',
	'KLF' => 'Kilifi',
	'KIR' => 'Kirinyaga',
	'KSI' => 'Kisii',
	'KIS' => 'Kisumu',
	'KTU' => 'Kitui',
	'KLE' => 'Kwale',
	'LKP' => 'Laikipia',
	'LAU' => 'Lamu',
	'MCS' => 'Machakos',
	'MUE' => 'Makueni',
	'MDA' => 'Mandera',
	'MAR' => 'Marsabit',
	'MRU' => 'Meru',
	'MIG' => 'Migori',
	'MBA' => 'Mombasa',
	'MRA' => 'Muranga',
	'NBO' => 'Nairobi',
	'NKU' => 'Nakuru',
	'NDI' => 'Nandi',
	'NRK' => 'Narok',
	'NYI' => 'Nyamira',
	'NDR' => 'Nyandarua',
	'NER' => 'Nyeri',
	'SMB' => 'Samburu',
	'SYA' => 'Siaya',
	'TVT' => 'Taita Taveta',
	'TAN' => 'Tana River',
	'TNT' => 'Tharaka-Nithi',
	'TRN' => 'Trans-Nzoia',
	'TUR' => 'Turkana',
	'USG' => 'Uasin Gishu',
	'VHG' => 'Vihiga',
	'WJR' => 'Wajir',
	'PKT' => 'West Pokot'
  );

  return $states;
}

function wc_equitel_gateway_init() {

	/**
	* @class WC_Gateway_Offline
	* @extends WC_Payment_Gateway
	**/
	class WC_Gateway_Equitel extends WC_Payment_Gateway {

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
			// Setup general properties
			$this->setup_properties();

			// Load the settings
			$this->init_form_fields();
			$this->init_settings();

			// Get settings
			$this->title              = $this->get_option( 'title' );
			$this->description        = $this->get_option( 'description' );
			$this->instructions       = $this->get_option( 'instructions' );
			$this->enable_for_methods = $this->get_option( 'enable_for_methods', array() );
			$this->enable_for_virtual = $this->get_option( 'enable_for_virtual', 'yes' ) === 'yes' ? true : false;

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
			add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'change_payment_complete_order_status' ), 10, 3 );

			// Customer Emails
			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
		}

		/**
		 * Setup general properties for the gateway.
		 */
		protected function setup_properties() {
			$this->id                 = 'equitel';
			$this->icon               = apply_filters( 'woocommerce_cod_icon', '' );
			$this->method_title       = __( 'Equitel Money', 'woocommerce' );
			$this->method_description = __( 'Have your customers pay conveniently using Equity Bank\'s Equitel Money.', 'woocommerce' );
			$this->has_fields         = false;
		}

		/**
		 * Initialise Gateway Settings Form Fields.
		 */
		public function init_form_fields() {
			$shipping_methods = array();

			foreach ( WC()->shipping()->load_shipping_methods() as $method ) {
				$shipping_methods[ $method->id ] = $method->get_method_title();
			}

			$this -> form_fields = array(
				'enabled' => array(
					'title'       => __( 'Enable/Disable', 'woocommerce' ),
					'label'       => __( 'Enable Equitel', 'woocommerce' ),
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no',
				),
				'live' => array(
					'title'       => __( 'Enable/Disable', 'woocommerce' ),
					'label'       => __( 'Is Live Environment', 'woocommerce' ),
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'yes',
				),
				'key' => array(
					'title'       => __( 'App Key', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Your App Key From Safaricom Daraja.', 'woocommerce' ),
					'default'     => __( 'Your app key', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'secret' => array(
					'title'       => __( 'App Secret', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Your App Secret From Safaricom Daraja.', 'woocommerce' ),
					'default'     => __( 'Your app secret', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'business' => array(
					'title'       => __( 'Business Name', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Your Equitel Business Name.', 'woocommerce' ),
					'default'     => __( get_bloginfo( 'name' ), 'woocommerce' ),
					'desc_tip'    => true,
				),
				'shortcode' => array(
					'title'       => __( 'Equitel Shortcode', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Your Equitel Business Shortcode.', 'woocommerce' ),
					'default'     => __( 'Your Equitel Shortcode', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'account' => array(
					'title'       => __( 'Equity Account Number', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Your Equity Account Number.', 'woocommerce' ),
					'default'     => __( 'Your Equity Account Number', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'bank' => array(
					'title'       => __( 'Bank Name', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Your Bank Name.', 'woocommerce' ),
					'default'     => __( 'Equity Bank', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'bankbranch' => array(
					'title'       => __( 'Bank Branch', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Your Bank Branch.', 'woocommerce' ),
					'default'     => __( 'Your Bank Branch', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'bankcode' => array(
					'title'       => __( 'Bank Code', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Your Bank Code.', 'woocommerce' ),
					'default'     => __( 'Your Bank Code', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'title' => array(
					'title'       => __( 'Title', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
					'default'     => __( 'Equitel Money', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => __( 'Description', 'woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'Payment method description that the customer will see on your website.', 'woocommerce' ),
					'default'     => __( 'Press the button below. You will get a pop-up on your phone asking you to confirm the payment.
Enter your service PIN to proceed.
You will receive a confirmation message shortly thereafter.', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'instructions' => array(
					'title'       => __( 'Instructions', 'woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'Instructions that will be added to the thank you page.', 'woocommerce' ),
					'default'     => __( 'Equitel Money.', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'enable_for_methods' => array(
					'title'             => __( 'Enable for shipping methods', 'woocommerce' ),
					'type'              => 'multiselect',
					'class'             => 'wc-enhanced-select',
					'css'               => 'width: 400px;',
					'default'           => '',
					'description'       => __( 'If Equitel Money is only available for certain methods, set it up here. Leave blank to enable for all methods.', 'woocommerce' ),
					'options'           => $shipping_methods,
					'desc_tip'          => true,
					'custom_attributes' => array(
						'data-placeholder' => __( 'Select shipping methods', 'woocommerce' ),
					),
				),
				'enable_for_virtual' => array(
					'title'             => __( 'Accept for virtual orders', 'woocommerce' ),
					'label'             => __( 'Accept Equitel Money if the order is virtual', 'woocommerce' ),
					'type'              => 'checkbox',
					'default'           => 'yes',
				),
		   );
		}

		/**
		 * Check If The Gateway Is Available For Use.
		 *
		 * @return bool
		 */
		public function is_available() {
			$order          = null;
			$needs_shipping = false;

			// Test if shipping is needed first
			if ( WC()->cart && WC()->cart->needs_shipping() ) {
				$needs_shipping = true;
			} elseif ( is_page( wc_get_page_id( 'checkout' ) ) && 0 < get_query_var( 'order-pay' ) ) {
				$order_id = absint( get_query_var( 'order-pay' ) );
				$order    = wc_get_order( $order_id );

				// Test if order needs shipping.
				if ( 0 < sizeof( $order->get_items() ) ) {
					foreach ( $order->get_items() as $item ) {
						$_product = $item->get_product();
						if ( $_product && $_product->needs_shipping() ) {
							$needs_shipping = true;
							break;
						}
					}
				}
			}

			$needs_shipping = apply_filters( 'woocommerce_cart_needs_shipping', $needs_shipping );

			// Virtual order, with virtual disabled
			if ( ! $this->enable_for_virtual && ! $needs_shipping ) {
				return false;
			}

			// Only apply if all packages are being shipped via chosen method, or order is virtual.
			if ( ! empty( $this->enable_for_methods ) && $needs_shipping ) {
				$chosen_shipping_methods = array();

				if ( is_object( $order ) ) {
					$chosen_shipping_methods = array_unique( array_map( 'wc_get_string_before_colon', $order->get_shipping_methods() ) );
				} elseif ( $chosen_shipping_methods_session = WC()->session->get( 'chosen_shipping_methods' ) ) {
					$chosen_shipping_methods = array_unique( array_map( 'wc_get_string_before_colon', $chosen_shipping_methods_session ) );
				}

				if ( 0 < count( array_diff( $chosen_shipping_methods, $this->enable_for_methods ) ) ) {
					return false;
				}
			}

			return parent::is_available();
		}


		/**
		 * Process the payment and return the result.
		 *
		 * @param int $order_id
		 * @return array
		 */
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );

			// Mark as processing or on-hold (payment won't be taken until delivery)
			$order->update_status( apply_filters( 'woocommerce_cod_process_payment_order_status', $order->has_downloadable_item() ? 'on-hold' : 'processing', $order ), __( 'Payment to be made upon delivery.', 'woocommerce' ) );

			// Reduce stock levels
			wc_reduce_stock_levels( $order_id );

			// Remove cart
			WC()->cart->empty_cart();

			// Return thankyou redirect
			return array(
				'result' 	=> 'success',
				'redirect'	=> $this->get_return_url( $order ),
			);
		}

		/**
		 * Output for the order received page.
		 */
		public function thankyou_page() {
			if ( $this->instructions ) {
				echo wpautop( wptexturize( $this->instructions ) );
			}
		}

		/**
		 * Change payment complete order status to completed for Equitel orders.
		 *
		 * @since  3.1.0
		 * @param  string $status
		 * @param  int $order_id
		 * @param  WC_Order $order
		 * @return string
		 */
		public function change_payment_complete_order_status( $status, $order_id = 0, $order = false ) {
			if ( $order && 'equitel' === $order->get_payment_method() ) {
				$status = 'completed';
			}
			return $status;
		}

		/**
		 * Add content to the WC emails.
		 *
		 * @access public
		 * @param WC_Order $order
		 * @param bool $sent_to_admin
		 * @param bool $plain_text
		 */
		public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
			if ( $this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method() ) {
				echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
			}
		}
	}
}

add_action('admin_init', 'equity_sampleoptions_init' );
add_action('admin_menu', 'equity_sampleoptions_add_page');

// Init plugin options to white list our options
function equity_sampleoptions_init(){
	register_setting( 'equity_sampleoptions_options', 'equity_sample', 'equity_sampleoptions_validate' );
}

// Add menu page
function equity_sampleoptions_add_page() {
	add_options_page('Equitel Money', 'Equitel', 'manage_options', 'equity_sampleoptions', 'equity_sampleoptions_do_page');
}

// Draw the menu page itself
function equity_sampleoptions_do_page() {
	?>
	<div class="wrap">
		<h2>Equitel Money</h2>

		<h3>Buy Airtime</h3>
		<form method="post" action="options.php">
			<?php settings_fields('equity_sampleoptions_options'); ?>
			<?php $options = get_option('equity_sample'); ?>
			<table class="form-table">
				<tr valign="top"><th scope="row">Phone Number</th>
					<td><input type="text" name="equity_sample[sometext]" value="<?php echo $options['sometext']; ?>" /></td>
				</tr>
				<tr valign="top"><th scope="row">Amount</th>
					<td><input type="text" name="equity_sample[sometext]" value="<?php echo $options['sometext']; ?>" /></td>
				</tr>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Buy Airtime') ?>" />
			</p>
		</form>

		<h3>Transfer Funds</h3>
		<form method="post" action="options.php">
			<?php settings_fields('equity_sampleoptions_options'); ?>
			<?php $options = get_option('equity_sample'); ?>
			<table class="form-table">
				<tr valign="top"><th scope="row">Phone Number</th>
					<td><input type="text" name="equity_sample[sometext]" value="<?php echo $options['sometext']; ?>" /></td>
				</tr>
				<tr valign="top"><th scope="row">Amount</th>
					<td><input type="text" name="equity_sample[sometext]" value="<?php echo $options['sometext']; ?>" /></td>
				</tr>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Transfer Funds') ?>" />
			</p>
		</form>

		<h3>Transaction Status</h3>
		<form method="post" action="options.php">
			<?php settings_fields('equity_sampleoptions_options'); ?>
			<?php $options = get_option('equity_sample'); ?>
			<table class="form-table">
				<tr valign="top"><th scope="row">Transaction ID</th>
					<td><input type="text" name="equity_sample[sometext]" value="<?php echo $options['sometext']; ?>" /></td>
				</tr>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Check') ?>" />
			</p>
		</form>
	</div>
	<?php	
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function equity_sampleoptions_validate($input) {
	// Our first value is either 0 or 1
	$input['option1'] = ( $input['option1'] == 1 ? 1 : 0 );
	
	// Say our second option must be safe text with no HTML tags
	$input['sometext'] =  wp_filter_nohtml_kses($input['sometext']);
	
	return $input;
}