<?php

if (!defined('ABSPATH')) {
    exit;
}
require_once MONTY_WOO_PLUGIN_PATH . 'includes/libraries/MontyPayLibrary.php';

if (!class_exists('WC_Gateway_MontyPay')) {
    include_once( 'class-wc-gateway-montypay.php' );
}

/**
 * Monty_wallets Payment Gateway class.
 *
 * Extended by individual payment gateways to handle payments.
 *
 * @class       WC_Gateway_Monty_wallets
 * @extends     WC_Payment_Gateway
 */
class WC_Gateway_MontyPay_hosted extends WC_Gateway_MontyPay {

    protected $code;

    /**
     * Constructor
     */
    public function __construct() {
        $this->code               = 'hosted';
        $this->method_description = __('MontyPay Hosted Payment.', 'monty-woocommerce');
        $this->method_title       = __('MontyPay', 'monty-woocommerce');

        parent::__construct();

        if ($this->countryMode == "nigeria"){
            if(in_array("a2a-transfer",$this->method) && count($this->method) == 1){
                $this->title = get_translation('Pay with Bank Transfer');
            }elseif(in_array("a2a-transfer",$this->method) && count($this->method) > 1){
                $this->title = get_translation('Pay with Card / Bank Transfer');
            }else{
                $this->title = get_translation('Pay with Card');
            }

        }else{
            $this->title = get_translation('Pay with Card');

        }

        $this->has_fields = true;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

function admin_options()
    {
?>
        <h3><?php _e('MontyPay Payment Gateway - Hosted Payment Settings', 'woocommerce-gateway-wpgfull'); ?></h3>
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

    /**
     * Initialize Gateway Settings Form Fields.
     */
    function init_form_fields() {
        $this->form_fields = include(MONTY_WOO_PLUGIN_PATH . 'includes/admin/payment.php' );

        //used in wallets and need to be read from hosted
        unset($this->form_fields['merchant_identifier']);
        unset($this->form_fields['settlement_account']);
        // unset($this->form_fields['method']);
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

        // $class = new PaymentMontyApiS2S

        $PaymentMontyApiS2S = new PaymentMontyApiS2S($this->apiKey, $this->countryMode, ($this->testMode === 'yes'));


        $curlData = $this->montypay_getPayLoadData($orderId, "hosted");

        // $sessionId = mfFilterInputField('mfData', 'POST');

        $response =  $PaymentMontyApiS2S->getHostedCheckoutURL($curlData, $orderId);

        // update_post_meta($orderId, 'InvoiceId', $data['invoiceId']);
        // file_put_contents('./log_hosted_lb_fields.log', json_encode($response), FILE_APPEND);


        if(isset($response['redirect_url']) && $response['redirect_url'] ){
            return array(
                'result'   => 'success',
                'redirect' => $response['redirect_url'],
            );
        }else{
            // file_put_contents('./log_process_payment.log', json_encode($response['errors']), FILE_APPEND);

            $errors = '';
            foreach($response['errors'] as $value){
                $errors .= $value->error_message.'<br>';
            }
            wc_add_notice(  $response['error_message'].'<br>'.$errors, 'error' );
			return;
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    function payment_fields_hosted() {

        // include_once(MONTY_WOO_PLUGIN_PATH . 'templates/paymentFieldsHosted.php');
        include(MONTY_WOO_PLUGIN_PATH . '/templates/paymentFieldsHosted.php');
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Return the gateway's title.
     *
     * @return string
     */
    public function get_title() {

        return apply_filters('woocommerce_gateway_title', $this->title, $this->id);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Return the gateway's icon.
     *
     * @return string
     */
    public function get_icon() {

        // echo json_encode($this->method);
        // exit;
        if(in_array("a2a-transfer",$this->method) && count($this->method) == 1){
            // $image = "method-a2a-transfer.png";
            $icon = '<img src="'.WP_PLUGIN_URL.'/montypay-payment-gateway/assets/images/method-a2a-transfer.png" class="method_icon" alt="" />';

        }elseif(in_array("a2a-transfer",$this->method) && count($this->method) > 1){
            $icon = '<img src="'.WP_PLUGIN_URL.'/montypay-payment-gateway/assets/images/master-visa-bank.png" class="method_icon" alt="" />';

        }else{
            // $image = "master-visa-exp.png";
            $icon = '<img src="'.WP_PLUGIN_URL.'/montypay-payment-gateway/assets/images/master-visa-exp.png" class="method_icon" alt="" />';

        }
        
        // $icon = '<img src="'.WP_PLUGIN_URL.'/montypay-payment-gateway/assets/images/method-a2a-transfer.png" class="method_icon" alt="" />';
        return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Don't enable this payment, if there is no API key in "Monty - Cards" payment settings or newDesign is enabled
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
            $v2Options = get_option('woocommerce_wc_gateway_montypay_hosted_settings');
            if (empty($v2Options['merchant_key']) || empty($v2Options['merchant_password'])) {
                WC_Admin_Settings::add_error(__('You should add the Merchant key and password in the "Monty - Wallets" payment Settings first, to enable this payment method', 'monty-woocommerce'));
                $enabled = 'no';
            } else {
                // $monty = new PaymentMontyApiV2($v2Options['apiKey'], $v2Options['countryMode'], ($v2Options['testMode'] === 'yes'));
                try {
                    // $monty->getVendorGateways();
                } catch (Exception $ex) {
                    WC_Admin_Settings::add_error(__($ex->getMessage(), 'monty-woocommerce'));
                    $enabled = 'no';
                }
            }

            //don't enable if hosted if S2S or Wallets are enabled 
            $s2sOptions = get_option('woocommerce_wc_gateway_montypay_s2s_settings');
            $walletsOptions = get_option('woocommerce_wc_gateway_montypay_wallets_settings');
            $afsOptions = get_option('woocommerce_wc_gateway_montypay_benefit_settings');


            // if ((isset($s2sOptions['enabled']) && $s2sOptions['enabled'] == 'yes') || (isset($walletsOptions['enabled']) && $walletsOptions['enabled'] == 'yes') || (isset($afsOptions['enabled']) && $afsOptions['enabled'] == 'yes')) {
            //     WC_Admin_Settings::add_error(__('You should disable the S2S, Wallets & benefit options in the payment Settings first, to enable this payment method', 'monty-woocommerce'));
            //     $enabled = 'no';
            // }
        }

        return $enabled;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Return whether or not this gateway still requires setup to function.
     *
     * When this gateway is toggled on via AJAX, if this returns true a
     * redirect will occur to the settings page instead.
     *
     * @since 3.4.0
     * @return bool
     */
    public function needs_setup() {

        $v2Options = get_option('woocommerce_wc_gateway_montypay_hosted_settings');
        if (empty($v2Options['apiKey'])) {
            return true;
        }

        if (isset($v2Options['enabled']) && $v2Options['enabled'] == 'yes' && isset($v2Options['newDesign']) && $v2Options['newDesign'] == 'yes') {
            return true;
        }

        return false;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------    
}
