<?php

if (!defined('ABSPATH')) {
    exit;
}
require_once MONTY_WOO_PLUGIN_PATH . 'includes/libraries/MontyPayLibrary.php';

if (!class_exists('WC_Gateway_MontyPay')) {
    include_once('class-wc-gateway-montypay.php');
}

/**
 * Monty_V2 Payment Gateway class.
 *
 * Extended by individual payment gateways to handle payments.
 *
 * @class       WC_Gateway_Monty_pg
 * @extends     WC_Payment_Gateway
 */
class WC_Gateway_MontyPay_s2s extends WC_Gateway_MontyPay {

    protected $code;
    protected $count           = 0;
    protected $gateways        = [];
    protected $appleRegistered = false;
    protected $totalAmount     = 0;
    protected $monty;

    /**
     * Constructor
     */
    public function __construct() {
        $this->code               = 's2s';
        $this->method_description = __("MontyPay Debit/Credit Card payment.", 'monty-woocommerce');
        $this->method_title       = __('MontyPay - Cards', 'monty-woocommerce');
        parent::__construct();


        $this->title       = get_translation('Credit/Debit card');

        $this->has_fields = true;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
function admin_options()
    {

?>
        <h3><?php _e('MontyPay Payment Gateway - S2S Settings', 'woocommerce-gateway-wpgfull'); ?></h3>
        <p><?php _e('MontyPay Payment Gateway. The plugin works by opened checkout page, and then sending the details to payment system for verification.', 'woocommerce-gateway-wpgfull'); ?></p>
        <input type="hidden" name="" id="wc_api_url" value="<?php echo add_query_arg('wc-api', 'wc_set_picture', home_url('/')); ?>">
        <table class="form-table">
            <?php
            $this->generate_settings_html();
            ?>
            <p>
                <strong><?php _e('Callback Url: ') ?></strong><?php echo add_query_arg('wc-api', 'wc_web_payment_gateway', home_url('/')); ?>
            </p>
        </table>


<?php
    }
//-----------------------------------------------------------------------------------------------------------------------------------------

    function init_form_fields() {
        $this->form_fields = include(MONTY_WOO_PLUGIN_PATH . 'includes/admin/payment.php' );

        unset($this->form_fields['merchant_identifier']);
        unset($this->form_fields['method']);
        unset($this->form_fields['connector']);
        unset($this->form_fields['settlement_account']);
        unset($this->form_fields['additional_payment_logos']);



    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Process the payment and return the result.
     * 
     * @param int $orderId
     * @return array
     */
    public function process_payment($orderId) {

        

        $curlData = $this->montypay_getPayLoadData($orderId, "S2S");


        // wc_add_notice(  json_encode($curlData), 'error' );
		// return;
        // $gateway = mfFilterInputField('mf_gateway', 'POST') ?? 'monty';

        // $configId = mfFilterInputField('mfData', 'POST');
        $response     = $this->mf->getInvoiceURL($curlData, $orderId);
        // update_post_meta($orderId, 'InvoiceId', $data['invoiceId']);
        
        
        file_put_contents('./wp-content/plugins/montypay-payment-gateway/includes/log_callback.log', json_encode($response), FILE_APPEND);

        if($response['result'] != 'ERROR' && $response['result'] == 'REDIRECT' && $response['status'] == 'REDIRECT'){
            // session_start();
            
            // $_SESSION['body'] = $response['body'];
            // $_SESSION['url'] = $response['redirect_url'];
            // $_SESSION['method'] = $response['redirect_method'];
    
            // $redirect = WP_PLUGIN_URL . "/montypay-payment-gateway/redirects/awaiting_3d_secure.php";
            // $redirect = plugin_dir_url(__FILE__) . 'redirects/awaiting_3d_secure.php';

            $redirect = add_query_arg(array(
                'awaiting_3d_secure' => '1',
                'body' => $response['body'],
                'method' => $response['redirect_method'],
                'url' => $response['redirect_url'],

            ), home_url('index.php'));

            return array(
                'result'   => 'success',
                'redirect' => $redirect,
            );
        }elseif($response['result'] == 'REDIRECT' && $response['status'] == '3DS'){
            // new
            // session_start();
            
            // $_SESSION['body'] = $response['body'];
            // $_SESSION['url'] = $response['redirect_url'];
            // $_SESSION['method'] = $response['redirect_method'];

            // $redirect = WP_PLUGIN_URL . "/montypay-payment-gateway/redirects/awaiting_3d_secure.php";
            $redirect = add_query_arg(array(
                'awaiting_3d_secure' => '1',
                'body' => $response['body'],
                'method' => $response['redirect_method'],
                'url' => $response['redirect_url'],

            ), home_url('index.php'));

            return array(
                'result'   => 'success',
                'redirect' => $redirect,
            );
            
            
        }elseif($response['result'] != 'ERROR' && $response['result'] == 'SUCCESS' && $response['status'] == 'SETTLED'){
            return array(
                'result'   => 'success',
                'redirect' => $response['redirect_url'],
            );
        }elseif($response['result'] != 'ERROR' && $response['result'] == 'DECLINED' && $response['status'] == 'DECLINED'){
            wc_add_notice($response['decline_reason'], 'error' );
			return;
        }else{
            file_put_contents('./wp-content/plugins/montypay-payment-gateway/includes/log_callback_stripe.log', json_encode($response), FILE_APPEND);

            $errors = '';
            foreach($response['errors'] as $value){
                $errors .= $value->error_message.'<br>';
            }
            wc_add_notice(  $response['error_message'].'<br>'.$errors, 'error' );
			return;
        }
        
    }

    public function get_3ds_verification_url($url, $method, $body) {
        // Construct the URL for 3DS verification
        $url = 'https://3ds-authentication.com/verify'; // Replace with your actual 3DS URL

        // Assuming you have query parameters for the verification
        $query_params = array(
            'transaction_id' => $order_id,
            'token'          => 'abcdef', // Replace with your actual token
        );

        // Construct the query string
        $query_string = http_build_query($query_params);

        // Assuming method is POST
        $method = 'POST';

        // Set your request body
        $body = '...'; // Construct your request body here

        // Create the cURL request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute cURL request and get the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            // Handle error
            return false;
        }

        // Close cURL session
        curl_close($ch);

        // Return the 3DS verification URL
        return $response;
    }

    // ... Other methods ...

    public function run_script_once() {
            echo '<script>alert("hello")</script>';
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    function payment_fields_s2s() {

            include(MONTY_WOO_PLUGIN_PATH . 'templates/paymentFields.php');
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Return the gateway's title.
     *
     * @return string
     */
    public function get_title() {
        // $this->setGateways();
        if ($this->listOptions === 'multigateways' && $this->count == 1 && $this->isSingleView === 'yes') {
            // option new design 
            if (isset($this->newDesign) && $this->newDesign == 'yes') {
                return ($this->lang == 'ar') ? $this->gateways['all'][0]->PaymentMethodAr : $this->gateways['all'][0]->PaymentMethodEn;
            } else {
                return ($this->lang == 'ar') ? $this->gateways[0]->PaymentMethodAr : $this->gateways[0]->PaymentMethodEn;
            }
        } else {
            return apply_filters('woocommerce_gateway_title', $this->title, $this->id);
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Return the gateway's icon.
     *
     * @return string
     */
    public function get_icon() {

            if($this->countryMode == 'jordan'){
                $icon = '<img src="'.WP_PLUGIN_URL.'/montypay-payment-gateway/assets/images/jordan_logos.png" class="jordan_logos" alt="" />';
            }elseif($this->countryMode == 'bahrain'){
                $icon = '<img src="'.WP_PLUGIN_URL.'/montypay-payment-gateway/assets/images/bahrain_logos.png" class="bahrain_logos" alt="" />';

            }elseif($this->countryMode == 'nigeria'){
                $icon = '<img src="'.WP_PLUGIN_URL.'/montypay-payment-gateway/assets/images/nigeria_logos.png" class="nigeria_logos" alt="" />';
            }else{
                $icon = '<img src="'.WP_PLUGIN_URL.'/montypay-payment-gateway/assets/images/master-visa-exp.png" class="method_icon" alt="" />';
            }
        // } 

        return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    protected function setGateways() {
        if ($this->listOptions === 'monty' || count($this->gateways) != 0) {
            return;
        }


        if (!is_ajax() || !isset($_SERVER['HTTP_REFERER']) || stripos($_SERVER['HTTP_REFERER'], get_permalink(wc_get_page_id('checkout'))) !== 0) {
            return;
        }

        // option new design 
        try {
            if (isset($this->newDesign) && $this->newDesign == 'yes') {
//                $totals = WC()->cart->total;
//                $this->gateways = $this->mf->getPaymentMethodsForDisplay($totals, get_woocommerce_currency());

                $totals                = WC()->cart->get_totals();
                $this->totalAmount     = $totals['total'];
                $this->appleRegistered = ($this->registerApplePay == 'yes') ? true : false;

                // $this->gateways = $this->mf->getPaymentMethodsForDisplay($this->totalAmount, get_woocommerce_currency(), $this->appleRegistered);
                // $this->count    = count($this->gateways['all']);
            } else {
                $gateways = $this->mf->getCachedPaymentMethods();

                $embedOptions = get_option('woocommerce_wc_gateway_montypay_wallets_settings');

                $this->gateways = (isset($embedOptions) && $embedOptions['enabled'] == 'yes') ? $gateways['cards'] : $gateways['all'];
                $this->count    = count($this->gateways);
            }
        } catch (Exception $ex) {
            $this->mfError = $ex->getMessage();
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Don't enable this payment, if there is no API key
     * 
     * @param type $key
     * @param type $value
     * 
     * @return string
     */
    public function validate_enabled_field($key, $value) {
        $enabled = is_null($value) ? 'no' : 'yes';

        if ($enabled == 'yes') {

            //don't enable if there is no API key
            // $apiKey = $this->get_field_value('apiKey', $this->form_fields['apiKey']);
            $merchant_key = $this->get_field_value('merchant_key', $this->form_fields['merchant_key']);
            $merchant_password = $this->get_field_value('merchant_password', $this->form_fields['merchant_password']);

            if (!$merchant_key || !$merchant_password) {
                WC_Admin_Settings::add_error(__('You should add the Merchnat key and password first, to enable this payment method', 'monty-woocommerce'));
                $enabled = 'no';
            } else {
                // $countryMode      = $this->get_field_value('countryMode', $this->form_fields['countryMode']);
                // $testMode         = $this->get_field_value('testMode', $this->form_fields['testMode']);
                // $this->monty = new PaymentMontyApiV2($apiKey, $countryMode, ($testMode === 'yes'), $this->pluginlog);
                try {
                    // $this->monty->getVendorGateways();
                } catch (Exception $ex) {
                    WC_Admin_Settings::add_error(__($ex->getMessage(), 'monty-woocommerce'));
                    $enabled = 'no';
                }
            }

            //don't enable if hosted if S2S or Wallets are enabled 
            // $hostedOptions = get_option('woocommerce_wc_gateway_montypay_hosted_settings');
            // if (isset($hostedOptions['enabled']) && $hostedOptions['enabled'] == 'yes') {
            //     WC_Admin_Settings::add_error(__('You should disable the hosted checkout option in the "MontyPay - Hosted" payment Settings first, to enable this payment method', 'monty-woocommerce'));
            //     $enabled = 'no';
            // }
        }

        return $enabled;
    }


//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Disable the wallets if the new design enabled
     * 
     * @param type $key
     * @param type $value
     * 
     * @return string
     */
    public function validate_newDesign_field($key, $value) {
        $active = is_null($value) ? 'no' : 'yes';

        if ($active == 'yes') {
            $embedOptions = get_option('woocommerce_wc_gateway_montypay_wallets_settings');

            $enableFieldValue = mfFilterInputField($this->get_field_key('enabled'), 'POST'); //get_field_value without validation to avoid duplicate error message
            $apiKey           = $this->get_field_value('apiKey', $this->form_fields['apiKey']); //don't disable if there is no API key
            if ($apiKey && $enableFieldValue && isset($embedOptions['enabled']) && $embedOptions['enabled'] == 'yes') {
                $embedOptions['enabled'] = 'no';
                update_option('woocommerce_wc_gateway_montypay_wallets_settings', apply_filters('woocommerce_settings_api_sanitized_fields_' . 'monty_wallets', $embedOptions), 'yes');
            }
        }

        return $active;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    // /**
    //  * Keep register Apple Pay value
    //  * 
    //  * @param type $key
    //  * @param type $value
    //  * 
    //  * @return string
    //  */
    // public function validate_registerApplePay_field($key, $value) {
    //     $active = is_null($value) ? 'no' : 'yes';
    //     if ($active == 'no' || $this->get_field_value('enabled', $this->form_fields['enabled']) == 'no')
    //         return $active;


    //     if ($this->get_field_value('listOptions', $this->form_fields['listOptions']) == 'monty' ||
    //             $this->get_field_value('newDesign', $this->form_fields['newDesign']) == 'no'
    //     ) {
    //         $active = 'no';
    //         WC_Admin_Settings::add_error(__('Please make sure to select New design and List all gateway option to enable Apple Pay Wallets.', 'monty-woocommerce'));
    //         return $active;
    //     }

    //     try {
    //         $data = $this->monty->registerApplePayDomain(get_site_url());
    //         if ($data->Message == 'OK') {
    //             return 'yes';
    //         }
    //         WC_Admin_Settings::add_error($data->Message);
    //         return 'no';
    //     } catch (Exception $ex) {
    //         WC_Admin_Settings::add_error($ex->getMessage());
    //         return 'no';
    //     }
    // }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Return whether or not this gateway still requires setup to function.
     *
     * When this gateway is toggled on via AJAX, if this returns true a
     * redirect will occur to the settings page instead.
     * @since 3.4.0
     *
     * @return bool
     */
    public function needs_setup() {

        if (empty($this->apiKey)) {
            return true;
        }

        $embedOptions = get_option('woocommerce_wc_gateway_montypay_wallets_settings');
        if (isset($this->newDesign) && $this->newDesign == 'yes' && isset($embedOptions['enabled']) && $embedOptions['enabled'] == 'yes') {
            return true;
        }
        return false;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
}
