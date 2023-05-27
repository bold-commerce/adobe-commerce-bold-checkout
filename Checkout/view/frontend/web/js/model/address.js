define([
    'underscore',
], function (_) {
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
         * @param address object
         * @param type string
         * @return object
         */
        convertAddress: function (address, type) {
            const countryId = address.countryId;
            const country = this.countries.find(country => country.value === countryId);
            const countryName = country ? country.label : null;
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
            this.validatePayload(payload, type);
            return payload;
        },

        /**
         * Validate address payload.
         *
         * @param payload object
         * @param type string
         * @return void
         * @throws Error
         * @private
         */
        validatePayload(payload, type) {
            let requiredFields = {
                'first_name': 'Please provide your ' + type +' address first name.',
                'last_name': 'Please provide your ' + type +' address last name.',
                'postal_code': 'Please provide your ' + type +' address postal code.',
                'phone_number': 'Please provide your ' + type +' address phone number.',
                'country': 'Please select your ' + type +' address country.',
                'city': 'Please provide your ' + type +' address city.',
                'address_line_1': 'Please provide your ' + type +' address address.',
            }
            const country = this.countries.find(country => country.value === payload.country_code);
            if (country && country.is_region_visible) {
                requiredFields.province = 'Please select your ' + type +' address province.';
                requiredFields.province_code = 'Please select your ' + type +' address province.';
            }
            _.each(requiredFields, function (message, field) {
                if (!payload[field]) {
                    throw new Error(message);
                }
            })
        },
    }

    boldAddress.initialize();
    return boldAddress;
});
