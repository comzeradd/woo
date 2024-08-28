<?php

    $icon_float;

    if(get_locale() == 'ar'){
        $icon_float = "left";
    }else{
        $icon_float = "right";
    }

?>

<input type="hidden" disabled data-mfVersion="<?php echo MONTY_WOO_PLUGIN_VERSION; ?>"/>

<style>
    .apple-pay-button {
    display: inline-block;
    -webkit-appearance: -apple-pay-button;
    -apple-pay-button-type: check-out; /* Use any supported button type. */
    }
    .apple-pay-button-black {
        -apple-pay-button-style: black;
    }
    .apple-pay-button-white {
        -apple-pay-button-style: white;
    }
    .apple-pay-button-white-with-line {
        -apple-pay-button-style: white-outline;
    }

    img.wallets_icon{
        float: <?php echo $icon_float; ?> !important;
    }
</style>
<div class="apple_pay_container"></div>
<div class="apple_pay_hint"></div>
<?php

if (!empty($this->gateways['form'])) {
    ?>
    <div class="mf-embed-container">
        <div id="mf-card-element" style="margin-inline-start: 0.25rem; margin-top: 0.25rem;"></div>
    </div>
    <?php
} else {
    ?>
    <script>
        jQuery('.payment_method_montypay_embedded').hide();
    </script>
    <?php
}

$error1 = get_translation("Sorry! you can't make payment using Apple Pay. Activate a card in your wallet and try again.");
$error2 = get_translation("Sorry! you can only make a payment through an IOS device.");

if($this->settlement_account == 'usd'){
    $hint = get_translation("Note: Currency will be converted to JOD in the following step.");
}else{
    $hint = "";
}

?>

<script>
    jQuery(document).ready(function($) {
            if (window.ApplePaySession) {
            var merchantIdentifier = "<?php echo $this->merchant_identifier; ?>";
            var promise = ApplePaySession.canMakePaymentsWithActiveCard(merchantIdentifier);
            promise.then(function (canMakePayments) {
                // if (canMakePayments){
                    $(".apple_pay_container").html(`<button type="submit" class="button alt wp-element-button apple-pay-button apple-pay-button-black" name="montypay_checkout_place_order" id="applepay_btn" value="PLACE ORDER"></button>`);
                    $(".apple_pay_hint").html('<p class="applepay_error"><?php echo $hint; ?> </p>');
                // }else{
                //     $(".apple_pay_container").html(`<p class="applepay_error">//echo $error1; </p>`);
                // }
            }); 
            
        }else{
            $(".apple_pay_container").append(`<p class="applepay_error"><?php echo $error2; ?> </p>`);
        }

        $("#apple_pay").click(function(){
            $("#pay_method").val("applepay");
        });
    });
</script>