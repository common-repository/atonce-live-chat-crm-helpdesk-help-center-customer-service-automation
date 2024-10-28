<?php
   /*
   Plugin Name: AtOnce - Live Chat, CRM & Helpdesk - Help Center & Customer Service Automation
   Plugin URI: https://atonce.com
   description: Add AtOnce Live Chat & CRM to WordPress free. Chat with customers, help generate leads & get live chat sales.
   Version: 1.0
   Author: AtOnce
   License: GPL3
   */
   

   
	function add_atonce_live_chat() {
		$options = get_option( 'atonce_options' );
		if (isset($options) && isset($options["atonce_field_brand"])) {
			$options_brand = $options["atonce_field_brand"];
			echo "<script type='module'>import { createAtOnceLiveChat } from 'https://cdn.jsdelivr.net/gh/AtOnceCo/AtOnce@main/selfservice.js';createAtOnceLiveChat('{$options_brand}');</script>";
		}
	}
	
	/**
	 * custom option and settings
	 */
	function atonce_settings_init() {
		// Register a new setting for "atonce" page.
		register_setting( 'atonce', 'atonce_options' );

		// Register a new section in the "atonce" page.
		add_settings_section(
			'atonce_section_developers',
			__( 'Live Chat Setup', 'atonce' ), 'atonce_section_developers_callback',
			'atonce'
		);

		// Register a new field in the "atonce_section_developers" section, inside the "atonce" page.
		add_settings_field(
			'atonce_field_brand', // As of WP 4.6 this value is used only internally.
									// Use $args' label_for to populate the id inside the callback.
				__( 'AtOnce Email Prefix', 'atonce' ),
			'atonce_field_brand_cb',
			'atonce',
			'atonce_section_developers',
			array(
				'label_for'         => 'atonce_field_brand',
				'class'             => 'atonce_row',
				'atonce_custom_data' => 'custom',
			)
		);
	}

	/**
	 * Register our atonce_settings_init to the admin_init action hook.
	 */
	add_action( 'admin_init', 'atonce_settings_init' );


	/**
	 * Custom option and settings:
	 *  - callback functions
	 */


	/**
	 * Developers section callback function.
	 *
	 * @param array $args  The settings array, defining title, id, callback.
	 */
	function atonce_section_developers_callback( $args ) {
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( '', 'atonce' ); ?></p>
		<?php
	}

	/**
	 * AtOnce Brand field callback function.
	 *
	 * WordPress has magic interaction with the following keys: label_for, class.
	 * - the "label_for" key value is used for the "for" attribute of the <label>.
	 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
	 * Note: you can add custom key value pairs to be used inside your callbacks.
	 *
	 * @param array $args
	 */
	 
	function atonce_field_brand_cb( $args ) {
		// Get the value of the setting we've registered with register_setting()
		$options = get_option( 'atonce_options' );
		?>
		<input
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			data-custom="<?php echo esc_attr( $args['atonce_custom_data'] ); ?>"
			name="atonce_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
			value="<?php echo isset( $options[ $args['label_for'] ] ) ? ( $options[ $args['label_for'] ] ) : ( '' ); ?>"
		>
		</input>
		<p class="description">If your email is yourbrand@emails.atonce.com, this is <b>yourbrand</b></p>
		<p class="description">Find this in <a href="https://atonce.com/settings/integrations" target="_blank" rel="noopener noreferrer">Settings > Integrations</a></p>
		<?php
	}

	/**
	 * Add the top level menu page.
	 */
	function atonce_options_page() {
		add_menu_page(
			'AtOnce Settings',
			'AtOnce',
			'manage_options',
			'atonce',
			'atonce_options_page_html'
		);
	}


	/**
	 * Register our atonce_options_page to the admin_menu action hook.
	 */
	add_action( 'admin_menu', 'atonce_options_page' );


	/**
	 * Top level menu callback function
	 */
	function atonce_options_page_html() {
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// add error/update messages

		// check if the user have submitted the settings
		// WordPress will add the "settings-updated" $_GET parameter to the url
		if ( isset( $_GET['settings-updated'] ) ) {
			// add settings saved message with the class of "updated"
			add_settings_error( 'atonce_messages', 'atonce_message', __( 'Settings Saved', 'atonce' ), 'updated' );
		}

		// show error/update messages
		settings_errors( 'atonce_messages' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<h3>Don't have an AtOnce account? <a href="https://atonce.com/auth/signup?utm_medium=wordpress&utm_campaign=settings" target="_blank" rel="noopener noreferrer">Sign Up For Free</a></h3>
			<br/>
			<form action="options.php" method="post">
				<?php
				// output security fields for the registered setting "atonce"
				settings_fields( 'atonce' );
				// output setting sections and their fields
				// (sections are registered for "atonce", each field is registered to a specific section)
				do_settings_sections( 'atonce' );
				// output save settings button
				submit_button( 'Save Settings' );
				?>
			</form>
		</div>
		<?php
	}

	function atonce_plugin_action_links($links, $file) {
		static $this_plugin;

		if (!$this_plugin) {
			$this_plugin = plugin_basename(__FILE__);
		}

		if ($file == $this_plugin) {
			$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=atonce">Settings</a>';
			array_unshift($links, $settings_link);
		}

		return $links;
	}
   
	add_filter('plugin_action_links', 'atonce_plugin_action_links', 10, 2);
   
   if (function_exists("add_atonce_live_chat")) {
	add_action("wp_footer", "add_atonce_live_chat");
   }
   
   
   
   
   
   
   // Woocommerce Coupon Code URL
   // If url contains ?credit=code then apply it to the cart
   
   function atonce_woocommerce_coupon_links(){

		// Bail if WooCommerce or sessions aren't available.

		if (!function_exists('WC') || !WC()->session) {
			return;
		}

		/**
		 * Filter the coupon code query variable name.
		 *
		 * @since 1.0.0
		 *
		 * @param string $query_var Query variable name.
		 */
		$query_var = apply_filters('woocommerce_coupon_links_query_var', 'credit');

		// Bail if a coupon code isn't in the query string.

		if (empty($_GET[$query_var])) {
			return;
		}

		// Set a session cookie to persist the coupon in case the cart is empty.

		WC()->session->set_customer_session_cookie(true);

		// Apply the coupon to the cart if necessary.

		if (!WC()->cart->has_discount($_GET[$query_var])) {

			// WC_Cart::add_discount() sanitizes the coupon code.

			WC()->cart->add_discount($_GET[$query_var]);
		}
	}
	add_action('wp_loaded', 'atonce_woocommerce_coupon_links', 30);
	add_action('woocommerce_add_to_cart', 'atonce_woocommerce_coupon_links');
   

	// Woocommerce Post-purchase Hook to Add Order Details & Redirect to Self-Service if Necessary
	
	add_action('woocommerce_thankyou', 'atonce_process_order_info', 10, 1);

	function atonce_order_storage($order_num, $order_zip) {
		echo "<script>
			try {
				let order_num_f = `{$order_num}`.replace(/\s/g, '').replace(/\D/g, '').trim().toUpperCase();
				let current_orders = [`\${order_num_f}_atonce_{$order_zip}`];
				let current_storage = localStorage.getItem('AtOnceOrders');
				if (current_storage) {
					try {
						let split_list = current_storage.split(',');
						if (split_list && split_list.length) {
							for (let val of split_list) {
								if (val && val.length > 7 && current_orders.indexOf(val) == -1) {
									current_orders.push(val);
								}
							}
						}
					} catch (error) {}
				}
				localStorage.setItem('AtOnceOrders',current_orders);
			} catch (error) {}
		</script>";
	}

	function atonce_process_order_info( $order_id ) {
		
		if ( ! $order_id )
        return;
		
			// Getting an instance of the order object
			$order = wc_get_order( $order_id );
			$order_meta = $order->get_meta('_atonce_return');
		
			$pre_zipcode = '';
			try {
				$pre_ship_zipcode = preg_replace('/\s+/', '', strtoupper(trim($order->get_shipping_postcode())));
				$pre_bill_zipcode = preg_replace('/\s+/', '', strtoupper(trim($order->get_billing_postcode())));

				if ($pre_ship_zipcode) {
					$pre_zipcode = $pre_ship_zipcode;
				} else {
					$pre_zipcode = $pre_bill_zipcode;
				}
			} catch (Exception $e) {}
		
			try {
				atonce_order_storage($order_id, $pre_zipcode);
			} catch (Exception $e) {}
		
			if( ! get_post_meta( $order_id, '_thankyou_action_done', true ) ) {
				
				// Flag the action as done (to avoid repetitions on reload for example)
				$order->update_meta_data( '_thankyou_action_done', true );
				$order->save();
				
				if ($order_meta) {
					if ( ! $order->has_status( 'failed' ) ) {
						$options = get_option( 'atonce_options' );
						if (isset($options) && isset($options["atonce_field_brand"])) {
							$options_brand = $options["atonce_field_brand"];
							$ao_redirect_url = "https://{$options_brand}.useatonce.com?return={$order_meta}";
							echo "<script>
									try {
										window.location.href = '{$ao_redirect_url}';
									} catch (error) {}
								</script>";
						}	
					}
				}
			}
	}
   
?>