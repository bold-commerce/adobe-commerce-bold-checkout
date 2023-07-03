define([
    'jquery',
    'Magento_Customer/js/model/authentication-popup',
    'Magento_Customer/js/customer-data'
], function ($, authenticationPopup, customerData) {
    'use strict';

    return function (config, element) {
        $(element).click(function (event) {
            var cart = customerData.get('cart'),
                customer = customerData.get('customer');
            event.preventDefault();
            if (!window.vatexemptConfig.apllied) {
                $('#block-shipping').collapsible('activate');
                $('html, body').animate(
                    {
                        scrollTop: 0
                    },
                    'slow'
                );
                return false;
            }
            if (!customer().firstname && cart().isGuestCheckoutAllowed === false) {
                authenticationPopup.showModal();

                return false;
            }
            $(element).attr('disabled', true);
            location.href = config.checkoutUrl;
        });

    };
});
