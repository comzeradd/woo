<?php

/**
 * Class montypayApiV2 is responsible for handling calling montypay API endpoints.
 * Also, It has necessary library functions that help in providing the correct parameters used endpoints.
 *
 * montypay offers a seamless business experience by offering a technology put together by our tech team. This enables smooth business operations involving sales activity, product invoicing, shipping, and payment processing. montypay invoicing and payment gateway solution trigger your business to greater success at all levels in the new age world of commerce. Leverage your sales and payments at all e-commerce platforms (ERPs, CRMs, CMSs) with transparent and slick applications that are well-integrated into social media and telecom services. For every closing sale click, you make a business function gets done for you, along with generating factual reports and statistics to fine-tune your business plan with no-barrier low-cost.
 * Our technology experts have designed the best GCC E-commerce solutions for the native financial instruments (Debit Cards, Credit Cards, etc.) supporting online sales and payments, for events, shopping, mall, and associated services.
 *
 * Created by montypay http://montypay.com/
 * Developed By montypay.com
 * Date: 03/03/2021
 * Time: 12:00
 *
 * API Documentation on https://montypay.readme.io/docs
 * Library Documentation and Download link on https://montypay.com
 *
 * @author    montypay <montypay.com>
 * @copyright 2021 montypay, All rights reserved
 * @license   GNU General Public License v3.0
 */
class MontyApiV2 {
    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * The URL used to connect to montypay test/live API server
     *
     * @var string
     */
    protected $apiURL = '';

    /**
     * The API Token Key is the authentication which identify a user that is using the app
     * To generate one follow instruction here https://montypay.readme.io/docs/live-token
     *
     * @var string
     */
    protected $apiKey;

    /**
     * This is the file name or the logger object
     * It is used in logging the payment/shipping events to help in debugging and monitor the process and connections.
     *
     * @var string|object
     */
    protected $loggerObj;

