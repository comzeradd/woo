jQuery(document).ready(function($) {

        $("#card_number").keyup(function(e){
            var str = $("#card_number").val();
            var nospaces = str.replace(/\s/g, "");
    
            if(nospaces.length >= 16){
                var data = {
                    "action": "checksum",
                    "card_number": nospaces
                };
    
                $.ajax({
                    url: ajax_object.ajax_url,
                    type: "POST",
                    data: data,
                    // dataType: "json",
                    success: function (response) {
    
                        // console.log($.trim(response));
                        if($.trim(response) == "invalid"){
                            // $('#cn_error').show();
                            $("#cn_error").css("display", "block")
                            $('#montypay_pay').prop('disabled', true);
    
                        }else{
                            if(!str.match(/\s/g)){
                                const with_spaces = str.match(/.{1,4}/g);
                                $('#card_number').val(with_spaces.join(' '));
                            }
                            $('#cn_error').hide();
                            $('#montypay_pay').prop('disabled', false);
    
                        }
                        
                    },
                    error: function (response) {
                        console.log(response); 
                        // alert(data);
                        
                    }
                });
            }
        });

        $("#card_number").keydown(function (e) {
            var key = e.charCode || e.keyCode || 0;
            $text = $(this); 
            if (key !== 8 && key !== 9) {
                if ($text.val().length === 4) {
                    $text.val($text.val() + " ");
                }
                if ($text.val().length === 9) {
                    $text.val($text.val() + " ");
                }
                if ($text.val().length === 14) {
                    $text.val($text.val() + " ");
                }
    
            }
    
        
        })
    
        $("#card_exp").keyup(function(e) {
            var key1 = e.charCode || e.keyCode || 0;
            $date = $(this); 
            if (key1 !== 8 && key1 !== 9) {
                if($date.val().length === 1){
                    var one = $date.val().charAt(0);
                    var two = e.key;
                    
                    if(one > 1){
                     
                        $date.val("0"+$date.val() + " / ");
                    }
                }
                if ($date.val().length === 2) {
                    $date.val($date.val() + " / ");
                    
                }
            }
        })
    
        $("#card_exp").change(function(e){
            
            var exptrim = $.trim(this.value);
            const myArray = exptrim.split("/");
            let year = myArray[1];
            if(year.length > 2){
              var lastChar = year.substr(year.length - 2);
              $("#card_exp").val($.trim(myArray[0])+ " / "+lastChar);
              
              
            }
        })
    
        $("#card_exp").focus(function(){
            $(this).attr("placeholder", "MM / YY");
          });
          $("#card_exp").blur(function(){
            if(ajax_object.lang == 'ar'){
                $(this).attr("placeholder", "تاريخ الانتهاء");
            }else{
                $(this).attr("placeholder", "Expiry Date");
            }
          });


        //   $("#montypay_pay").click(function (e){
        //     e.preventDefault();
        //     alert('test');
        //   })

    });