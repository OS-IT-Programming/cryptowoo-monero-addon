<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Plugin Name: CryptoWoo Monero Add-on
 * Plugin URI: https://github.com/Olsm/cryptowoo-monero-addon
 * GitHub Plugin URI: Olsm/cryptowoo-bitcoin-cash-addon
 * Forked From: Olsm/cryptowoo-monero-addon
 * - Monero Addon was Forked From: Olsm/cryptowoo-bitcoin-cash-addon, Author: Olsm
 * - Bitcoin Cash Addon was Forked From: CryptoWoo/cryptowoo-dash-addon, Author: flxstn
 * Description: Accept XMR payments in WooCommerce. Requires CryptoWoo main plugin and CryptoWoo HD Wallet Add-on.
 * Version: 1.0
 * Author: Olav Småriset
 * Author URI: https://github.com/Olsm
 * License: GPLv2
 * Text Domain: cryptowoo-xmr-addon
 * Domain Path: /lang
 * WC tested up to: 3.2.5
 *
 */

define( 'CWXMR_VER', '1.0' );
define( 'CWXMR_FILE', __FILE__ );
$cw_dir = WP_PLUGIN_DIR . "/cryptowoo";
$cw_license_path = "$cw_dir/am-license-menu.php";

// Load the plugin update library if it is not already loaded
if ( ! class_exists( 'CWXMR_License_Menu' ) && file_exists( $cw_license_path ) ) {
	require_once( $cw_license_path );

	class CWXMR_License_Menu extends CW_License_Menu {};

	CWXMR_License_Menu::instance( CWXMR_FILE, 'CryptoWoo Monero Add-on', CWXMR_VER, 'plugin', 'https://www.cryptowoo.com/' );
}

/**
 * Plugin activation
 */
function cryptowoo_xmr_addon_activate() {

	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	$hd_add_on_file = 'cryptowoo-hd-wallet-addon/cryptowoo-hd-wallet-addon.php';
	if ( ! file_exists( WP_PLUGIN_DIR . '/' . $hd_add_on_file ) || ! file_exists( WP_PLUGIN_DIR . '/cryptowoo/cryptowoo.php' ) ) {

		// If WooCommerce is not installed then show installation notice
		add_action( 'admin_notices', 'cryptowoo_xmr_notinstalled_notice' );

		return;
	} elseif ( ! is_plugin_active( $hd_add_on_file ) ) {
		add_action( 'admin_notices', 'cryptowoo_xmr_inactive_notice' );

		return;
	}
}

register_activation_hook( __FILE__, 'cryptowoo_xmr_addon_activate' );
add_action( 'admin_init', 'cryptowoo_xmr_addon_activate' );

/**
 * CryptoWoo inactive notice
 */