    /**
     * If $loggerObj is set as a logger object, you should set this var with the function name that will be used in the debugging.
     *
     * @var string
     */
    protected $loggerFunc;

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Constructor
     * Initiate new montypay API process
     *
     * @param string        $apiKey      The API Token Key is the authentication which identify a user that is using the app. To generate one follow instruction here https://montypay.readme.io/docs/live-token.
     * @param string        $countryMode Select the country mode.
     * @param boolean       $isTest      Set it to false for live mode.
     * @param string|object $loggerObj   This is the file name or the logger object. It is used in logging the payment/shipping events to help in debugging and monitor the process and connections. Leave it null, if you don't want to log the events.
     * @param string        $loggerFunc  If $loggerObj is set as a logger object, you should set this var with the function name that will be used in the debugging.
     */
    public function __construct($countryMode, $isTest = false, $loggerObj = null, $loggerFunc = null) {

        
        $this->apiURL = ($isTest) ? 'https://api.montypay.com' : 'https://api.montypay.com';

        // $this->apiKey     = trim($apiKey);
        $this->loggerObj  = $loggerObj;
        $this->loggerFunc = $loggerFunc;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     *
     * @param string         $url        montypay API endpoint URL
     * @param array          $postFields POST request parameters array. It should be set to null if the request is GET.
     * @param integer|string $orderId    The order id or the payment id of the process, used for the events logging.
     * @param string         $function   The requester function name, used for the events logging.
     *
     * @return object       The response object as the result of a successful calling to the API.
     *
     * @throws Exception    Throw exception if there is any curl/validation error in the montypay API endpoint URL
     */
    public function callAPI($url, $payment_method, $postFields = null, $orderId = null, $function = null) {
        
        //to prevent json_encode adding lots of decimal digits
        ini_set('precision', 14);
        ini_set('serialize_precision', -1);

        $request = isset($postFields) ? 'POST' : 'GET';
        $fields  = json_encode($postFields);

        $msgLog = "Order #$orderId ----- $function";

        if ($function != 'Direct Payment') {
            $this->log("$msgLog - Request: $fields");
        }

        //***************************************
        //call url
        //***************************************
        $card_number = $postFields['card_number'];
        $month = $postFields['card_exp_month'];
        $year = $postFields['card_exp_year'];
        $card_cvc = $postFields['card_cvv2'];
        $order_id = $postFields['order_id'];
        $client_key = $postFields['client_key'];
        $order_amount = $postFields['order_amount'];
        $order_currency = $postFields['order_currency'];
        $order_decsription = $postFields['order_description'];
        $payer_first_name = $postFields['payer_first_name'];
        $payer_last_name = $postFields['payer_last_name'];
        $payer_address = $postFields['payer_address'];
        $payer_country = $postFields['payer_country'];
        $payer_state = $postFields['payer_state'];
        $payer_city = $postFields['payer_city'];
        $payer_zip = $postFields['payer_zip'];
        $payer_email = $postFields['payer_email'];
        $payer_phone = $postFields['payer_phone'];
        $payer_ip = $postFields['payer_ip'];
        $term_url_3ds = $postFields['term_url_3ds'];
        $hash = $postFields['hash'];

        if($payment_method == "S2S"){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "action=SALE&client_key=$client_key&order_id=$order_id&order_amount=$order_amount&order_currency=$order_currency&order_description=$order_decsription&card_number=$card_number&card_exp_month=$month&card_exp_year=$year&card_cvv2=$card_cvc&payer_first_name=$payer_first_name&payer_last_name=$payer_last_name&payer_address=$payer_address&payer_country=$payer_country&payer_state=$payer_state&payer_city=$payer_city&payer_zip=$payer_zip&payer_email=$payer_email&payer_phone=$payer_phone&payer_ip=$payer_ip&term_url_3ds=$term_url_3ds&hash=$hash");
            // curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            $headers = array();
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $res = curl_exec($ch);
            $err = curl_error($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        }elseif($payment_method == "wallets"){
            $getter = curl_init($url); //init curl
            curl_setopt($getter, CURLOPT_POST, 1); //post
            curl_setopt($getter, CURLOPT_POSTFIELDS, $fields); //json
            curl_setopt($getter, CURLOPT_HTTPHEADER, array('Content-Type:application/json')); //header
            curl_setopt($getter, CURLOPT_RETURNTRANSFER, true);

            $res = curl_exec($getter);
            $err = curl_error($getter);
            $httpcode = curl_getinfo($getter, CURLINFO_HTTP_CODE);
        }
        

        $response = json_decode($res, true);


        //example set a local ip to host apitest.montypay.com
        if ($err) {
            $this->log("$msgLog - cURL Error: $err");
            throw new Exception($err);
        }

        if ($httpcode != 200) {
            $this->log("cURL Error - httpcode: $httpcode");
            $this->log("cURL Error - ERROR MSG: $res");
            // return false;
            // $errors = '';
            // foreach($response['errors'] as $value){
            //     $errors .= $value['error_message'].'<br>';
            // }
            // wc_add_notice(  $response['error_message'].'<br>'.$errors, 'error' );
			// exit;
        }

        // wc_add_notice(  $response['error_message'].'<br>'.$order_currency, 'error' );
		// 	exit;
            
        $this->log("$msgLog - Response: $res");

        $json = json_decode((string) $res);

        //***************************************
        //check for errors
        //***************************************

        $error = $this->getAPIError($json, (string) $res);
        if ($error) {
            $this->log("$msgLog - Error: $error");
            throw new Exception($error);
        }

        //***************************************
        //Success
        //***************************************
        return $json;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Handles Endpoint Errors Function
     *
     * @param object|string $json
     * @param string        $res
     *
     * @return string
     */
    protected function getAPIError($json, $res) {

        if (isset($json->IsSuccess) && $json->IsSuccess == true) {
            return '';
        }

        //to avoid blocked IP like:
        //<html>
        //<head><title>403 Forbidden</title></head>
        //<body>
        //<center><h1>403 Forbidden</h1></center><hr><center>Microsoft-Azure-Application-Gateway/v2</center>
        //</body>
        //</html>
        //and, skip apple register <YourDomainName> tag error
        $stripHtmlStr = strip_tags($res);
        if ($res != $stripHtmlStr && !stripos($stripHtmlStr, 'apple-developer-merchantid-domain-association')) {
            return trim(preg_replace('/\s+/', ' ', $stripHtmlStr));
        }

        //Check for the errors
        $err = $this->getJsonErrors($json);
        if ($err) {
            return $err;
        }

        if (!$json) {
            return (!empty($res) ? $res : 'Kindly review your MontyPay admin configuration due to a wrong entry.');
        }

        if (is_string($json)) {
            return $json;
        }

        return '';
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Check for the json (response model) errors
     *
     * @param object|string $json
     *
     * @return string
     */
    protected function getJsonErrors($json) {

        if (isset($json->ValidationErrors) || isset($json->FieldsErrors)) {
            //$err = implode(', ', array_column($json->ValidationErrors, 'Error'));

            $errorsObj = isset($json->ValidationErrors) ? $json->ValidationErrors : $json->FieldsErrors;
            $blogDatas = array_column($errorsObj, 'Error', 'Name');

            return implode(', ', array_map(function ($k, $v) {
                        return "$k: $v";
                    }, array_keys($blogDatas), array_values($blogDatas)));
        }

        if (isset($json->Data->ErrorMessage)) {
            return $json->Data->ErrorMessage;
        }

        if (isset($json->Message)) {
            return $json->Message;
        }

        return '';
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * It will log the payment/shipping process events
     *
     * @param string $msg It is the string message that will be written in the log file
     *
     * @return null
     */
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

}

/**
 *  PaymentmontypayApiV2 handle the payment process of montypay API endpoints
 *
 * @author    montypay <montypay.com>
 * @copyright 2021 montypay, All rights reserved
 * @license   GNU General Public License v3.0
 */
class PaymentMontyApiS2S extends MontyApiV2 {

     /**
     * To specify either the payment will be onsite or offsite
     * (default value: false)
     *
     * @var boolean
     */
    protected $isDirectPayment = false;

    /**
     *
     * @var string
     */
    public static $pmCachedFile = __DIR__ . '/mf-methods.json';

    /**
     *
     * @var array
     */
    protected static $paymentMethods;

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get the invoice/payment URL and the invoice id
     *
     * @param array          $curlData
     * @param string         $gatewayId (default value: 'montypay')
     * @param integer|string $orderId   (default value: null) used in log file
     * @param string         $sessionId
     *
     * @return array
     */
    public function getInvoiceURL($curlData, $orderId = null) {

        $this->log('------------------------------------------------------------');

        return $this->sendPayment($curlData, $orderId);
    }
    public function getHostedCheckoutURL($curlData, $orderId = null) {

        $this->log('------------------------------------------------------------');

        return $this->sendPaymentHosted($curlData, $orderId);
    }

    public function get_return_url( $order = null ) {
		if ( $order ) {
			$return_url = $order->get_checkout_order_received_url();
		} else {
			$return_url = wc_get_endpoint_url( 'order-received', '', wc_get_checkout_url() );
		}

		return apply_filters( 'woocommerce_get_return_url', $return_url, $order );
	}
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
    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * (POST API)
     *
     * @param array          $curlData
     * @param integer|string $orderId  (default value: null) used in log file
     *
     * @return array
     */
    protected function sendPayment($curlData, $orderId = null) {
        global $woocommerce;
        
        $json = $this->callAPI("https://api.montypay.com/post", 'S2S', $curlData, $orderId, 'Send Payment');

        

        $this->log("sendpayment - result_new: ".json_encode($json));

        if(isset($json->redirect_url) && isset($json->result) && $json->result != 'ERROR' && $json->status == 'REDIRECT' && $json->result == 'REDIRECT'){

            $order = new WC_Order($json->order_id);

            $order->update_status('on-hold', 'Awaiting 3-D Secure Payment');

            $this->log("3ds - response: ".json_encode($json));

            
            // ob_start();
            //     echo '<script>alert("test")</script>';
            // ob_get_clean();

            return ['result' => $json->result, 'status' => $json->status, 'order_id' => $json->order_id, 'redirect_url' => $json->redirect_url, 'body' => $json->redirect_params->body, 'redirect_method' => $json->redirect_method];

        }elseif(isset($json->result) && $json->result == 'SUCCESS' && $json->status == 'SETTLED'){
            $order = new WC_Order($json->order_id);

            $order->update_status('pending', 'Pending Payment');

            return ['result' => $json->result, 'status' => $json->status, 'order_id' => $json->order_id, 'redirect_url' => $this->get_montypay_return_url($order)]; // $order->get_view_order_url()

        }elseif(isset($json->redirect_url) && isset($json->redirect_method) && $json->redirect_method == 'GET' && isset($json->result) && $json->result == 'REDIRECT' && $json->status == '3DS'){
            // new
            $order = new WC_Order($json->order_id);

            $order->update_status('on-hold', 'Awaiting 3-D Secure Payment');

            $this->log("3ds - response: ".json_encode($json));

            return ['result' => $json->result, 'status' => $json->status, 'order_id' => $json->order_id, 'redirect_url' => $json->redirect_url, 'body' => "", 'redirect_method' => $json->redirect_method];
            // new
        }elseif(isset($json->redirect_url) && isset($json->redirect_method) && $json->redirect_method == 'POST' && isset($json->result) && $json->result == 'REDIRECT' && $json->status == '3DS'){
            // new
            $order = new WC_Order($json->order_id);

            $order->update_status('on-hold', 'Awaiting 3-D Secure Payment');

            $this->log("3ds - response: ".json_encode($json));

            return ['result' => $json->result, 'status' => $json->status, 'order_id' => $json->order_id, 'redirect_url' => $json->redirect_url, 'body' => $json->redirect_params, 'redirect_method' => $json->redirect_method];
            // new
        }elseif(isset($json->result) && $json->result == 'DECLINED' && $json->status == 'DECLINED'){
            
            return ['result' => $json->result, 'status' => $json->status, 'order_id' => $json->order_id, 'decline_reason' => $json->decline_reason];
        }else{

            return ['result' => $json->result, 'error_message' => $json->error_message, 'errors' => $json->errors];
        }

        // $this->log("Response - redirect_url: ".$json->redirect_url);
        // $this->log("Response - redirect_method: ".$json->redirect_method);
        // $this->log("Response - body: ".$json->redirect_params->body);
    }
    protected function sendPaymentHosted($curlData, $orderId = null) {
        global $woocommerce;

        $json = $this->callAPI("https://checkout.montypay.com/api/v1/session", 'wallets', $curlData, $orderId, 'Send Payment Hosted');


        $this->log("Response - json: ".json_encode($json));
        // file_put_contents('./log_hosted_lb.log', json_encode($json), FILE_APPEND);

        if(isset($json->redirect_url) && $json->redirect_url){

            $order = new WC_Order($json->order_id);

            // $woocommerce->cart->empty_cart();
            $order->update_status('pending', 'Pending Payment');

            return [ 'order_id' => $orderId, 'redirect_url' => $json->redirect_url];

        }else{

            return ['result' => $json->result, 'error_message' => $json->error_message, 'errors' => $json->errors];
        }

        // $this->log("Response - redirect_url: ".$json->redirect_url);
        // $this->log("Response - redirect_method: ".$json->redirect_method);
        // $this->log("Response - body: ".$json->redirect_params->body);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
