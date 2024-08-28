
<?php

/**
 * Plugin Name: MontyPay Payment Gateway
 * Plugin URI: 
 * Description: MontyPay Payment Gateway for WooCommerce.
 * Version: 2.1.5
 * Author: MontyPay
 * Author URI: https://www.montypay.com/
 *
 * WC requires at least: 4.0
 * WC tested up to: 6.9.3
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * 
 * @package Monty
 */
if (!defined('ABSPATH')) {
    exit;
}

//MFWOO_PLUGIN
define('MONTY_WOO_PLUGIN_VERSION', '2.1.5');
define('MONTY_WOO_PLUGIN', plugin_basename(__FILE__));
define('MONTY_WOO_PLUGIN_NAME', dirname(MONTY_WOO_PLUGIN));
define('MONTY_WOO_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * Monty WooCommerce Class
 */

// Add HPOS compatibility
add_filter('woocommerce_high_performance_order_storage_enabled', '__return_true');
add_filter('woocommerce_high_performance_order_storage_is_enabled', '__return_true');

class MontyPayWoocommerce {
//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Constructor
     */
    public function __construct() {

        add_filter('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 2);

        //actions
        // add_action('activate_plugin', array($this, 'superessActivate'), 0);
        add_action('plugins_loaded', array($this, 'init'), 0);

        // add_action('in_plugin_update_message-' . MONTY_WOO_PLUGIN, array($this, 'prefix_plugin_update_message'), 10, 2);
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Show row meta on the plugin screen.
     *
     * @param mixed $links Plugin Row Meta.
     * @param mixed $file  Plugin Base file.
     *
     * @return array
     */
    public static function plugin_row_meta($links, $file) {

        if (MONTY_WOO_PLUGIN === $file) {
            $row_meta = array(
                'docs'    => '<a href="' . esc_url('https://docs.montypay.com') . '" aria-label="' . esc_attr__('View MontyPay documentation', 'monty-woocommerce') . '">' . esc_html__('Docs', 'woocommerce') . '</a>',
            );

            //unset($links[2]);
            return array_merge($links, $row_meta);
        }

        return (array) $links;
    }

//-----------------------------------------------------------------------------------------------------------------------------
    function superessActivate($plugin) {

        // Localisation
        $arTrans = 'montypay-woocommerce-ar';

        $filePath = WP_LANG_DIR . '/plugins/' . $arTrans;
        $moFileAr = $filePath . '.mo';
        $poFileAr = $filePath . '.po';

        $newFilePath = MONTY_WOO_PLUGIN_PATH . 'i18n/languages/' . $arTrans;
        $moNewFileAr = $newFilePath . '.mo';
        $poNewFileAr = $newFilePath . '.po';

        copy($moNewFileAr, $moFileAr);
        copy($poNewFileAr, $poFileAr);

        //it is very important to say that the plugin is Monty 
        if ($plugin == MONTY_WOO_PLUGIN && !in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) && !array_key_exists('woocommerce/woocommerce.php', apply_filters('active_plugins', get_site_option('active_sitewide_plugins')))) {
            wp_die(__('WooCommerce plugin needs to be activated first to activate MontyPay plugin', 'monty-woocommerce'));
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Init localizations and files
     */
    public function init() {
        // Localisation
        load_plugin_textdomain('monty-woocommerce', false, MONTY_WOO_PLUGIN_NAME . '/i18n/languages');

    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Show important release note
     * @param type $data
     * @param type $response
     */
    // function prefix_plugin_update_message($data, $response) {

    //     $notice = null;
    //     if (!empty($data['upgrade_notice'])) {
    //         $notice = trim(strip_tags($data['upgrade_notice']));
    //     } else if (!empty($response->upgrade_notice)) {
    //         $notice = trim(strip_tags($response->upgrade_notice));
    //     }

    //     if (!empty($notice)) {
    //         printf(
    //                 '<div class="update-message notice-error"><p style="background-color: #d54e21; padding: 10px; color: #f9f9f9; margin-top: 10px"><strong>Important Upgrade Notice: </strong>%s',
    //                 __($notice, 'montypay-woocommerce')
    //         );
    //     }
    //     //https://andidittrich.com/2015/05/howto-upgrade-notice-for-wordpress-plugins.html
    // }

//-----------------------------------------------------------------------------------------------------------------------------
}

new MontyPayWoocommerce();

//load payment
if (!class_exists('MontyPayWoocommercePayment')) {
    require_once 'payment.php';
    include_once( 'montypay_functions.php' );

    new MontyPayWoocommercePayment('s2s');
    new MontyPayWoocommercePayment('wallets');
    new MontyPayWoocommercePayment('hosted');
    new MontyPayWoocommercePayment('benefit');
    new MontyPayWoocommercePayment('stripejs');

}


//-----------------------------------------------------------------------------------------------------------------------------

/**
 * Filter an input field
 * 
 * @param string $name
 * @param string $type
 * @return string
 */
function mfFilterInputField($name, $type = 'GET') {
    // $value = $GLOBALS["_$type"][$name] ?? NULL;
    $value = isset($GLOBALS["_$type"][$name]) ? $GLOBALS["_$type"][$name] : NULL;

    if (isset($value)) {
        $value = htmlspecialchars($value);
        return sanitize_text_field($value);
    }
    return null;
}

##########################################################################

function register_custom_page_rewrite_rule() {
    add_rewrite_rule('^awaiting-3d-secure$', 'index.php?awaiting_3d_secure=1', 'top');
}
add_action('init', 'register_custom_page_rewrite_rule');

function add_custom_page_query_var($vars) {
    $vars[] = 'awaiting_3d_secure';
    return $vars;
}
add_filter('query_vars', 'add_custom_page_query_var');

function custom_page_template_include($template) {
    if (get_query_var('awaiting_3d_secure')) {
        return plugin_dir_path(__FILE__) . 'redirects/awaiting_3d_secure.php';
    }
    return $template;
}
add_filter('template_include', 'custom_page_template_include');

add_action( 'before_woocommerce_init', function() {
    if ( class_exists( 'Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', 'montypay-payment-gateway/montypay-woocommerce.php', true );
    }
} );
