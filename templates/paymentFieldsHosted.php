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
    
    table.custom_hosted_table, .custom_hosted_table td {
        border: none !important;
        margin: 0 0 0em;
    }
    table.custom_hosted_table {
        margin: 0 0 0em !important;
    }
    .payment_box.payment_method_montypay_hosted {
        padding-top: 0em !important;
    }

    img.method_icon{
        float: <?php echo $icon_float; ?> !important;
    }
</style>

<table class="custom_hosted_table">
    <tr>
        <td style="width:100px">
            <div class="hosted_pay_container"><?php echo get_translation('Powered By'); ?></div>
        </td>
        <td>
            <div>
                <img src="https://montypaydev.com/global_assets/images/mp_logo.png" class="description_image">
            </div>
        </td>
    </tr>
</table>
