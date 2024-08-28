<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once MONTY_WOO_PLUGIN_PATH . 'includes/libraries/MontyPayLibrary.php';

/**
 * WC_Gateway_Monty class.
 *
 * handle payments.
 *
 * @extends     WC_Payment_Gateway
 */
class WC_Gateway_MontyPay extends WC_Payment_Gateway {

//-----------------------------------------------------------------------------------------------------------------------------

    public $enabled, $title, $description, $countryMode, $settlement_account, $connector, $testMode, $apiKey,$method, $merchant_key, $merchant_password, $listOptions, $registerApplePay, $orderStatus, $success_url, $fail_url, $debug, $saveCard, $icon, $isSingleView, $webhookSecretKey;
    public $mfCountries = [];

    public $currencies_3dotexponent = ['BHD', 'JOD', 'KWD', 'OMR', 'TND'];
    public $currencies_noexponent = [
        //'CLP', 
        'VND', 
        'ISK', 
        'UGX', 
        //'KRW', 
        //'JPY'
    ];

    protected $loggerObj;

    /**
     * If $loggerObj is set as a logger object, you should set this var with the function name that will be used in the debugging.
     *
     * @var string
     */
    protected $loggerFunc;
    

    /**
     * Constructor for your payment class
     *
     * @access public
     * @return void
     */
    public function __construct() {

        $this->id   = 'wc_gateway_montypay_' . $this->code;
        $this->lang = substr(determine_locale(), 0, 2);

        $this->pluginlog = WC_LOG_DIR . $this->id . '.log';

        //this will appeare in the setting details page. For more customize page you override function admin_options()
        $this->supports = array(
            'products',
//            'refunds',
        );

        

        //Get setting values
        $this->init_settings();

        //enabled, title, description, countryMode, testMode, apiKey, listOptions, orderStatus, success_url, fail_url, debug, icon, 
        foreach ($this->settings as $key => $val) {
            $this->$key = $val;
        }

        // $v2Options = get_option('woocommerce_monty_v2_settings');

        // if ($v2Options) {
        //     $this->apiKey           = isset($v2Options['apiKey']) ? $v2Options['apiKey'] : '';
        //     $this->merchant_key     = isset($v2Options['merchant_key']) ? $v2Options['merchant_key'] : '';
        //     $this->merchant_password= isset($v2Options['merchant_password']) ? $v2Options['merchant_password'] : '';
        //     $this->apiKey           = isset($v2Options['apiKey']) ? $v2Options['apiKey'] : '';
        //     $this->countryMode      = isset($v2Options['countryMode']) ? $v2Options['countryMode'] : 'KWT';
        //     $this->testMode         = isset($v2Options['testMode']) ? $v2Options['testMode'] : 'no';
        //     $this->debug            = isset($v2Options['debug']) ? $v2Options['debug'] : 'yes';
        //     $this->saveCard         = isset($v2Options['saveCard']) ? $v2Options['saveCard'] : 'no';
        //     $this->orderStatus      = isset($v2Options['orderStatus']) ? $v2Options['orderStatus'] : 'processing';
        //     $this->success_url      = isset($v2Options['success_url']) ? $v2Options['success_url'] : '';
        //     $this->fail_url         = isset($v2Options['fail_url']) ? $v2Options['fail_url'] : '';
        //     $this->webhookSecretKey = isset($v2Options['webhookSecretKey']) ? $v2Options['webhookSecretKey'] : '';
        //     $this->invoiceItems     = isset($v2Options['invoiceItems']) ? $v2Options['invoiceItems'] : 'yes';
        //     $this->registerApplePay = isset($v2Options['registerApplePay']) ? $v2Options['registerApplePay'] : 'no';
        // }

        if ('yes' === $this->debug) {
            $this->mf = new PaymentMontyApiS2S($this->apiKey, $this->countryMode, ($this->testMode === 'yes'), $this->pluginlog);
        } else {
            $this->mf = new PaymentMontyApiS2S($this->apiKey, $this->countryMode, ($this->testMode === 'yes'));
        }



        //Create plugin admin fields
        $this->init_form_fields();

        //save admin setting action
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        add_action('woocommerce_api_wc_web_payment_gateway', array($this, 'check_ipn_response'));

    }

//------------------------------------------CallBack notification function-----------------------------------------------------


    function check_ipn_response()
    {
        global $woocommerce;

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            exit;
        }

