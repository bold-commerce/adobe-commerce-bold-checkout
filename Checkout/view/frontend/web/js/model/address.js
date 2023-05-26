define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote',
], function ($, customerData, quote) {
    'use strict';

    return {
        /**
         * Get billing address from quote.
         *
         * @return object
         */
        getBillingAddress: function () {
            return this.convertAddress(quote.billingAddress());
        },

        /**
         * Get billing address from quote.
         *
         * @return object
         */
        getShippingAddress: function () {
            return this.convertAddress(quote.shippingAddress());
        },

        /**
         * Convert address to Bold API format.
         *
         * @param address object
         * @return object
         * @private
         */
        convertAddress: function (address) {
            if (!address.firstname) {
                throw new Error('Please provide your first name.');
            }
            if (!address.lastname) {
                throw new Error('Please provide your last name.');
            }
            const countryId = address.countryId;
            const countryData = customerData.get('directory-data');
            const countryName = countryData()[countryId] !== undefined ? countryData()[countryId].name : '';
            return {
                'business_name': address.company ? address.company : '',
                'country_code': countryId,
                'country': countryName,
                'city': address.city ? address.city : '',
                'first_name': address.firstname ? address.firstname : '',
                'last_name': address.lastname ? address.lastname : '',
                'phone_number': address.telephone ? address.telephone : '',
                'postal_code': address.postcode ? address.postcode : '',
                'province': address.region ? address.region : '',
                'province_code': address.regionCode ? address.regionCode : '',
                'address_line_1': address.street !== undefined && address.street[0] ? address.street[0] : '',
                'address_line_2': address.street !== undefined && address.street[1] ? address.street[1] : '',
            }
        },
    };
});
