jQuery(document).ready( function($) {

        var country = $('#woocommerce_wc_gateway_montypay_hosted_countryMode').val();
        if(country == 'lebanon'){
            $("#woocommerce_wc_gateway_montypay_hosted_connector").parents("tr").show();
        }else{
            $("#woocommerce_wc_gateway_montypay_hosted_connector").parents("tr").hide();
        }

        $('#woocommerce_wc_gateway_montypay_hosted_countryMode').change(function(){
            var country = $(this).val();
            if(country == 'lebanon'){
              $("#woocommerce_wc_gateway_montypay_hosted_connector").parents("tr").show();
            }else{
              $("#woocommerce_wc_gateway_montypay_hosted_connector").parents("tr").hide();
            }

        });

        $('#woocommerce_wc_gateway_montypay_hosted_method').html("");
        var methods = $('#woocommerce_wc_gateway_montypay_hosted_method').val();
            var data = {
                action: "get_payment_method",
                country: $('#woocommerce_wc_gateway_montypay_hosted_countryMode').val(),
                methods_selected: '',
                on_load: 'yes',
                payment_settings: 'hosted',
                connector: $('#woocommerce_wc_gateway_montypay_hosted_connector').val()

            };
            $.ajax({
                url: ajax_object.ajax_url,
                type: "POST",
                data: data,
                dataType: "json",
                success: function (response) {
                    $('#woocommerce_wc_gateway_montypay_hosted_method').html(response)
                },
                error: function (response) {
                    console.log(response); 
                }
            });

        $('#woocommerce_wc_gateway_montypay_hosted_countryMode').change(function(){
            var methods = $('#woocommerce_wc_gateway_montypay_hosted_method').val();
            var data = {
                action: "get_payment_method",
                country: this.value,
                methods_selected: methods,
                on_load: 'no',
                payment_settings: 'hosted',
                connector: $('#woocommerce_wc_gateway_montypay_hosted_connector').val()
            };
            $.ajax({
                url: ajax_object.ajax_url,
                type: "POST",
                data: data,
                dataType: "json",
                success: function (response) {
                    $('#woocommerce_wc_gateway_montypay_hosted_method').html(response)
                },
                error: function (response) {
                    console.log(response); 
                }
            });
        })


        $('#woocommerce_wc_gateway_montypay_hosted_method').html("");
        var methods = $('#woocommerce_wc_gateway_montypay_hosted_method').val();
            var data = {
                action: "get_payment_method",
                country: $('#woocommerce_wc_gateway_montypay_hosted_countryMode').val(),
                methods_selected: '',
                on_load: 'yes',
                payment_settings: 'hosted',
                connector: $('#woocommerce_wc_gateway_montypay_hosted_connector').val(),
            };

            $.ajax({
                url: ajax_object.ajax_url,
                type: "POST",
                data: data,
                dataType: "json",
                success: function (response) {
                    $('#woocommerce_wc_gateway_montypay_hosted_method').html(response)
                },
                error: function (response) {
                    console.log(response); 
                }
            });

        $('#woocommerce_wc_gateway_montypay_hosted_connector').change(function(){
            var methods = $('#woocommerce_wc_gateway_montypay_hosted_method').val();
            var data = {
                action: "get_payment_method",
                country: $('#woocommerce_wc_gateway_montypay_hosted_countryMode').val(),
                methods_selected: methods,
                on_load: 'no',
                payment_settings: 'hosted',
                connector: $(this).val()
            };
            $.ajax({
                url: ajax_object.ajax_url,
                type: "POST",
                data: data,
                dataType: "json",
                success: function (response) {
                    $('#woocommerce_wc_gateway_montypay_hosted_method').html(response)
                },
                error: function (response) {
                    console.log(response); 
                }
            });
        })
});