        $response = '';
        foreach($_POST as $key => $val){
            $response .= $key . ' : ' . $val .PHP_EOL;
        }
        file_put_contents('./wp-content/plugins/montypay-payment-gateway/includes/log_callback.log', $response, FILE_APPEND);
		
        if(isset($_POST['order_id']) && $_POST['order_id']){
            $order_id = $_POST['order_id'];
        }
        if(isset($_POST['order_number']) && $_POST['order_number']){
            $order_id = $_POST['order_number'];
        }
        $order = new WC_Order($order_id);
        $order->set_transaction_id($_POST['trans_id']);

        if ($order->get_status() == 'pending' || $order->get_status() == 'waiting' || $order->get_status() == 'failed' || $order->get_status() == '' || $order->get_status() == 'on-hold') {
            if (($_POST['result'] == 'SUCCESS' && $_POST['action'] == 'SALE' && $_POST['status'] == 'SETTLED') || ($_POST['type'] == 'sale' && $_POST['status'] == 'success')) {
                //successful purchase
                $order->payment_complete($order_id);
                $woocommerce->cart->empty_cart();
                $order->update_status('processing', 'Payment successfully paid'); //completed or processing
				exit;
            }

            if ($_POST['status'] == 'waiting' && $_POST['action'] == 'sale') {
                //waiting purchase
                $order->update_status('on-hold', __('On hold', 'woocommerce'));
                exit;
            }

            if ($_POST['status'] == 'REDIRECT' && $_POST['action'] == 'SALE') {
                //waiting purchase
                $order->update_status('on-hold', __('On hold', 'woocommerce'));
                exit;
            }

            if ($_POST['status'] == 'fail' && $_POST['action'] == 'sale') {
                //failed purchase
                // $woocommerce->cart->empty_cart();
                $order->update_status('failed', $_POST['reason']);
                exit;
            }

            if ($_POST['status'] == 'fail' && $_POST['type'] == 'sale') {
                //failed purchase
                // $woocommerce->cart->empty_cart();
                $order->update_status('failed', $_POST['reason']);
                exit;
            }
            
            
            if ($_POST['status'] == 'DECLINED' && $_POST['action'] == 'SALE') {
                //failed purchase
                // $woocommerce->cart->empty_cart();
                $order->update_status('failed', $_POST['decline_reason']);
                exit;
            }
        }


        if ($order->get_status() == 'processing') {
            if ($_POST['status'] == 'success' && $_POST['action'] == 'refund') {
                $order->update_status('refunded', __('Refunded', 'woocommerce'));
                $order->add_order_note('Refund confirmed by the payment system');
                exit;
            }
            if ($_POST['status'] == 'fail' && $_POST['action'] == 'refund') {
                $order->update_status('failed', $_POST['reason']);
                exit;
            }
        }
    }

    public function log($msg) {

        if (!$this->loggerObj) {
            return;
        }
        if (is_string($this->loggerObj)) {
            error_log(PHP_EOL . date('d.m.Y h:i:s') . ' - ' . $msg, 3, $this->loggerObj);
        } elseif (method_exists($this->loggerObj, $this->loggerFunc)) {
            $this->loggerObj->{$this->loggerFunc}($msg);
        }
    }

    





//-----------------------------------------------------------------------------------------------------------------------------






    /**
     * Define Settings Form Fields
     * @return void 
     */
    function init_form_fields() {
        $this->form_fields = include(MONTY_WOO_PLUGIN_PATH . 'includes/admin/payment.php' );
    }