function cryptowoo_xmr_inactive_notice() {

	?>
    <div class="error">
        <p><?php _e( '<b>CryptoWoo Monero add-on error!</b><br>It seems like the CryptoWoo HD Wallet add-on has been deactivated.<br>
       				Please go to the Plugins menu and make sure that the CryptoWoo HD Wallet add-on is activated.', 'cryptowoo-xmr-addon' ); ?></p>
    </div>
	<?php
}


/**
 * CryptoWoo HD Wallet add-on not installed notice
 */
function cryptowoo_xmr_notinstalled_notice() {
	$addon_link = '<a href="https://www.cryptowoo.com/shop/cryptowoo-hd-wallet-addon/" target="_blank">CryptoWoo HD Wallet add-on</a>';
	?>
    <div class="error">
        <p><?php printf( __( '<b>CryptoWoo Monero add-on error!</b><br>It seems like the CryptoWoo HD Wallet add-on is not installed.<br>
					The CryptoWoo Monero add-on will only work in combination with the CryptoWoo main plugin and the %s.', 'cryptowoo-xmr-addon' ), $addon_link ); ?></p>
    </div>
	<?php
}

function cwxmr_hd_enabled() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	return is_plugin_active( 'cryptowoo-hd-wallet-addon/cryptowoo-hd-wallet-addon.php' ) && is_plugin_active( 'cryptowoo/cryptowoo.php' );
}

if ( cwxmr_hd_enabled() ) {
	// Coin symbol and name
	add_filter( 'woocommerce_currencies', 'cwxmr_woocommerce_currencies', 10, 1 );
	add_filter( 'cw_get_currency_symbol', 'cwxmr_get_currency_symbol', 10, 2 );
	add_filter( 'cw_get_enabled_currencies', 'cwxmr_add_coin_identifier', 10, 1 );

	// Custom block explorer URL
	add_filter( 'cw_link_to_address', 'cwxmr_link_to_address', 10, 4 );

	// Options page validations
	add_filter( 'cryptowoo_is_ready', 'cwxmr_cryptowoo_is_ready', 10, 3 );
	add_filter( 'cw_get_shifty_coins', 'cwxmr_cw_get_shifty_coins', 10, 1 );
	add_filter( 'cw_misconfig_notice', 'cwxmr_cryptowoo_misconfig_notice', 10, 2 );

	// Order sorting and prioritizing
	add_filter( 'cw_sort_unpaid_addresses', 'cwxmr_sort_unpaid_addresses', 10, 2 );
	add_filter( 'cw_prioritize_unpaid_addresses', 'cwxmr_prioritize_unpaid_addresses', 10, 2 );
	add_filter( 'cw_filter_batch', 'cwxmr_filter_batch', 10, 2 );

	// Add discovery button to currency option
	//add_filter( 'redux/options/cryptowoo_payments/field/cryptowoo_xmr_address', 'hd_wallet_discovery_button' );
	//add_filter( 'redux/options/cryptowoo_payments/field/cryptowoo_xmr_address', 'hd_wallet_discovery_button' );

	// Exchange rates
	add_filter( 'cw_force_update_exchange_rates', 'cwxmr_force_update_exchange_rates', 10, 2 );
	add_filter( 'cw_cron_update_exchange_data', 'cwxmr_cron_update_exchange_data', 10, 2 );

	// Catch failing processing API (only if processing_fallback is enabled)
	//ToDo add_filter( 'cw_get_tx_api_config', 'cwxmr_cw_get_tx_api_config', 10, 3 );

	// Insight API URL
	//add_filter( 'cw_prepare_insight_api', 'cwxmr_override_insight_url', 10, 4 );

	// Add Blockdozer processing
	add_filter( 'cw_update_tx_details', 'cwxmr_cw_update_tx_details', 10, 5 );

	// Wallet config
	add_filter( 'wallet_config', 'cwxmr_wallet_config', 10, 3 );
	add_filter( 'cw_get_processing_config', 'cwxmr_processing_config', 10, 3 );

	// Options page
	add_action( 'plugins_loaded', 'cwxmr_add_fields', 10 );

	// Check if monero is enabled
    add_filter('cw_coins_enabled', 'cwxmr_coins_enabled_override', 10, 3);

    // get payment address
    add_filter('cw_create_payment_address_XMR', 'cwxmr_get_payment_address', 10, 2);

    // Validate payment address
    add_filter('cw_validate_XMR_address', 'cwxmr_address_validate_override', 10, 2);
}

/**
 * Monero font color for aw-cryptocoins
 * see cryptowoo/assets/fonts/aw-cryptocoins/cryptocoins-colors.css
 */
function cwxmr_coin_icon_color() { ?>
    <style type="text/css">
        i.cc.XMR:before, i.cc.XMR-alt:before {
            content: "\e936";
        }

        i.cc.XMR, i.cc.XMR-alt {
            color: #FF6600;
        }
    </style>
<?php }

add_action( 'wp_head', 'cwxmr_coin_icon_color' );

/**
 * Add wallet config
 *
 * @param $wallet_config
 * @param $currency
 * @param $options
 *
 * @return array
 */
function cwxmr_wallet_config( $wallet_config, $currency, $options ) {
	if ( $currency === 'XMR' ) {
		$wallet_config                       = array(
			'coin_client'   => 'monero',
			'request_coin'  => 'XMR',
			'multiplier'    => (float) $options['multiplier_xmr'],
			'safe_address'  => false,
			'decimals'      => 8
		);
		$wallet_config['hdwallet']           = CW_Validate::check_if_unset( $wallet_config['cryptowoo_xmr_address'], $options, false ) &&
                                               CW_Validate::check_if_unset( $wallet_config['cryptowoo_xmr_view_key'], $options, false );
		$wallet_config['coin_protocols'][]   = 'xmr';
		$wallet_config['forwarding_enabled'] = false;
	}

	return $wallet_config;
}

/**
 * @param string $payment_address
 * @param array $options
 */
function cwxmr_get_payment_address( $payment_address, $options ) {
    if (CW_Validate::check_if_unset('cryptowoo_xmr_address', $options) && CW_Validate::check_if_unset('cryptowoo_xmr_view_key', $options)) {
        $payment_address = $options['cryptowoo_xmr_address'];
    }
    return $payment_address;
}

/**
 * @param string[] $coins
 * @param string[]||string $coin_identifiers
 * @param array $options
 *
 * @return mixed
 */
function cwxmr_coins_enabled_override( $coins, $coin_identifiers, $options ) {
    if (isset($coins['hd_xmr_enabled'])) {
        if (isset($options['cryptowoo_xmr_address']) && isset($options['cryptowoo_xmr_view_key'])) {
            $coins['monero_enabled'] = true;
        }
    } elseif (is_array($coin_identifiers) && isset($coin_identifiers['XMR'])) {
	    if (isset($options['cryptowoo_xmr_address']) && isset($options['cryptowoo_xmr_view_key'])) {
		    $coins['XMR'] = "Monero";
	    }
    }
    return $coins;
}

/**
 * @param bool $address_valid
 * @param string $payment_address
 *
 * @return bool
 */
function cwxmr_address_validate_override ( $address_valid, $payment_address ) {
    if ( cwxmr_address_is_valid($payment_address) ) {
        return true;
    }
    return $address_valid;
}

/**
 * @param string $payment_address
 *
 * @return bool
 */
function cwxmr_address_is_valid( $payment_address ) {
	$address_valid = true;

	if (strpos($payment_address, "4") !== 0) {
		$address_valid = false;
	}

	if (strlen($payment_address) !== 95) {
		$address_valid = false;
	}

	$second = substr($payment_address, 1, 1);
	if (!is_numeric($second) && $second != "A" && $second != "B") {
		$address_valid = false;
	}

	return $address_valid;
}

/**
 * Add address validation
 *
 * @param $field
 * @param $value
 * @param $existing_value
 *
 * @return array
 */
function cwma_validate_monero_address( $field, $value, $existing_value ) {
	if(empty($value) || $value === $existing_value) {
		$return['value'] = $value;
		return $return;
	}

	if(!cwxmr_address_is_valid($value)) {

		$value = $existing_value;

		$field['msg'] = "Monero address invalid! <br>";
		$return['error'] = $field;

		if(WP_DEBUG) {
			file_put_contents(CW_LOG_DIR . 'cryptowoo-error.log', date("Y-m-d H:i:s") . __FILE__ . "\n".'redux_validate_address debug - '. $field['id'].' currency: '.var_export('xmr', true) .' value: '.var_export($value, true) . ' | result: ' .var_export($return, true) ."\n", FILE_APPEND);
		}
	}
	$return['value'] = $value;
	return $return;
}

/**
 * Add view key validation
 *
 * @param $field
 * @param $value
 * @param $existing_value
 *
 * @return array
 */
function cwma_validate_monero_view_key( $field, $value, $existing_value ) {
	$options = get_option('cryptowoo_payments');

	if(empty($options['cryptowoo_xmr_address']) && (empty($value) || $value === $existing_value)) {
		$return['value'] = $value;
		return $return;
	}

	$view_key_valid = true;

	if (empty($value)) {
		$view_key_valid = false;
	}

	if(!$view_key_valid) {

		$value = $existing_value;

		$field['msg'] = "Monero view key invalid! <br>";
		$return['error'] = $field;

		if(WP_DEBUG) {
			file_put_contents(CW_LOG_DIR . 'cryptowoo-error.log', date("Y-m-d H:i:s") . __FILE__ . "\n".'redux_validate_address debug - '. $field['id'].' currency: '.var_export('xmr', true) .' value: '.var_export($value, true) . ' | result: ' .var_export($return, true) ."\n", FILE_APPEND);
		}
	}
	$return['value'] = $value;
	return $return;
}

/**
 * Processing API configuration error
 *
 * @param $enabled
 * @param $options
 *
 * @return mixed
 */
function cwxmr_cryptowoo_misconfig_notice( $enabled, $options ) {
	$enabled['XMR'] = $options['processing_api_xmr'] === 'disabled' &&
                      ( (bool) CW_Validate::check_if_unset( 'cryptowoo_xmr_address', $options ) ) &&
                      ( (bool) CW_Validate::check_if_unset( 'cryptowoo_xmr_address', $options ) );

	return $enabled;
}

/**
 * Add currency name
 *
 * @param $currencies
 *
 * @return mixed
 */
function cwxmr_woocommerce_currencies( $currencies ) {
	$currencies['XMR'] = __( 'Monero', 'cryptowoo' );

	return $currencies;
}


/**
 * Add currency symbol
 *
 * @param $currency_symbol
 * @param $currency
 *
 * @return string
 */
function cwxmr_get_currency_symbol( $currency_symbol, $currency ) {
	return $currency === 'XMR' ? 'XMR' : $currency_symbol;
}


/**
 * Add coin identifier
 *
 * @param $coin_identifiers
 *
 * @return array
 */
function cwxmr_add_coin_identifier( $coin_identifiers ) {
	$coin_identifiers['XMR'] = 'xmr';

	return $coin_identifiers;
}

/**
 * Add InstantSend and "raw" zeroconf settings to processing config
 *
 * @param $pc_conf
 * @param $currency
 * @param $options
 *
 * @return array
 */
function cwxmr_processing_config( $pc_conf, $currency, $options ) {
	if ( $currency === 'XMR' ) {
		$pc_conf['min_confidence'] = 1;
	}

	return $pc_conf;
}


/**
 * Override links to payment addresses
 *
 * @param $url
 * @param $address
 * @param $currency
 * @param $options
 *
 * @return string
 */
function cwxmr_link_to_address( $url, $address, $currency, $options ) {
    //ToDo
	if ( $currency === 'XMR' ) {
	    /*
		$url = "http://xmrchain.net/address/{$address}";
		if ( $options['preferred_block_explorer_xmr'] === 'custom' && isset( $options['custom_block_explorer_xmr'] ) ) {
			$url = preg_replace( '/{{ADDRESS}}/', $address, $options['custom_block_explorer_xmr'] );
			if ( ! wp_http_validate_url( $url ) ) {
				$url = '#';
			}
		}
	    */
	}

	return $url;
}

/**
 * Do xmrchain.net api processing if chosen
 *
 * @param $batch_data
 * @param $batch_currency
 * @param $orders
 * @param $processing
 * @param $options
 *
 * @return string
 */
function cwxmr_cw_update_tx_details( $batch_data, $batch_currency, $orders, $processing, $options ) {
	if ( $batch_currency == "XMR" && $options['processing_api_xmr'] == "xmrchain.net" ) {
		//ToDo
	}

	return $batch_data;
}

function cwxmr_monero_api_get_block_height($options) {
    //ToDo
}

function cwxmr_monero_api_tx_update($address, $order, $options) {
    //ToDo
}


/**
 * Add currency to cryptowoo_is_ready
 *
 * @param $enabled
 * @param $options
 * @param $changed_values
 *
 * @return array
 */
function cwxmr_cryptowoo_is_ready( $enabled, $options, $changed_values ) {
	$enabled['XMR']           = (bool) CW_Validate::check_if_unset( 'cryptowoo_xmr_address', $options, false );
	$enabled['XMR_transient'] = (bool) CW_Validate::check_if_unset( 'cryptowoo_xmr_address', $changed_values, false );

	return $enabled;
}


/**
 * Add currency to is_cryptostore check
 *
 * @param $cryptostore
 * @param $woocommerce_currency
 *
 * @return bool
 */
function cwxmr_is_cryptostore( $cryptostore, $woocommerce_currency ) {
	return (bool) $cryptostore ?: $woocommerce_currency === 'XMR';
}

add_filter( 'is_cryptostore', 'cwxmr_is_cryptostore', 10, 2 );

/**
 * Add currency to Shifty button option field
 *
 * @param $select
 *
 * @return array
 */
function cwxmr_cw_get_shifty_coins( $select ) {
	$select['XMR'] = sprintf( __( 'Display only on %s payment pages', 'cryptowoo' ), 'Monero' );

	return $select;
}


/**
 * Add HD index key id for currency
 *
 * @param $index_key_ids
 *
 * @return array
 */
function cwxmr_index_key_ids( $index_key_ids ) {
	$index_key_ids['XMR'] = 'cryptowoo_xmr_index';

	return $index_key_ids;
}


/**
 * Add HD mpk key id for currency
 *
 * @param $mpk_key_ids
 *
 * @return array
 */
function cwxmr_mpk_key_ids( $mpk_key_ids ) {
	$mpk_key_ids['XMR'] = 'cryptowoo_xmr_address';

	return $mpk_key_ids;
}


/**
 * Override mpk_key
 *
 * @param $mpk_key
 * @param $currency
 * @param $options
 *
 * @return string
 */
function cwxmr_get_mpk_data_mpk_key( $mpk_key, $currency, $options ) {
	if ( $currency === 'XMR' ) {
		$mpk_key = "cryptowoo_xmr_address";
	}

	return $mpk_key;
}

/**
 * Add currency force exchange rate update button
 *
 * @param $results
 *
 * @return array
 */
function cwxmr_force_update_exchange_rates( $results ) {
	$results['xmr'] = CW_ExchangeRates::update_altcoin_fiat_rates( 'XMR', false, true );

	return $results;
}

/**
 * Add currency to background exchange rate update
 *
 * @param $data
 * @param $options
 *
 * @return array
 */
function cwxmr_cron_update_exchange_data( $data, $options ) {
	$xmr = CW_ExchangeRates::update_altcoin_fiat_rates( 'XMR', $options );

	// Maybe log exchange rate updates
	if ( (bool) $options['logging']['rates'] ) {
		if ( $xmr['status'] === 'not updated' || strpos( $xmr['status'], 'disabled' ) ) {
			$data['xmr'] = strpos( $xmr['status'], 'disabled' ) ? $xmr['status'] : $xmr['last_update'];
		} else {
			$data['xmr'] = $xmr;
		}
	}

	return $data;
}

/**
 * Add currency to currencies array
 *
 * @param $currencies
 *
 * @return array
 */
function cwxmr_add_currency_to_array( $currencies ) {
	$currencies[] = 'XMR';

	return $currencies;
}

/**
 * Add XMR addresses to sort unpaid addresses
 *
 * @param array $top_n
 * @param mixed $address
 *
 * @return array
 */
function cwxmr_sort_unpaid_addresses( $top_n, $address ) {
	if ( strcmp( $address->payment_currency, 'XMR' ) === 0 ) {
		$top_n[3]['XMR'][] = $address;
	}

	return $top_n;
}

/**
 * Add XMR addresses to prioritize unpaid addresses
 *
 * @param array $top_n
 * @param mixed $address
 *
 * @return array
 */
function cwxmr_prioritize_unpaid_addresses( $top_n, $address ) {
	if ( strcmp( $address->payment_currency, 'XMR' ) === 0 ) {
		$top_n[3][] = $address;
	}

	return $top_n;
}

/**
 * Add XMR addresses to address_batch
 *
 * @param array $address_batch
 * @param mixed $address
 *
 * @return array
 */
function cwxmr_filter_batch( $address_batch, $address ) {
	if ( strcmp( $address->payment_currency, 'XMR' ) === 0 ) {
		$address_batch['XMR'][] = $address->address;
	}

	return $address_batch;
}


/* ToDo
/**
 * Fallback on failing API
 *
 * @param $api_config
 * @param $currency
 *
 * @return array
 *
function cwxmr_cw_get_tx_api_config( $api_config, $currency ) {
	// ToDo: add Blockcypher
	if ( $currency === 'XMR' ) {
        $api_config->tx_update_api   = 'xmrchain.net';
        $api_config->skip_this_round = false;
	}

	return $api_config;
}
*/

/**
 * Override Insight API URL if no URL is found in the settings
 *
 * @param $insight
 * @param $endpoint
 * @param $currency
 * @param $options
 *
 * @return mixed
 */
function cwxmr_override_insight_url( $insight, $endpoint, $currency, $options ) {
	if ( $currency === 'XMR' && isset( $options['processing_fallback_url_xmr'] ) && wp_http_validate_url( $options['processing_fallback_url_xmr'] ) ) {
		$fallback_url = $options['processing_fallback_url_xmr'];
		$urls         = $endpoint ? CW_Formatting::format_insight_api_url( $fallback_url, $endpoint ) : CW_Formatting::format_insight_api_url( $fallback_url, '' );
		$insight->url = $urls['surl'];
	}

	return $insight;
}

/**
 * Add Redux options
 */
function cwxmr_add_fields() {
	$woocommerce_currency = get_option( 'woocommerce_currency' );

	/*
	 * Required confirmations
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-confirmations',
		'id'         => 'cryptowoo_xmr_min_conf',
		'type'       => 'spinner',
		'title'      => sprintf( __( '%s Minimum Confirmations', 'cryptowoo' ), 'XMR' ),
		'desc'       => sprintf( __( 'Minimum number of confirmations for <strong>%s</strong> transactions - %s Confirmation Threshold', 'cryptowoo' ), 'Monero', 'Monero' ),
		'default'    => 1,
		'min'        => 1,
		'step'       => 1,
		'max'        => 1,
	) );

	// ToDo: Enable raw zeroconf
	/*
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-confirmations',
		'id'         => 'xmr_raw_zeroconf',
		'type'       => 'switch',
		'title'      => __( 'Monero "Raw" Zeroconf', 'cryptowoo' ),
		'subtitle'   => __( 'Accept unconfirmed Monero transactions as soon as they are seen on the network.', 'cryptowoo' ),
		'desc'       => sprintf( __( '%sThis practice is generally not recommended. Only enable this if you know what you are doing!%s', 'cryptowoo' ), '<strong>', '</strong>' ),
		'default'    => false,
		'required'   => array(
			//array('processing_api_xmr', '=', 'custom'),
			array( 'cryptowoo_xmr_min_conf', '=', 0 )
		),
	) );
	*/


	/*
	 * ToDo: Zeroconf order amount threshold
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-zeroconf',
		'id'         => 'cryptowoo_max_unconfirmed_xmr',
		'type'       => 'slider',
		'title'      => sprintf( __( '%s zeroconf threshold (%s)', 'cryptowoo' ), 'Monero', $woocommerce_currency ),
		'desc'       => '',
		'required'   => array( 'cryptowoo_xmr_min_conf', '<', 1 ),
		'default'    => 100,
		'min'        => 0,
		'step'       => 10,
		'max'        => 500,
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-zeroconf',
		'id'         => 'cryptowoo_xmr_zconf_notice',
		'type'       => 'info',
		'style'      => 'info',
		'notice'     => false,
		'required'   => array( 'cryptowoo_xmr_min_conf', '>', 0 ),
		'icon'       => 'fa fa-info-circle',
		'title'      => sprintf( __( '%s Zeroconf Threshold Disabled', 'cryptowoo' ), 'Monero' ),
		'desc'       => sprintf( __( 'This option is disabled because you do not accept unconfirmed %s payments.', 'cryptowoo' ), 'Monero' ),
	) );
	 */


	/*
	// Remove 3rd party confidence
	Redux::removeField( 'cryptowoo_payments', 'custom_api_confidence', false );

	/*
	 * Confidence warning
	 * /
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-confidence',
			'id'    => 'xmr_confidence_warning',
			'type'  => 'info',
			'title' => __('Be careful!', 'cryptowoo'),
			'style' => 'warning',
			'desc'  => __('Accepting transactions with a low confidence value increases your exposure to double-spend attacks. Only proceed if you don\'t automatically deliver your products and know what you\'re doing.', 'cryptowoo'),
			'required' => array('min_confidence_xmr', '<', 95)
	));

	/*
	 * Transaction confidence
	 * /

	Redux::setField( 'cryptowoo_payments', array(
			'section_id'        => 'processing-confidence',
			'id'      => 'min_confidence_xmr',
			'type'    => 'switch',
			'title'   => sprintf(__('%s transaction confidence (%s)', 'cryptowoo'), 'Monero', '%'),
			//'desc'    => '',
			'required' => array('cryptowoo_xmr_min_conf', '<', 1),

	));


	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-confidence',
		'id'      => 'min_confidence_xmr_notice',
		'type'    => 'info',
		'style' => 'info',
		'notice'    => false,
		'required' => array('cryptowoo_xmr_min_conf', '>', 0),
		'icon'  => 'fa fa-info-circle',
		'title'   => sprintf(__('%s "Raw" Zeroconf Disabled', 'cryptowoo'), 'Monero'),
		'desc'    => sprintf(__('This option is disabled because you do not accept unconfirmed %s payments.', 'cryptowoo'), 'Monero'),
	));

	// Re-add 3rd party confidence
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-confidence',
		'id'       => 'custom_api_confidence',
		'type'     => 'switch',
		'title'    => __('Third Party Confidence Metrics', 'cryptowoo'),
		'subtitle' => __('Enable this to use the chain.so confidence metrics when accepting zeroconf transactions with your custom Bitcoin, Litecoin, or Dogecoin API.', 'cryptowoo'),
		'default'  => false,
	));
    */

	// Remove blockcypher token field
	Redux::removeField( 'cryptowoo_payments', 'blockcypher_token', false );

	/*
	 * Processing API
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-api',
		'id'                => 'processing_api_xmr',
		'type'              => 'select',
		'title'             => sprintf( __( '%s Processing API', 'cryptowoo' ), 'Monero' ),
		'subtitle'          => sprintf( __( 'Choose the API provider you want to use to look up %s payments.', 'cryptowoo' ), 'Monero' ),
		'options'           => array(
			'xmrchain.net' => 'xmrchain.net',
			'disabled'   => __( 'Disabled', 'cryptowoo' ),
		),
		'desc'              => '',
		'default'           => 'disabled',
		'ajax_save'         => false, // Force page load when this changes
		'validate_callback' => 'redux_validate_processing_api',
		'select2'           => array( 'allowClear' => false ),
	) );

	// Re-add blockcypher token field
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-api',
		'id'                => 'blockcypher_token',
		'type'              => 'text',
		'ajax_save'         => false, // Force page load when this changes
		'desc'              => sprintf( __( '%sMore info%s', 'cryptowoo' ), '<a href="http://dev.blockcypher.com/#rate-limits-and-tokens" title="BlockCypher Docs: Rate limits and tokens" target="_blank">', '</a>' ),
		'title'             => __( 'BlockCypher Token (optional)', 'cryptowoo' ),
		'subtitle'          => sprintf( __( 'Use the API token from your %sBlockCypher%s account.', 'cryptowoo' ), '<strong><a href="https://accounts.blockcypher.com/" title="BlockCypher account vtcboard" target="_blank">', '</a></strong>' ),
		'validate_callback' => 'redux_validate_token'
	) );

	/*
	 * Preferred exchange rate provider
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'rates-exchange',
		'id'                => 'preferred_exchange_xmr',
		'type'              => 'select',
		'title'             => 'Monero Exchange (XMR/BTC)',
		'subtitle'          => sprintf( __( 'Choose the exchange you prefer to use to calculate the %sMonero to Bitcoin exchange rate%s', 'cryptowoo' ), '<strong>', '</strong>.' ),
		'desc'              => sprintf( __( 'Cross-calculated via BTC/%s', 'cryptowoo' ), $woocommerce_currency ),
		'options'           => array(
			'bittrex'    => 'Bittrex',
			'poloniex'   => 'Poloniex',
			'shapeshift' => 'ShapeShift'
		),
		'default'           => 'poloniex',
		'ajax_save'         => false, // Force page load when this changes
		'validate_callback' => 'redux_validate_exchange_api',
		'select2'           => array( 'allowClear' => false )
	) );

	/*
	 * Exchange rate multiplier
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'    => 'rates-multiplier',
		'id'            => 'multiplier_xmr',
		'type'          => 'slider',
		'title'         => sprintf( __( '%s exchange rate multiplier', 'cryptowoo' ), 'Monero' ),
		'subtitle'      => sprintf( __( 'Extra multiplier to apply when calculating %s prices.', 'cryptowoo' ), 'Monero' ),
		'desc'          => '',
		'default'       => 1,
		'min'           => .01,
		'step'          => .01,
		'max'           => 2,
		'resolution'    => 0.01,
		'validate'      => 'comma_numeric',
		'display_value' => 'text'
	) );

	/*
	 * Preferred blockexplorer
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'rewriting',
		'id'         => 'preferred_block_explorer_xmr',
		'type'       => 'select',
		'title'      => sprintf( __( '%s Block Explorer', 'cryptowoo' ), 'Monero' ),
		'subtitle'   => sprintf( __( 'Choose the block explorer you want to use for links to the %s blockchain.', 'cryptowoo' ), 'Monero' ),
		'desc'       => '',
		'options'    => array(
			'autoselect' => __( 'Autoselect by processing API', 'cryptowoo' ),
			'xmrchain.net' => 'xmrchain.net',
			'custom'     => __( 'Custom (enter URL below)' ),
		),
		'default'    => 'xmrchain.net',
		'select2'    => array( 'allowClear' => false )
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'rewriting',
		'id'         => 'preferred_block_explorer_xmr_info',
		'type'       => 'info',
		'style'      => 'critical',
		'icon'       => 'el el-warning-sign',
		'required'   => array(
			array( 'preferred_block_explorer_xmr', '=', 'custom' ),
			array( 'custom_block_explorer_xmr', '=', '' ),
		),
		'desc'       => sprintf( __( 'Please enter a valid URL in the field below to use a custom %s block explorer', 'cryptowoo' ), 'Monero' ),
	) );

	/*
	 * Currency Switcher plugin decimals
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'rewriting-switcher',
		'id'         => 'decimals_XMR',
		'type'       => 'select',
		'title'      => sprintf( __( '%s amount decimals', 'cryptowoo' ), 'Monero' ),
		'subtitle'   => '',
		'desc'       => __( 'This option overrides the decimals option of the WooCommerce Currency Switcher plugin.', 'cryptowoo' ),
		'required'   => array( 'add_currencies_to_woocs', '=', true ),
		'options'    => array(
			2 => '2',
			4 => '4',
			6 => '6',
			8 => '8'
		),
		'default'    => 4,
		'select2'    => array( 'allowClear' => false )
	) );

	/*
	 * HD wallet section start
	 */

	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-other',
		'id'         => 'wallets-other-xmr',
		'type'       => 'section',
		'title'      => __( 'Monero', 'cryptowoo-monero-addon' ),
		//'required' => array('testmode_enabled','equals','0'),
		'icon'       => 'cc-XMR',
		//'subtitle' => __('Use the field with the correct prefix of your Litecoin MPK. The prefix depends on the wallet client you used to generate the key.', 'cryptowoo-hd-wallet-addon'),
		'indent'     => true,
	) );

	/*
	 * Extended public key
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-other',
		'id'                => 'cryptowoo_xmr_address',
		'type'              => 'text',
		'title'             => sprintf( __( '%sprefix%s', 'cryptowoo-monero-addon' ), '<b>XMR "4.."', '</b>' ),
		'desc'              => __( 'Monero (XMR) address', 'cryptowoo-monero-addon' ),
		'validate_callback' => 'cwma_validate_monero_address',
		'placeholder'       => '4..',
	) );
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-other',
		'id'                => 'cryptowoo_xmr_view_key',
		'type'              => 'text',
		'subtitle'          => '',
		'title'             => sprintf( __( '%s View Key', 'cryptowoo-monero-addon' ), 'Monero' ),
		'desc'              => __( 'Change the view key to match the view key of your wallet client.', 'cryptowoo-monero-addon' ),
		'validate_callback' => 'cwma_validate_monero_view_key',
		//ToDo: Validate view key
		//'validate_callback' => 'redux_validate_view_key',
	) );

	/*
	 * HD wallet section end
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-hdwallet',
		'id'         => 'section-end',
		'type'       => 'section',
		'indent'     => false,
	) );

	// Re-add Bitcoin testnet section
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-hdwallet',
		'id'         => 'wallets-hdwallet-testnet',
		'type'       => 'section',
		'title'      => __( 'TESTNET', 'cryptowoo-hd-wallet-addon' ),
		//'required' => array('testmode_enabled','equals','0'),
		'icon'       => 'fa fa-flask',
		'desc'       => __( 'Accept BTC testnet coins to addresses created via a "tpub..." extended public key. (testing purposes only!)<br><b>Depending on the position of the first unused address, it could take a while until your changes are saved.</b>', 'cryptowoo-hd-wallet-addon' ),
		'indent'     => true,
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-hdwallet',
		'id'                => 'cryptowoo_btc_test_mpk',
		'type'              => 'text',
		'ajax_save'         => false,
		'username'          => false,
		'desc'              => __( 'Bitcoin TESTNET extended public key (tpub...)', 'cryptowoo-hd-wallet-addon' ),
		'title'             => __( 'Bitcoin TESTNET HD Wallet Extended Public Key', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_mpk',
		'placeholder'       => 'tpub...',
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => sprintf( __( 'If you enter a used key you will have to run the address discovery process after saving this setting.%sUse a dedicated HD wallet (or at least a dedicated xpub) for your store payments to prevent address reuse.', 'cryptowoo-hd-wallet-addon' ), '<br>' ),
		)
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-hdwallet',
		'id'                => 'derivation_path_btctest',
		'type'              => 'select',
		'subtitle'          => '',
		'title'             => sprintf( __( '%s Derivation Path', 'cryptowoo-hd-wallet-addon' ), 'BTCTEST' ),
		'desc'              => __( 'Change the derivation path to match the derivation path of your wallet client.', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_derivation_path',
		'options'           => array(
			'0/' => __( 'm/0/i (e.g. Electrum Standard Wallet)', 'cryptowoo-hd-wallet-addon' ),
			'm'  => __( 'm/i (BIP44 Account)', 'cryptowoo-hd-wallet-addon' ),
		),
		'default'           => '0/',
		'select2'           => array( 'allowClear' => false )
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-hdwallet',
		'id'         => 'section-end',
		'type'       => 'section',
		'indent'     => false,
	) );

}

