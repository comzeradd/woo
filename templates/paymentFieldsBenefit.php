<?php

    $icon_float;

    if(get_locale() == 'ar'){
        $icon_float = "left";
    }else{
        $icon_float = "right";
    }

?>
<style>
img.benefit_icon{
        float: <?php echo $icon_float; ?> !important;
    }
</style>
<input type="hidden" disabled data-mfVersion="<?php echo MONTY_WOO_PLUGIN_VERSION; ?>"/>

<div class="benefit_container">
    <button type="submit" class="button alt wp-element-button" name="montypay_checkout_place_order" id="afsbenefit_btn" value="PLACE ORDER">
        <?php echo get_translation("Pay with Benefit"); ?>
    </button>
</div>