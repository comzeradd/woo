jQuery(document).ready( function($) {

    $('#woocommerce_wc_gateway_montypay_wallets_countryMode').change(function(){
      // $(this).val(this.value).change();
      $('option').removeAttr('new_selected');
      var val = $(this).find(":selected").val();

      $('option[value='+val+']').attr('new_selected', val);

      var selected = $('#woocommerce_wc_gateway_montypay_wallets_method').val();

      if(val == 'jordan'){
        if(selected.indexOf('applepay') >= 0){
          $("#woocommerce_wc_gateway_montypay_wallets_settlement_account").parents("tr").show();
          $("#woocommerce_wc_gateway_montypay_wallets_settlement_account").prop('required',true);

          $("#woocommerce_wc_gateway_montypay_wallets_merchant_identifier").parents("tr").show();
          $("#woocommerce_wc_gateway_montypay_wallets_merchant_identifier").prop('required',true);
        }else{
          $("#woocommerce_wc_gateway_montypay_wallets_settlement_account").parents("tr").hide();
          $("#woocommerce_wc_gateway_montypay_wallets_settlement_account").prop('required',false);

          $("#woocommerce_wc_gateway_montypay_wallets_merchant_identifier").parents("tr").hide();
          $("#woocommerce_wc_gateway_montypay_wallets_merchant_identifier").prop('required',false);
        }
      }else{
        $("#woocommerce_wc_gateway_montypay_wallets_settlement_account").parents("tr").hide();
        $("#woocommerce_wc_gateway_montypay_wallets_settlement_account").prop('required',false);

        if(selected.indexOf('applepay') >= 0){
          if(val == 'lebanon'){
            $("#woocommerce_wc_gateway_montypay_wallets_merchant_identifier").parents("tr").hide();
            $("#woocommerce_wc_gateway_montypay_wallets_merchant_identifier").prop('required',false);
          }else{
            $("#woocommerce_wc_gateway_montypay_wallets_merchant_identifier").parents("tr").show();
            $("#woocommerce_wc_gateway_montypay_wallets_merchant_identifier").prop('required',true);
          }

        }else{

          $("#woocommerce_wc_gateway_montypay_wallets_merchant_identifier").parents("tr").hide();
          $("#woocommerce_wc_gateway_montypay_wallets_merchant_identifier").prop('required',false);
        }
      }
    });

    var selected = $('#woocommerce_wc_gateway_montypay_wallets_method').val();
    var country = $('#woocommerce_wc_gateway_montypay_wallets_countryMode').val();

        if(selected.indexOf('applepay') >= 0){
          // $('#woocommerce_wpgfull_merchant_identifier').show();
          $("#woocommerce_wc_gateway_montypay_wallets_merchant_identifier").parents("tr").show();
          $("#woocommerce_wc_gateway_montypay_wallets_merchant_identifier").prop('required',true);

          if(country == 'jordan'){
            $("#woocommerce_wc_gateway_montypay_wallets_settlement_account").parents("tr").show();
            $("#woocommerce_wc_gateway_montypay_wallets_settlement_account").prop('required',true);
          }else{
            $("#woocommerce_wc_gateway_montypay_wallets_settlement_account").parents("tr").hide();
            $("#woocommerce_wc_gateway_montypay_wallets_settlement_account").prop('required',false);
          }
        }else{
          $("#woocommerce_wc_gateway_montypay_wallets_merchant_identifier").parents("tr").hide();
          $("#woocommerce_wc_gateway_montypay_wallets_merchant_identifier").prop('required',false);

          $("#woocommerce_wc_gateway_montypay_wallets_settlement_account").parents("tr").hide();
          $("#woocommerce_wc_gateway_montypay_wallets_settlement_account").prop('required',false);

        }
      $('#woocommerce_wc_gateway_montypay_wallets_method').change(function(){
        var selected = $('#woocommerce_wc_gateway_montypay_wallets_method').val();
        if(selected.indexOf('applepay') >=0){
          // $('#woocommerce_wpgfull_merchant_identifier').show();
          $("#woocommerce_wc_gateway_montypay_wallets_merchant_identifier").parents("tr").show();
          $("#woocommerce_wc_gateway_montypay_wallets_merchant_identifier").prop('required',true);

          var new_country = $('option[new_selected]').attr('new_selected');

          if(new_country == 'jordan'){
            $("#woocommerce_wc_gateway_montypay_wallets_settlement_account").parents("tr").show();
            $("#woocommerce_wc_gateway_montypay_wallets_settlement_account").prop('required',true);
          }else{
            $("#woocommerce_wc_gateway_montypay_wallets_settlement_account").parents("tr").hide();
            $("#woocommerce_wc_gateway_montypay_wallets_settlement_account").prop('required',false);
          }
        }else{
          $("#woocommerce_wc_gateway_montypay_wallets_merchant_identifier").val('');
          $("#woocommerce_wc_gateway_montypay_wallets_merchant_identifier").parents("tr").hide();
          $("#woocommerce_wc_gateway_montypay_wallets_merchant_identifier").prop('required',false);

          $("#woocommerce_wc_gateway_montypay_wallets_settlement_account").parents("tr").hide();
          $("#woocommerce_wc_gateway_montypay_wallets_settlement_account").prop('required',false);
        }
    
      });

        $('#woocommerce_wc_gateway_montypay_wallets_method').html("");
        var methods = $('#woocommerce_wc_gateway_montypay_wallets_method').val();
            var data = {
                action: "get_payment_method",
                country: $('#woocommerce_wc_gateway_montypay_wallets_countryMode').val(),
                methods_selected: '',
                on_load: 'yes',
                payment_settings: 'wallets',
                connector: ''
            };
            $.ajax({
                url: ajax_object.ajax_url,
                type: "POST",
                data: data,
                dataType: "json",
                success: function (response) {
                    $('#woocommerce_wc_gateway_montypay_wallets_method').html(response)
                },
                error: function (response) {
                    console.log(response); 
                }
            });

        $('#woocommerce_wc_gateway_montypay_wallets_countryMode').change(function(){
            var methods = $('#woocommerce_wc_gateway_montypay_wallets_method').val();
            var data = {
                action: "get_payment_method",
                country: this.value,
                methods_selected: methods,
                on_load: 'no',
                payment_settings: 'wallets',
                connector: ''
            };
            $.ajax({
                url: ajax_object.ajax_url,
                type: "POST",
                data: data,
                dataType: "json",
                success: function (response) {
                    $('#woocommerce_wc_gateway_montypay_wallets_method').html(response)
                },
                error: function (response) {
                    console.log(response); 
                }
            });
        })
});