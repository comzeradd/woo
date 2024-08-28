<?php

    $icon_float;

    if(get_locale() == 'ar'){
        $icon_float = "left";
    }else{
        $icon_float = "right";
    }

    $redirect = WP_PLUGIN_URL . "/montypay-payment-gateway/redirects/awaiting_3d_secure.php";

?>

<input type="hidden" disabled data-mfVersion="<?php echo MONTY_WOO_PLUGIN_VERSION; ?>"/>

<style>
    @font-face {
        font-family: "primeformpro";

        src: url("<?php echo WP_PLUGIN_URL; ?>/montypay-payment-gateway/assets/fonts/PrimeformPro-Light.otf'.'") format("opentype");
    }

    img.method_icon, img.jordan_logos{
        float: <?php echo $icon_float; ?> !important;
    }

    input#card_number, input#card_exp, input#card_cvc {
        direction: ltr;
        text-align: inherit;
    }
    
</style>
        <div class="custom-credit-card-form"><fieldset id="wc-' . esc_attr( $this->id ) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">
        <div>
            <img src="https://montypaydev.com/global_assets/images/mp_logo_bg_trans.png" class="description_image">
        </div>
        <div id="apple_pay_container"></div>
        <div class="divider_container">
            <div class="mf-divider">
                </div> 
            <p class="form_desc"><?php echo get_translation('Insert Card Details'); ?></p>
        </div>
        <div class="card_container">
            <div class="row card_info">
                <div class="input-group input-group-icon" style="margin-bottom: 1.5em; width: -webkit-fill-available;">
                    <input type="text" name="card_number" id="card_number" autocomplete="cc-number" inputmode="numeric" maxlength="19" placeholder="<?php echo get_translation('Card Number'); ?>"/>
                    <div>
                        <span id="cn_error"><?php echo get_translation('Invalid Card Number'); ?></span>
                    </div>
                </div>
                <div class="col-half">
                    <div class="input-group input-group-icon expiry">
                        <input type="text" name="card_exp" id="card_exp" maxlength="7" pattern="[0-9]*" placeholder="<?php echo get_translation('Expiry Date'); ?>"/>
                    </div>
                </div>
                <div class="col-half">
                    <div class="input-group input-group-icon">
                        <input type="password" name="card_cvc" id="card_cvc" maxlength="4" placeholder="<?php echo get_translation('CVV'); ?>"/>
                    </div>
                </div>
                <input type="hidden" value="" id="pay_method" name="pay_method">
            </div>
        </div>
      
      <button type="submit" class="button alt wp-element-button" name="montypay_checkout_place_order" id="montypay_pay" value="PLACE ORDER" data-value="PLACE ORDER"><?php echo get_translation('Place Order & Pay'); ?></button>
      
      <div class="clear"></div></fieldset></div>

      <!-- <form action="" method="$_SE">

      </form> -->

      <script src="<?php echo WP_PLUGIN_URL; ?>/montypay-payment-gateway/assets/js/montypay_js.js"></script>

<?php

if ($this->listOptions === 'multigateways') {
    if ((!empty($this->gateways['ap'])) || $this->count > 1 || (!empty($this->gateways['form'])) && count($this->gateways['form']) >= 1) {
        $txtPayWith = __('Pay With', 'montypay-woocommerce');
        ?>
        <div class="mf-payment-methods-container">
            <!--Start Card Section-->
            <?php if (!empty($this->gateways['cards']) || !empty($this->gateways['ap'])) { ?>
                <div class="mf-grey-text" style="font-family: 'Roboto', sans-serif; font-size: 14px; font-weight: 500" ><?php echo __('How would you like to pay?', 'montypay-woocommerce'); ?></div>
                <?php if (!empty($this->gateways['ap'])) {
                    ?>
                    <div id="mf-apple-button" style="height: 40px; padding-top: 12px;"></div>
                <?php } ?>
                <?php if (!empty($this->gateways['cards'])) { ?>
                    <div class="mf-divider">
                        <span class="mf-divider-span"><?php echo $txtPayWith; ?></span>
                    </div> 
                <?php } ?>
                <input id="mf_gateway" name="mf_gateway" type="hidden" value="">

                <?php foreach ($this->gateways['cards'] as $mfCard) { ?>
                    <?php $mfPaymentTitle = ($this->lang == 'ar') ? $mfCard->PaymentMethodAr : $mfCard->PaymentMethodEn ?>

                    <button class="mf-card-container" style="width: unset;" mfCardId="<?php echo $mfCard->PaymentMethodId; ?>" title="<?php echo ($txtPayWith . ' ' . $mfPaymentTitle); ?>" >
                        <div class="mf-row-container">
                            <img class="mf-payment-logo" src="<?php echo $mfCard->ImageUrl; ?>"alt="<?php echo $mfPaymentTitle; ?>">
                            <span class="mf-payment-text mf-card-title"><?php echo $mfPaymentTitle; ?></span>
                        </div>
                        <span class="mf-payment-text" style="text-align: end;">
                            <?php echo $mfCard->GatewayData['GatewayTotalAmount']; ?> <?php echo $mfCard->GatewayData['GatewayCurrency']; ?>
                        </span>
                    </button>
                <?php } ?>
                <script>
                    jQuery(document).ready(function ($) {
                        //card button clicked
                        $("[mfCardId]").on('click', function (e) {
                            e.preventDefault();
                            $('#mf_gateway').val($(this).attr('mfCardId'));
                            var fc = 'form.checkout';
                            $(fc).submit();
                        });
                    });
                </script>
                <!--End Card Section-->
                <!--Start Form Section-->
                <?php
            }
            if (!empty($this->gateways['form'])) {
                ?>

                <div class="mf-divider">
                    <span class="mf-divider-span">
                        <?php
                        if (count($this->gateways['cards']) > 0 || !empty($this->gateways['ap'])) {
                            echo __('Or ', 'montypay-woocommerce');
                        }
                        echo __('Insert Card Details', 'montypay-woocommerce');
                        ?>
                    </span>
                </div>
                <div id="mf-card-element" style="width:99%; max-width:800px; padding: 0rem 0.2rem"></div>
                <button class="mf-btn mf-pay-now-btn" type="button" 
                        style="background-color: #0293cc;
                        border: none; border-radius: 8px;
                        padding: 7px 3px;">
                    <span class="mf-pay-now-span">
                        <?php echo __('Pay Now', 'montypay-woocommerce'); ?> 
                    </span>
                </button>
                <input type="hidden" id="mfData" name="mfData" value="">

            <?php } ?>
            <!--End Form Section-->
        </div>
        <?php
    } else if ($this->count == 1) {
        $card = isset($this->gateways['cards'][0]->PaymentMethodId) ? $this->gateways['cards'][0]->PaymentMethodId : null;
        ?>
        <input id="mf_gateway" name="mf_gateway" type="hidden" value="<?php echo $card; ?>">
        <?php
    } else if ($this->count == 0) {
        ?>
        <script>
            jQuery('.payment_method_montypay_v2').hide();
        </script>
        <?php
    }
}    