//-----------------------------------------------------------------------------------------------------------------------------
function montypay_getPayLoadData($order_id, $payment_method)
{
    
    $order = new WC_Order($order_id);

    if(get_locale() == 'ar'){
        $customer = array(
            'email' => $order->get_billing_email(),
        );
    }else{
        $customer = array(
            'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'email' => $order->get_billing_email(),
        );
    }
    

    if($this->countryMode == 'lebanon' && $this->connector == 'blom'){
        $billing_address = array(
            'country' => $order->get_billing_country() ? $order->get_billing_country() : 'NA',
            'city' => $order->get_billing_city() ? $order->get_billing_city() : 'NA',
            'address' => $order->get_billing_address_1() ? substr($order->get_billing_address_1(), 0 , 19) : 'NA',
            'zip' => $order->get_billing_postcode() ? $order->get_billing_postcode() : 'NA',
            'phone' => $order->get_billing_phone() ? $order->get_billing_phone() : 'NA',
        );
    }else{
        $billing_address = array(
            'country' => $order->get_billing_country() ? $order->get_billing_country() : 'NA',
            'state' => $order->get_billing_state() ? $order->get_billing_state() : 'NA',
            'city' => $order->get_billing_city() ? $order->get_billing_city() : 'NA',
            'address' => $order->get_billing_address_1() ? $order->get_billing_address_1() : 'NA',
            'zip' => $order->get_billing_postcode() ? $order->get_billing_postcode() : 'NA',
            'phone' => $order->get_billing_phone() ? $order->get_billing_phone() : 'NA',
        );
    }

    if($payment_method == "wallets" && $this->countryMode == 'jordan' && in_array('applepay', $this->method) ){
        if($this->settlement_account == 'usd' && get_woocommerce_currency() != 'JOD'){
            $amount = number_format($order->get_total(), 2, '.', '');
            if (in_array(get_woocommerce_currency(), $this->currencies_noexponent)) {
                $amount = number_format($order->get_total(), 0, '.', '');
            }elseif (in_array(get_woocommerce_currency(), $this->currencies_3dotexponent)) {
                    $amount = number_format($order->get_total(), 3, '.', '');
            }

            $ch = curl_init();
  
            $url = "https://api.currencybeacon.com/v1/convert";
            $dataArray = [
                'api_key' => '7191a3b41f69c04aa04012d3a2d6212e',
                'from' => get_woocommerce_currency(),
                'to' => 'JOD',
                'amount' => $amount,
        
            ];
        
            $data = http_build_query($dataArray);
        
            $getUrl = $url."?".$data;
        
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_URL, $getUrl);
            curl_setopt($ch, CURLOPT_TIMEOUT, 80);
            
            $response = curl_exec($ch);
                
            if(curl_error($ch)){
                echo 'Request Error:' . curl_error($ch);
            }else{
                $result = json_decode($response);
                $currency = 'JOD';
                $value = round($result->response->value, 2);
                $amount = number_format($value, 3, '.', '');
            }
            
            curl_close($ch);




        }else{
            $amount = number_format($order->get_total(), 2, '.', '');
            if (in_array(get_woocommerce_currency(), $this->currencies_noexponent)) {
                $amount = number_format($order->get_total(), 0, '.', '');
            }elseif (in_array(get_woocommerce_currency(), $this->currencies_3dotexponent)) {
                    $amount = number_format($order->get_total(), 3, '.', '');
            }
            
            $value = round($amount, 2);
            $amount = number_format($value, 3, '.', '');
            $currency = get_woocommerce_currency();
        }
    }else{
        $amount = number_format($order->get_total(), 2, '.', '');
        if (in_array(get_woocommerce_currency(), $this->currencies_noexponent)) {
            $amount = number_format($order->get_total(), 0, '.', '');
        }elseif (in_array(get_woocommerce_currency(), $this->currencies_3dotexponent)) {
            // $amount = number_format($order->get_total(), 3, '.', '');

            // if(get_woocommerce_currency() == 'JOD'){
                $value = round($order->get_total(), 2);
                $amount = number_format($value, 3, '.', '');

            // }
            //else{

            // }
        }
        $currency = get_woocommerce_currency();
    }


    $order_json = array(
        'number' => "$order_id",
        'description' => __('Payment Order # ', 'woocommerce') . $order_id . __(' in the store ', 'woocommerce') . home_url('/'),
        'amount' => $amount, //may troubles
        'currency' => $currency,
    );

    

    // $methods = $this->method; //may error

    // if($methods == null){
    //     $methods = array();
    // }

    $card_number = str_replace(" ", "",$_POST['card_number']);
    $CVV = $_POST['card_cvc'];

    $card_exp = $_POST['card_exp'];
    if ($card_exp) {
        $exp_array = explode(' / ', $_POST['card_exp']);
        $exp_month = str_replace(" ", "",$exp_array[0]);
        $exp_year = str_replace(" ", "",'20'.$exp_array[1]);
    } else {
        $exp_month = '';
        $exp_year = '';
    }

    if($payment_method == "S2S"){
        file_put_contents('./wp-content/plugins/montypay-payment-gateway/includes/log_callback.log',  $this->get_montypay_return_url($order), FILE_APPEND);

        return [
            'action'       => "SALE",
            'client_key'       => $this->merchant_key,
            'order_id' => $order_id,
            'order_amount'      => $amount,
            'order_currency'        => $order_json['currency'],
            'order_description'           => $order_json['description'],
            'card_number'  => $card_number,
            'card_exp_month'     => $exp_month,
            'card_exp_year'           => $exp_year,
            'card_cvv2'  => $CVV,
            'payer_first_name'    => $order->get_billing_first_name(),
            'payer_last_name'   => $order->get_billing_last_name(),
            'payer_address'         => $billing_address['address'],
            'payer_country'         => $billing_address['country'],
            'payer_state'    => $billing_address['state'],
            'payer_city'  => $billing_address['city'],
            'payer_zip'     => $billing_address['zip'],
            'payer_email'       => $customer['email'],
            'payer_phone'       => $billing_address['phone'],
            'payer_ip'       => '192.0.0.12', //$this->get_ip_address(),
            'term_url_3ds'       => $this->get_montypay_return_url($order), //$this->get_return_url($order),
            'hash'       => $this->get_hash('F1', 0, 0, $currency, $customer['email'], $card_number),
        ];


        //$order->get_view_order_url(),
    }
    elseif($payment_method == "wallets"){

        $request = [
            'merchant_key' => $this->merchant_key,
            'operation'    => 'purchase', //m subs purchase
            'methods'      => array("applepay"),
            'order'        => $order_json,
            'customer'     => $customer,
            'billing_address' => $billing_address,
            'success_url' => $this->get_montypay_return_url($order), //$this->get_return_url($order),
            'cancel_url'   => $order->get_view_order_url(), //
            'hash'         => $this->get_hash('F_hosted', $order_id, $amount, $currency, $customer['email'], $card_number),
        ];

        file_put_contents('./wp-content/plugins/montypay-payment-gateway/includes/log_callback.log', $request, FILE_APPEND);

        return $request;
    }
    elseif($payment_method == "stripejs"){

        $request = [
            'merchant_key' => $this->merchant_key,
            'operation'    => 'purchase', //m subs purchase
            'methods'      => array("stripe-js"),
            'order'        => $order_json,
            'customer'     => $customer,
            'billing_address' => $billing_address,
            'success_url' => $this->get_montypay_return_url($order), //$this->get_return_url($order),
            'cancel_url'   => $order->get_view_order_url(), //
            'hash'         => $this->get_hash('F_hosted', $order_id, $amount, $currency, $customer['email'], $card_number),
        ];

        file_put_contents('./wp-content/plugins/montypay-payment-gateway/includes/log_callback.log', $request, FILE_APPEND);

        return $request;
    }
    elseif($payment_method == "hosted"){
        $methods = $this->method; //may error
        if($methods == null){
            $methods = array();
        }
        return [
            'merchant_key' => $this->merchant_key,
            'operation'    => 'purchase', //m subs purchase
            'methods'      => $methods,
            'order'        => $order_json,
            'customer'     => $customer,
            'billing_address' => $billing_address,
            'success_url' => $this->get_montypay_return_url($order), //$this->get_return_url($order),
            'cancel_url'   => $order->get_view_order_url(), //
            'hash'         => $this->get_hash('F_hosted', $order_id, $amount, $currency, $customer['email'], $card_number),
        ];
    }elseif($payment_method == "benefit"){
        return [
            'merchant_key' => $this->merchant_key,
            'operation'    => 'purchase', //m subs purchase
            'methods'      => array("afsbenefit"),
            'order'        => $order_json,
            'customer'     => $customer,
            'billing_address' => $billing_address,
            'success_url' => $this->get_montypay_return_url($order), //$this->get_return_url($order),
            'cancel_url'   => $order->get_view_order_url(), //
            'hash'         => $this->get_hash('F_hosted', $order_id, $amount, $currency, $customer['email'], $card_number),
        ];
    }
    
    
}

