define([
    'underscore',
    'Magento_Checkout/js/model/quote',
    'checkoutData'
], function (_, quote, checkoutData) {
    'use strict';

    return {

        /**
         * Get customer api data.
         *
         * @return object
         */
        getCustomer: function () {
            const billingAddress = quote.billingAddress();
            if (!billingAddress) {
                return null;
            }
            const firstname = billingAddress.firstname;
            const lastname = billingAddress.lastname;
            const payload = {
                'email_address': checkoutData.getValidatedEmailValue(),
                'first_name': firstname,
                'last_name': lastname,
            }
            if (!payload.email_address || !payload.first_name || !payload.last_name) {
                return null;
            }
            return payload;
        }
    };
});
