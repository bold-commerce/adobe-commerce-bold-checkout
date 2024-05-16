define(
    [
        'Magento_Checkout/js/model/new-customer-address',
        'Magento_Customer/js/customer-data'
    ], function (
        NewCustomerAddressModel,
        customerData
    ) {
        'use strict';
        /**
         * Convert bold address to Magento address.
         *
         * @param {Object} boldAddress
         * @return {Object}
         */
        return function (boldAddress) {
            const directoryData = customerData.get('directory-data');
            const regions = directoryData()[boldAddress.country_code].regions;
            let regionId = null;
            let regionName = null;
            if (regions !== undefined) {
                Object.entries(regions).forEach(([key, value]) => {
                    if (value.code === boldAddress.province_code) {
                        regionId = key;
                        regionName = value.name;
                    }
                });
            }
            const convertedAddress = {
                firstname: boldAddress.first_name,
                lastname: boldAddress.last_name,
                street: [boldAddress.address_line_1, boldAddress.address_line_2],
                city: boldAddress.city,
                company: boldAddress.business_name,
                region: {
                    region: regionName,
                    region_code: boldAddress.region || null,
                    region_id: regionId
                },
                region_id: regionId,
                postcode: boldAddress.postal_code,
                country_id: boldAddress.country_code,
                telephone: boldAddress.phone_number,
            };
            return new NewCustomerAddressModel(convertedAddress);
        };
    });