// new 
public function get_montypay_return_url( $order = null, $id = null ) {
    if ( is_user_logged_in() ) {
        if ( is_object( $order ) ) {
            if ( empty( $id ) ) {
                $id = uniqid();
            }

            $order_id = $order->get_id();

            $args = [
                'utm_nooverride' => '1',
                'order_id'       => $order_id,
            ];

            return wp_sanitize_redirect( esc_url_raw( add_query_arg( $args, $this->get_return_url( $order ) ) ) );
        }

        return wp_sanitize_redirect( esc_url_raw( add_query_arg( [ 'utm_nooverride' => '1' ], $this->get_return_url() ) ) );
    }else{
        if ( is_object( $order ) ) {
            $order_received_url = wc_get_endpoint_url( 'order-received', $order->get_id(), wc_get_checkout_url() );
            $order_received_url = add_query_arg( 'key', $order->get_order_key(), $order_received_url );
            
            return $order_received_url;
        }else{
            return get_home_url();
        }
    }
}

function get_ip_address() {
    // check for shared internet/ISP IP
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && $this->validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }

    // check for IP addresses passing through proxies
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // check if multiple IP addresses exist in the HTTP_X_FORWARDED_FOR header
        $ip_array = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach ($ip_array as $ip) {
            if ($this->validate_ip($ip)) {
                return $ip;
            }
        }
    }

    // fallback to remote address
    return $_SERVER['REMOTE_ADDR'];
}

