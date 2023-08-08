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
         * Get billing address api data.
         *
         * @return object
         */
        getBillingAddress: function () {
            const address = quote.billingAddress();
            if (!address) {
                return null;
            }
            const postCode = registry.get('index = postcode')
            if (postCode && postCode.warn()) {
                return null;
            }
            const countryId = address.countryId;
            const country = this.countries.find(country => country.value === countryId);
            const countryName = country ? country.label : '';
            let street1 = '';
            let street2 = '';
            if (address.street && address.street[0]) {
                street1 = address.street[0];
            }
            if (address.street && address.street[1]) {
                street2 = address.street[1];
            }
            const billing = registry.get('index = billingAddress');
            if (!street1) {
                const street1Field = billing && billing.isAddressSameAsShipping()
                    ? registry.get('dataScope = shippingAddress.street.0')
                    : registry.get('dataScope = billingAddress.street.0');
                if (street1Field) {
                    street1 = street1Field.value();
                }
            }
            if (!street2) {
                const street2Field = billing && billing.isAddressSameAsShipping()
                    ? registry.get('dataScope = shippingAddress.street.1')
                    : registry.get('dataScope = billingAddress.street.1');
                if (street2Field) {
                    street2 = street2Field.value();
                }
            }
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
                'address_line_1': street1,
                'address_line_2': street2,
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
                'phone_number',
                'country',
                'address_line_1',
                'city',
            ];
            const country = this.countries.find(country => country.value === payload.country_code);
            if (country && country.is_region_required) {
                requiredFields.push('province');
                requiredFields.push('province_code');
            }
            if (country && country.is_zipcode_optional !== true) {
                requiredFields.push('postal_code');
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
