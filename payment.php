<?php

/**
 * MontyPay WooCommerce Class
 */
class MontyPayWoocommercePayment {
//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Constructor
     */
    public function __construct($code) {

        $this->code    = $code;
        $this->id      = 'wc_gateway_montypay_' . $code;
        $this->gateway = 'wc_gateway_montypay_' . $code;

        //filters
        add_filter('woocommerce_payment_gateways', array($this, 'register'), 0);
        add_filter('plugin_action_links_' . MONTY_WOO_PLUGIN, array($this, 'plugin_action_links'));
        add_action('admin_enqueue_scripts', array($this, 'load_admin_css_js'));

        // $paymentOptions = get_option('woocommerce_monty_' . $this->code . '_settings');
        // if ((isset($paymentOptions['enabled']) && $paymentOptions['enabled'] == 'yes') && (($this->code == 'v2' && (isset($paymentOptions['newDesign']) && $paymentOptions['newDesign'] == 'yes') && (isset($paymentOptions['listOptions']) && $paymentOptions['listOptions'] == 'multigateways')) || ($this->code == 'embedded' ))) {
        // }
        add_action('wp_enqueue_scripts', array($this, 'load_css_js'));

    }

//-----------------------------------------------------------------------------------------------------------------------------


//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Register the gateway to WooCommerce
     */
    public function register($gateways) {
        include_once("includes/payments/class-wc-gateway-montypay-$this->code.php");
        $gateways[] = $this->gateway;
        return $gateways;
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Show action links on the plugin screen.
     *
     * @param mixed $links Plugin Action links.
     *
     * @return array
     */
    public function plugin_action_links($links) {
        //http://wordpress-5.4.2.com/wp-admin/admin.php?page=wc-settings&tab=checkout&section=$this->id
        $name = [
            's2s'       => __('Cards', 'monty-woocommerce'),
            'wallets' => __('Wallets', 'monty-woocommerce'),
            'hosted' => __('Hosted', 'monty-woocommerce'),
            'benefit' => __('Benefit', 'monty-woocommerce'),
            'stripejs' => __('Stripe JS', 'monty-woocommerce'),
        ];

        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=' . $this->id) . '">' . $name[$this->code] . '</a>',
        );
        return array_merge($links, $plugin_links);
    }


//-----------------------------------------------------------------------------------------------------------------------------------------
    function load_css_js() {

        wp_enqueue_style('montypay-style', plugins_url('assets/css/montypay.css', __FILE__), [], MONTY_WOO_PLUGIN_VERSION);

        if(is_checkout()){
            wp_enqueue_script('handle_3ds_js',  plugins_url('assets/js/handle_redirect_3ds.js', __FILE__), [], MONTY_WOO_PLUGIN_VERSION, true);

            wp_enqueue_script('montypay_js',  plugins_url('assets/js/montypay_js.js', __FILE__), [], MONTY_WOO_PLUGIN_VERSION, true);

            wp_localize_script( 'montypay_js', 'ajax_object',
            array( 
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'lang' => get_locale(),
            
            ) );
        }

        $mdOptions = get_option('woocommerce_wc_gateway_montypay_wallets_settings');
        $v2Options = get_option('woocommerce_wc_gateway_montypay_s2s_settings');

        $istest      = (isset($v2Options['testMode']) && $v2Options['testMode'] == 'yes' );
        $isSAU       = (isset($v2Options['countryMode']) && $v2Options['countryMode'] == 'SAU' );
        $sessionPath = (($istest) ? 'demo' : ($isSAU ? 'sa' : 'portal'));

       
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function load_admin_css_js() {
        wp_enqueue_style('montypay-admin-style', plugins_url('assets/css/montypay-admin.css', __FILE__), [], MONTY_WOO_PLUGIN_VERSION);

        if(isset($_GET['section']) && $_GET['section'] == 'wc_gateway_montypay_wallets'){
            wp_enqueue_script('montypay_admin_wallets',  plugins_url('assets/js/montypay_admin_wallets.js', __FILE__), [], MONTY_WOO_PLUGIN_VERSION, true);

            wp_localize_script( 'montypay_admin_wallets', 'ajax_object',
                array( 
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'lang' => get_locale(),
                
                ) 
            );
        }
        if(isset($_GET['section']) && $_GET['section'] == 'wc_gateway_montypay_hosted'){
            wp_enqueue_script('montypay_admin_hosted',  plugins_url('assets/js/montypay_admin_hosted.js', __FILE__), [], MONTY_WOO_PLUGIN_VERSION, true);

            wp_localize_script( 'montypay_admin_hosted', 'ajax_object',
                array( 
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'lang' => get_locale(),
                ) 
            );
        }
        

    }

//-----------------------------------------------------------------------------------------------------------------------------
}
