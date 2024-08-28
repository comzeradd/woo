<?php

/**
 * Settings for MontyPay Gateway.
 */
$txtEnableNewDesign = __('Enable New Design', 'monty-woocommerce');


return array(
    'enabled'          => array(
        'title'   => __('Enable/Disable', 'woocommerce'),
        'type'    => 'checkbox',
        'default' => 'no',
        'label'   => __('Enable MontyPay', 'monty-woocommerce'),
    ),
    // 'newDesign'        => array(
    //     'title'   => $txtEnableNewDesign,
    //     'type'    => 'checkbox',
    //     'default' => 'yes',
    //     'label'   => $txtEnableNewDesign,
    // ),
    // 'title'            => array(
    //     'title'       => __('Title', 'woocommerce'),
    //     'type'        => 'text',
    //     'description' => __('This controls the title which the user sees during checkout.', 'montypay-woocommerce'),
    //     'desc_tip'    => true,
    //     'default'     => __($this->method_title, 'montypay-woocommerce'), //todo trans
    // ),
    // 'description'      => array(
    //     'title'       => __('Description', 'woocommerce'),
    //     'type'        => 'textarea',
    //     'description' => __('This controls the description which the user sees during checkout.', 'montypay-woocommerce'),
    //     'desc_tip'    => true,
    //     'default'     => __('Checkout with montypay Payment Gateway', 'montypay-woocommerce'),
    // ),
    'countryMode'      => array(
        'title'       => __('Country', 'montypay-woocommerce') . '<span class="mf-small mf-required dashicon dashicons dashicons-star-filled"></span>',
        'type'        => 'select',
        'description' => '<font style="color:#0093c9;"> <span class="dashicon dashicons dashicons-info"></span> ' . __('Select your country.', 'monty-woocommerce') . '</font>',
        'default'     => 'lebanon',
        'options' => array(
            'lebanon' => 'Lebanon',
            'bahrain' => 'Bahrain',
            'jordan' => 'Jordan',
            'nigeria' => 'Nigeria',
            'uae' => 'UAE'
        ),
    ),
    'connector'      => array(
        'title'       => __('Connector', 'montypay-woocommerce') . '<span class="mf-small mf-required dashicon dashicons dashicons-star-filled"></span>',
        'type'        => 'select',
        'description' => '<font style="color:#0093c9;"> <span class="dashicon dashicons dashicons-info"></span> ' . __('Select your Connector.', 'montypay-woocommerce') . '</font>',
        'default'     => 'blom',
        'options' => array(
            'blom' => 'BLOM',
            'creditlbenais' => 'Credit Libanais',
        ),
    ),
    'method' => array(
        'title' => __('Payment method', 'woocommerce-gateway-wpgfull'),
        'type' => 'multiselect',
        'description' => '<font style="color:#0093c9;"> <span class="dashicon dashicons dashicons-info"></span> ' .__('Payment method that client uses', 'woocommerce-gateway-wpgfull'). '</font>', /////
        'options' => array(
            'card' => 'Card',
            'applepay' => 'ApplePay'
        ),
    ),
    'settlement_account'      => array(
        'title'       => __('Settlement Account', 'montypay-woocommerce') . '<span class="mf-small mf-required dashicon dashicons dashicons-star-filled"></span>',
        'type'        => 'select',
        'description' => '<font style="color:#0093c9;"> <span class="dashicon dashicons dashicons-info"></span> ' . __('Select your settlement account.', 'montypay-woocommerce') . '</font>',
        'default'     => 'lebanon',
        'options' => array(
            '' => 'Select...',
            'usd' => 'USD',
            'jod' => 'JOD'
        ),
    ),
    // 'testMode'         => array(
    //     'title'       => __('Test Mode', 'montypay-woocommerce'),
    //     'type'        => 'checkbox',
    //     'description' => '<font style="color:#0093c9;"><span class="dashicon dashicons dashicons-info"></span> ' . __('Select Test Mode checkbox only when using test API Key.', 'montypay-woocommerce') . '</font>',
    //     'default'     => 'yes',
    //     'label'       => __('Enable Test Mode', 'montypay-woocommerce'),
    // ),
    // 'apiKey'           => array(
    //     'title'       => __('API Key', 'montypay-woocommerce') . '<span class="mf-small mf-required dashicon dashicons dashicons-star-filled"></span>',
    //     'type'        => 'textarea',
    //     'description' => __('Get your API Token Key from montypay Vendor Account.', 'montypay-woocommerce'),
    //     'desc_tip'    => true,
    // ),
    'merchant_identifier'      => array(
        'title'       => __('Merchant Identifier', 'monty-woocommerce'). '<span class="mf-small mf-required dashicon dashicons dashicons-star-filled"></span>',
        'type'        => 'text',
        'description' => __('Please insert merchant Identifier.', 'monty-woocommerce'),
        'desc_tip'    => true,
    ),
    'merchant_key'      => array(
        'title'       => __('Merchant Key', 'monty-woocommerce'). '<span class="mf-small mf-required dashicon dashicons dashicons-star-filled"></span>',
        'type'        => 'text',
        'description' => __('Please insert merchant key.', 'monty-woocommerce'),
        'desc_tip'    => true,
    ),
    'merchant_password'      => array(
        'title'       => __('Merchant Password', 'monty-woocommerce'). '<span class="mf-small mf-required dashicon dashicons dashicons-star-filled"></span>',
        'type'        => 'text',
        'description' => __('Please insert merchant key.', 'monty-woocommerce'),
        'desc_tip'    => true,
    ),
    'additional_payment_logos'      => array(
        'title'       => __('Additional Payments Logos', 'monty-woocommerce'). '<span class="mf-small mf-required dashicon dashicons dashicons-star-filled"></span>',
        'type'        => 'text',
        'description' => __('Please insert Additional Payments Logos.', 'monty-woocommerce'),
        'desc_tip'    => true,
    ),

    
//     'listOptions'      => array(
//         'title'       => __('List Payment Options', 'montypay-woocommerce'),
//         'type'        => 'select',
//         'description' => '<span id="listOptionsDesc" class="mf-hide"><font style="color:#0093c9;"> <span class="dashicon dashicons dashicons-update"></span> ' . __('Click on save changes to synchronize the payment gateways with your montypay account.', 'montypay-woocommerce') . '</font></span>',
//         'default'     => 'multigateways',
//         'options'     => [
//             'multigateways' => __('List All Enabled Gateways in Checkout Page', 'montypay-woocommerce'),
//             'montypay'    => __('Redirect to montypay Invoice Page', 'montypay-woocommerce'),
//         ],
//     ),
//     'registerApplePay' => array(
//         'title'       => __('Apple Pay Embedded', 'montypay-woocommerce'),
//         'type'        => 'checkbox',
//         'description' => '<font style="color:#0093c9;"><span class="dashicon dashicons dashicons-info"></span> ' . __('Create a folder named ".well-known" in the root path and copy the apple-developer-merchantid-domain-association file which you received from montypay support team (tech@montypay.com)', 'montypay-woocommerce') . '</font>',
//         'default'     => 'no',
//         'label'       => __('Enable Apple Pay Embedded', 'montypay-woocommerce'),
//     ),
//     'invoiceItems'     => array(
//         'title'       => __('Invoice items', 'montypay-woocommerce'),
//         'type'        => 'checkbox',
//         'description' => __('While disabling Invoice Items, montypay will send total order amount to the invoice page. In case of enabling montypay shipping, you should enable this option.', 'montypay-woocommerce'),
//         'default'     => 'yes',
//         'label'       => __('Enable Invoice items', 'montypay-woocommerce'),
//     ),
//     'orderStatus'      => array(
//         'title'       => __('Order Status', 'woocommerce'),
//         'type'        => 'select',
//         'description' => __('How to mark the successful payment in the Admin Orders Page.', 'montypay-woocommerce'),
//         'desc_tip'    => true,
//         'default'     => 'processing',
//         'options'     => array(
//             'processing' => __('Processing', 'woocommerce'),
//             'completed'  => __('Completed', 'woocommerce'),
//         ),
//     ),
//     'success_url'      => array(
//         'title'       => __('Payment Success URL', 'montypay-woocommerce'),
//         'type'        => 'text',
//         'description' => __('Please insert your Success URL (optional).', 'montypay-woocommerce'),
//         'desc_tip'    => true,
//         'default'     => '',
// //        'placeholder' => 'https://www.example.com/success',
//     ),
//     'fail_url'         => array(
//         'title'       => __('Payment Fail URL', 'montypay-woocommerce'),
//         'type'        => 'text',
//         'description' => __('Please insert your Failed URL (optional).', 'montypay-woocommerce'),
//         'desc_tip'    => true,
//         'default'     => '',
// //        'placeholder' => 'https://www.example.com/failed',
//     ),
//     'debug'            => array(
//         'title'       => __('Debug Mode', 'montypay-woocommerce'),
//         'type'        => 'checkbox',
//         'description' => __('Log montypay events in ', 'montypay-woocommerce') . $this->pluginlog,
//         'desc_tip'    => true,
//         'default'     => 'yes',
//         'label'       => __('Enable logging', 'montypay-woocommerce'),
//     ),
    // 'saveCard'         => array(
    //     'title'       => __('Save Card Information', 'montypay-woocommerce'),
    //     'type'        => 'checkbox',
    //     'description' => __('This feature allows the customers to save their card details for the future payments.', 'montypay-woocommerce'),
    //     'desc_tip'    => true,
    //     'default'     => 'no',
    //     'label'       => __('Enable montypay save card information feature for logged in users', 'montypay-woocommerce'),
    // ),
    // 'icon'             => array(
    //     'title'       => __('montypay Logo URL', 'montypay-woocommerce'),
    //     'type'        => 'text',
    //     'description' => __('Please insert your logo URL which the user sees during checkout.', 'montypay-woocommerce'),
    //     'desc_tip'    => true,
    //     'default'     => plugins_url(MONTY_WOO_PLUGIN_NAME) . '/assets/images/' . $this->code . '.png',
    //     'placeholder' => 'https://www.exampleurl.com',
    // ),
    // 'isSingleView'     => array(
    //     'title'       => __('Single Gateway View', 'montypay-woocommerce'),
    //     'type'        => 'checkbox',
    //     'description' => __('Use a custom title and icon to view the single gateway.', 'montypay-woocommerce'),
    //     'desc_tip'    => true,
    //     'default'     => 'yes',
    //     'label'       => __('Enable Single View', 'montypay-woocommerce'),
    // ),
    // 'webhookSecretKey' => array(
    //     'title'       => __('Webhook Secret Key', 'montypay-woocommerce'),
    //     'type'        => 'text',
    //     'description' => __('Get your Webhook Secret Key from montypay Vendor Account.', 'montypay-woocommerce'),
    //     'desc_tip'    => true,
    // ),
);
