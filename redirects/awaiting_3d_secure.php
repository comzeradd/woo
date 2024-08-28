<?php
/*
Template Name: Threedsecure Page Template
*/


if(isset($_GET['method'])) {
    $method = $_GET['method'];

    if($method == 'GET') {
        
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];
        
        $currentURL = $protocol . "://" . $host . $uri;
        
        $startPos = strpos($currentURL, 'url=');
        
        if ($startPos !== false) {
            // Extract the substring from the 'url=' position to the end of the string
            $urlParameterValue = substr($currentURL, $startPos + strlen('url='));
            
            $url = $urlParameterValue;
        } else {
            echo "URL parameter 'url=' not found.";
            exit;

        }
        
        $queryString = parse_url($url, PHP_URL_QUERY);

        // Parse the query string to get the parameters
        parse_str($queryString, $params);
        
        // Output an array of key-value pairs (parameter name => parameter value)
        $outputArray = [];
        foreach ($params as $paramName => $paramValue) {
            $outputArray[$paramName] = $paramValue;
        }
        
        
        
    }else{
        $url = $_GET['url'];
        $method = $_GET['method'];
        $body = $_GET['body'];
    }
}

// echo json_encode($outputArray);
// exit;



$error = false;

if(isset($method) && $method == "GET" && isset($url)){
    
    $error = false;
    $message = "Invoking 3-D secure form, please wait ...";

    
}elseif(isset($method) && $method == "POST"){
    
    $error = false;
    $message = "Invoking 3-D secure form, please wait ...";

    
}else{
    
    $error = true;
    $message = "Invalid 3D - Secure!";
    
}

$image = 'https://montypaydev.com/global_assets/images/mp_logo_bg_trans.png';

?>

<!DOCTYPE html>
<html>
<head>
    <title>3D Secure Verification</title>
    <script language="Javascript">
        function OnLoadEvent() { document.form.submit(); }
    </script>
    <style>
        .container {
            display: block;
            margin: auto;
            text-align: center;
            position: relative;
            margin-top: 5%;
        }
        @font-face {
            font-family: "primeformpro";

            src: url("PrimeformPro-Light.otf") format("opentype");
        }

        .awaiting_desc{
            font-family: 'primeformpro';
        }
    </style>
</head>
    <body OnLoad="OnLoadEvent();">
        <div class='container'>
            <p class='awaiting_desc'>
                <?php echo $message; ?>
            </p>
            <img src='<?php echo $image;?>' width='100'/>
        </div>
        <?php if(!$error){ ?>
            <form name="form" action="<?php echo $url; ?>" method="<?php echo $method; ?>">
                <?php if($method == "GET"){
                       foreach($outputArray as $key => $value){
                           ?>
                                <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
                           <?php
                       }
                    }else{

                        if(is_array($body)){

                            foreach($body as $key => $value){
      
                              
                              ?>
                              <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
      
                              <?php
                              }
                          }else{
                              ?>
                              <input type="hidden" name="body" value="<?php echo $body; ?>">
      
                              <?php
                          }
                    }
                ?>
        
                <noscript>
                    <p>Please click</p><input id="to-asc-button" type="submit">

                </noscript>

            </form>
        <?php } ?>
    </body>
</html>