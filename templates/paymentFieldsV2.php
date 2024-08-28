<input type="hidden" disabled data-mfVersion="<?php echo MONTY_WOO_PLUGIN_VERSION; ?>"/>
<?php
$this->get_parent_payment_fields();

if ($this->listOptions === 'multigateways') {
    if ($this->count > 1) {
        $key = 0;
        foreach ($this->gateways as $gateway) {
            $checked = ($key == 0) ? 'checked' : '';
            $key++;

            $label   = ($this->lang == 'ar') ? $gateway->PaymentMethodAr : $gateway->PaymentMethodEn;
            $radioId = 'mf-radio-' . $gateway->PaymentMethodId;
            ?>
            <span class="mf-div" style="margin: 20px; display: inline-flex;">
                <input class="mf-radio" <?php echo $checked; ?> type="radio" id="<?php echo $radioId; ?>" name="mf_gateway" value="<?php echo $gateway->PaymentMethodId; ?>" style="margin: 5px; vertical-align: top;"/>
                <label for="<?php echo $radioId; ?>">
                    <?php echo $label; ?>&nbsp;
                    <img class="mf-img" src="<?php echo $gateway->ImageUrl; ?>" alt="<?php echo $label; ?>" style="margin: 0px; width: 50px; height: 30px;"/>
                </label>
            </span>
            <?php
        }
    } else if ($this->count == 1) {
        ?>
        <input type="hidden" name="mf_gateway" value="<?php echo $this->gateways[0]->PaymentMethodId; ?>"/>
        <?php
    } else if ($this->count == 0) {
        ?>
        <script>
            jQuery('.payment_method_montypay_v2').hide();
        </script>
        <?php
    }
}
