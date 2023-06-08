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
            const countryId = address.countryId;
            const country = this.countries.find(country => country.value === countryId);
            const countryName = country ? country.label : '';
            const payload = {
                'id': address.customerAddressId ? Number(address.customerAddressId) : null,
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
            try {
                this.validatePayload(payload);
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
        validatePayload(payload) {
            let requiredFields = [
                'first_name',
                'last_name',
                'postal_code',
                'phone_number',
                'country',
                'city',
            ];
            const country = this.countries.find(country => country.value === payload.country_code);
            if (country && country.is_region_visible) {
                requiredFields.push('province');
                requiredFields.push('province_code');
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
