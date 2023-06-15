define([
    'underscore',
    'Magento_Checkout/js/model/quote',
    'uiRegistry',
], function (_, quote, registry) {
    'use strict';

    /**
     * Bold address model.
     *
     * @type object
     */
    const boldAddress = {
        initialize: function () {
            this.countries = window.checkoutConfig.bold.countries;
        },

        /**
         * Convert address to Bold API format.
         *
         * @return object
         */
        getBillingAddress: function () {
            const billingAddress = registry.get('index = billingAddress');
            if (billingAddress && !billingAddress.validate()) {
                return null;
            }
            const address = quote.billingAddress();
            if (!address) {
                return null;
            }
            const payload = {
               'address' : {
                   'id': address.customerAddressId ? Number(address.customerAddressId) : null,
                   'company': address.company ? address.company : '',
                   'country_id': address.countryId,
                   'city': address.city ? address.city : '',
                   'firstname': address.firstname ? address.firstname : '',
                   'lastname': address.lastname ? address.lastname : '',
                   'telephone': address.telephone ? address.telephone : '',
                   'postcode': address.postcode ? address.postcode : '',
                   'region': address.region ? address.region : '',
                   'region_id': address.regionId ? Number(address.regionId) : null,
                   'street': address.street,
               }
            };
            try {
                this.validateAddress(payload.address);
            } catch (e) {
                return null;
            }
            return payload;
        },

        /**
         * Validate address payload.
         *
         * @param payload object
         * @return void
         * @throws Error
         * @private
         */
        validateAddress(payload) {
            let requiredFields = [
                'firstname',
                'lastname',
                'postcode',
                'country_id',
                'street',
                'city',
            ];
            const country = this.countries.find(country => country.value === payload.country_code);
            if (country && country.is_region_visible) {
                requiredFields.push('region');
                requiredFields.push('region_id');
            }
            _.each(requiredFields, function (field) {
                if (!payload[field]) {
                    throw new Error('Missing required field: ' + field);
                }
            })
        },
    }

    boldAddress.initialize();
    return boldAddress;
});
