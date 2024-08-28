<?php

add_action( 'wp_ajax_checksum', 'checksum' );
add_action( 'wp_ajax_nopriv_checksum', 'checksum' );

function checksum() {
    $card_number = $_POST[ 'card_number' ];
    $extractedChecksum = substr($card_number, -1);

    // Extract the value.
    $extractedValue = substr($card_number, 0, -1);

    // Calculate the checksum on the extracted value.
    $calcuatedChecksum = generateChecksum($extractedValue);

    // Compare!
    if ($extractedChecksum != $calcuatedChecksum) {
        // return json_encode(array('result' =>'invalid'));
      echo 'invalid';
    } else {
        // return json_encode(array('result' =>'valid'));
      echo 'valid';
    }

    wp_die();
}



function generateChecksum($value) {
    $value = str_replace(" ", "", $value);
    if (!is_numeric($value)) {
      throw new \InvalidArgumentException(__FUNCTION__ . ' can only accept numeric values.');
    }

    // Force the value to be a string so we can work with it like a string.
    $value = (string) $value;

    // Set some initial values up.
    $length = strlen($value);
    $parity = $length % 2;
    $sum = 0;

    for ($i = $length - 1; $i >= 0; --$i) {
      // Extract a character from the value.
      $char = $value[$i];
      if ($i % 2 != $parity) {
        $char *= 2;
        if ($char > 9) {
          $char -= 9;
        }
      }
      // Add the character to the sum of characters.
      $sum += $char;
    }

    // Return the value of the sum multiplied by 9 and then modulus 10.
    return ($sum * 9) % 10;
  }

  
add_action( 'wp_ajax_get_payment_method', 'get_payment_method' );
add_action( 'wp_ajax_nopriv_get_payment_method', 'get_payment_method' );

function get_payment_method(){
  global $wpdb;

  $country = $_POST['country'];
  $selected_methods = $_POST['methods_selected'];
  $onload = $_POST['on_load'];
  $payment_settings = $_POST['payment_settings'];
  $connector = $_POST['connector'];

  if($payment_settings == 'wallets'){
    $section_settings = 'woocommerce_wc_gateway_montypay_wallets_settings';
    $bahrain_methods = array('applepay'=> 'ApplePay');
    if(isset($connector) && $connector == 'blom'){
      $lebanon_methods = array();
    }else{
      $lebanon_methods = array();
    }
    $jordan_methods = array('applepay'=> 'ApplePay',);
    $uae_methods = array('applepay'=> 'ApplePay',);
    $nigeria_methods = array();
  }elseif($payment_settings == 'hosted'){
    $section_settings = 'woocommerce_wc_gateway_montypay_hosted_settings';
    $bahrain_methods = array('applepay'=> 'ApplePay','afsbenefit'=>'Benefit','card'=>'Card' );
    if(isset($connector) && $connector == 'blom'){
      $lebanon_methods = array('cybersource'=>'Card');
    }else{
      $lebanon_methods = array('card'=>'Card');
    }
    $jordan_methods = array('applepay'=> 'ApplePay','card'=>'Card');
    $uae_methods = array('applepay'=> 'ApplePay','card'=>'Card');
    $nigeria_methods = array('a2a-transfer' => 'Bank Transfer', 'card'=>'Card');
  }


  $table_name = $wpdb->prefix . 'options';
  $query = "SELECT * FROM $table_name WHERE option_name='$section_settings'";
  $result = $wpdb->get_results($query);
  if($result){
    $settings = unserialize($result[0]->option_value);
    if($onload == 'yes'){
      $selected_methods = $settings['method'];
    }
  }
  
  if($country == 'jordan'){
    $option = get_options_methods_mp($jordan_methods,$selected_methods);

  }elseif($country == 'lebanon'){
    $option = get_options_methods_mp($lebanon_methods,$selected_methods);

  }elseif($country == 'bahrain'){
    $option = get_options_methods_mp($bahrain_methods,$selected_methods);
  }elseif($country == 'nigeria'){
    $option = get_options_methods_mp($nigeria_methods,$selected_methods);
  }elseif($country == 'uae'){
    $option = get_options_methods_mp($uae_methods,$selected_methods);
  }
  echo json_encode($option);
  die();
}

function get_options_methods_mp($country_methods,$selected_methods){
  $option = '';
  foreach($country_methods as $key => $value){
    if($selected_methods){
      if(in_array($key, $selected_methods)){
        $option .='<option value="'.$key.'" selected="selected">'.$value.'</option>';
      }else{
        $option .='<option value="'.$key.'">'.$value.'</option>';
      }
    }else{
      $option .='<option value="'.$key.'">'.$value.'</option>';

    }
  }
  return $option;
}

function get_translation($text){
  $translations = [
    "Powered By" => "مدعوم من",
    "Pay with Card" => "الدفع بالبطاقة",
    "Insert Card Details" => "أدخل تفاصيل البطاقة",
    "Card Number" => "رقم البطاقة",
    "Expiry Date" => "تاريخ الانتهاء",
    "CVV" => "الرقم السري",
    "Invalid Card Number" => "رقم البطاقة غير صحيح",
    "Place Order & Pay" => "تقديم الطلب والدفع",
    "Credit/Debit card" => "بطاقة الائتمان / الخصم",
    "Wallets" => "المحفظة",
    "Sorry! you can only make a payment through an IOS device." => "يمكنك فقط إجراء الدفع من خلال جهاز IOS.",
    "Sorry! you can't make payment using Apple Pay. Activate a card in your wallet and try again." => "لا يمكنك الدفع باستخدام Apple Pay. قم بتنشيط بطاقة في محفظتك وحاول مرة أخرى.",
    "Pay with Benefit" => "ادفع عن طريق بنفت",
    "Benefit" => "بنفت",
    "Note: Currency will be converted to JOD in the following step." => "ملاحظة: سيتم تحويل العملة إلى دينار أردني في الخطوة التالية.",
  ];

  if(array_key_exists($text, $translations)){
    if(get_locale() == 'ar'){
      return $translations[$text];
    }else{
      return $text;
    }
  }else{
    return $text;
  }
}


add_filter('woocommerce_order_button_html', 'remove_place_order_button_for_specific_payments' );
function remove_place_order_button_for_specific_payments( $button ) {
    // HERE define your targeted payment(s) method(s) in the array
    $targeted_payments_methods = array('wc_gateway_montypay_s2s', 'wc_gateway_montypay_wallets');
    $chosen_payment_method     = WC()->session->get('chosen_payment_method'); // The chosen payment

    // For matched payment(s) method(s), we remove place order button (on checkout page)
    if( in_array( $chosen_payment_method, $targeted_payments_methods ) && ! is_wc_endpoint_url() ) {
        $button = ''; 
    }
    return $button;
}

// jQuery - Update checkout on payment method change
add_action( 'wp_footer', 'custom_checkout_jquery_script' );
function custom_checkout_jquery_script() {
    if ( is_checkout() && ! is_wc_endpoint_url() ) :
    ?>
    <script type="text/javascript">
    jQuery( function($){
        $('form.checkout').on('change', 'input[name="payment_method"]', function(){
            $(document.body).trigger('update_checkout');
        });
    });
    </script>
    <?php
    endif;
}
