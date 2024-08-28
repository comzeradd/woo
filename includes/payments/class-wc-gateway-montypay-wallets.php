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
class WC_Gateway_MontyPay_Wallets extends WC_Gateway_MontyPay {

    protected $code;

    /**
     * Constructor
     */
    public function __construct() {
        $this->code               = 'wallets';
        $this->method_description = __('MontyPay Wallets payment.', 'monty-woocommerce');
        $this->method_title       = __('MontyPay', 'monty-woocommerce');

        parent::__construct();

        $this->title       = get_translation('Wallets');


        $this->has_fields = true;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

function admin_options()
    {
?>
        <h3><?php _e('MontyPay Payment Gateway - Wallets Settings', 'woocommerce-gateway-wpgfull'); ?></h3>
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

        //used in wallets and need to be read from v2
        unset($this->form_fields['connector']);
        unset($this->form_fields['additional_payment_logos']);


        // unset($this->form_fields['apiKey']);
        // unset($this->form_fields['testMode']);
        // unset($this->form_fields['debug']);
        // unset($this->form_fields['saveCard']);
        // unset($this->form_fields['orderStatus']);
        // unset($this->form_fields['success_url']);
        // unset($this->form_fields['fail_url']);
        // unset($this->form_fields['webhookSecretKey']);
        // unset($this->form_fields['invoiceItems']);
        // unset($this->form_fields['registerApplePay']);

        // //not used in wallets
        // unset($this->form_fields['listOptions']);
        // unset($this->form_fields['isSingleView']);
        // unset($this->form_fields['newDesign']);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Process the payment and return the result.
     * 
     * @param int $orderId
     * @return array
     */
    public function process_payment($orderId) {

        $curlData = $this->montypay_getPayLoadData($orderId, "wallets");

        // $sessionId = mfFilterInputField('mfData', 'POST');

        $response = $this->mf->getHostedCheckoutURL($curlData, $orderId);

        // update_post_meta($orderId, 'InvoiceId', $data['invoiceId']);

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

    function payment_fields_wallets() {

        include(MONTY_WOO_PLUGIN_PATH . 'templates/paymentFieldsWallets.php');
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
        
        $icon = '<img src="'.WP_PLUGIN_URL.'/montypay-payment-gateway/assets/images/applepaylogo.png" class="wallets_icon" alt="" />';
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
            $v2Options = get_option('woocommerce_wc_gateway_montypay_wallets_settings');
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
            $hostedOptions = get_option('woocommerce_wc_gateway_montypay_hosted_settings');
            if (isset($hostedOptions['enabled']) && $hostedOptions['enabled'] == 'yes') {
                WC_Admin_Settings::add_error(__('You should disable the hosted checkout option in the "MontyPay - Hosted" payment Settings first, to enable this payment method', 'monty-woocommerce'));
                $enabled = 'no';
            }
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

        $v2Options = get_option('woocommerce_monty_v2_settings');
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
