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
         * @return object
         */
        convertAddress: function (address) {
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
                'address_line_1',
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