function validate_ip($ip) {
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return false;
    }
    return true;
}

// new


function get_hash($formula, $order_id, $amount, $currency, $payer_email, $card_number){

    if($formula == 'F1'){
        $hash = md5(strtoupper(strrev($payer_email).$this->merchant_password.strrev(substr($card_number,0,6).substr($card_number,-4))));
    }else{
        $str_to_hash = $order_id . $amount . $currency . __('Payment Order # ', 'woocommerce') . $order_id . __(' in the store ', 'woocommerce') . home_url('/') . $this->merchant_password;
        $hash = sha1(md5(strtoupper($str_to_hash)));    
    }

    return $hash;
}

//-----------------------------------------------------------------------------------------------------------------------------

    public function updatePostMeta($orderId, $data) {

        $orderId->update_meta_data( 'InvoiceId',  $data->InvoiceId);
        $orderId->update_meta_data( 'InvoiceReference',  $data->InvoiceReference);
        $orderId->update_meta_data( 'InvoiceDisplayValue',  $data->InvoiceDisplayValue);

        //focusTransaction
        $orderId->update_meta_data( 'PaymentGateway',  $data->focusTransaction->PaymentGateway);
        $orderId->update_meta_data( 'PaymentId',  $data->focusTransaction->PaymentId);
        $orderId->update_meta_data( 'ReferenceId',  $data->focusTransaction->ReferenceId);
        $orderId->update_meta_data( 'TransactionId',  $data->focusTransaction->TransactionId);
    }

//-----------------------------------------------------------------------------------------------------------------------------

    public function addOrderNote(&$order, $data, $source) {
        $note = "Monty$source Payment Details:<br>";

        $note .= 'InvoiceStatus: ' . $data->InvoiceStatus . '<br>';
        if ($data->InvoiceStatus == 'Failed') {
            $note .= 'InvoiceError: ' . $data->InvoiceError . '<br>';
        }

        $note .= 'InvoiceId: ' . $data->InvoiceId . '<br>';
        $note .= 'InvoiceReference: ' . $data->InvoiceReference . '<br>';
        $note .= 'InvoiceDisplayValue: ' . $data->InvoiceDisplayValue . '<br>';

        //focusTransaction
        $note .= 'PaymentGateway: ' . $data->focusTransaction->PaymentGateway . '<br>';
        $note .= 'PaymentId: ' . $data->focusTransaction->PaymentId . '<br>';
        $note .= 'ReferenceId: ' . $data->focusTransaction->ReferenceId . '<br>';
        $note .= 'TransactionId: ' . $data->focusTransaction->TransactionId . '<br>';

        $order->add_order_note($note);
    }

//-----------------------------------------------------------------------------------------------------------------------------
    public function updateOrderData($orderId, &$order, $status, $data, $source) {
        
        //add notes
        $this->addOrderNote($order, $data, $source);
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Processes and saves options.
     * If there is an error thrown, will continue to save and validate fields, but will leave the erroring field out.
     *
     * @return bool was anything saved?
     */
    public function process_admin_options() {
        if (file_exists(PaymentMontyApiS2S::$pmCachedFile)) {
            unlink(PaymentMontyApiS2S::$pmCachedFile);
        }
        parent::process_admin_options();
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    function payment_fields() {

        echo '<!-- MontyPay version ' . MONTY_WOO_PLUGIN_VERSION . ' -->';

        try {
            // if (!wc_checkout_is_https()) {
            //     throw new Exception(__('MontyPay forces SSL checkout Payment. Your checkout is not secure! Please, contact the site admin to enable SSL and ensure that the server has a valid SSL certificate.', 'monty-woocommerce'));
            // }

            $this->{'payment_fields_' . $this->code}();
        } catch (Exception $ex) {
            $this->mfError = $ex->getMessage();
            include(MONTY_WOO_PLUGIN_PATH . 'templates/error.php');
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function get_parent_payment_fields() {
        parent::payment_fields();
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
}
