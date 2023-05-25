define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/quote',
        'jquery',
        'ko'
    ], function (Component, customerData, quote, $, ko) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Bold_Checkout/payment/bold.html',
            },
            initialize: function () {
                this._super();
                this.isAvailable = ko.observable(false);
                this.errorMessage = ko.observable('Something went wrong.');
                this.iframeSrc = ko.observable(window.checkoutConfig.bold.payment.iframeSrc);
                this.iframeHeight = ko.observable('350px');
                this.sendBillingData();
                this.getIframeHeight();
            },
            getIframeHeight: function () {
                const self = this;
                window.addEventListener('message', ({data}) => {
                    const newHeight = data.height ? data.height.round() + 'px' : self.iframeHeight();
                    self.iframeHeight(newHeight);
                });
            },
            sendBillingData: function () {
                const billingAddress = quote.billingAddress();
                if (window.checkoutConfig.bold.customerIsGuest && !quote.guestEmail) {
                    this.errorMessage('Please provide your email address.');
                }
                if (!billingAddress.firstname ) {
                    this.errorMessage('Please provide your first name.');
                    this.isAvailable(false);
                    return;
                }
                if (!billingAddress.lastname ) {
                    this.errorMessage('Please provide your last name.');
                    this.isAvailable(false);
                    return;
                }
                if (window.checkoutConfig.bold.customerIsGuest) {
                    this.sendGuestCustomerInfo();
                }
                this.sendBillingAddress();
            },
            placeOrder: function (data, event) {
                this._super(data, event);
            },
            sendGuestCustomerInfo: function () {
                const billingAddress = quote.billingAddress();
                $.ajax({
                    context: this,
                    url: window.checkoutConfig.bold.guestUrl,
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + window.checkoutConfig.bold.jwtToken,
                        'Content-Type': 'application/json'
                    },
                    data: JSON.stringify(
                        {
                            'email_address': quote.guestEmail,
                            'first_name': billingAddress.firstname,
                            'last_name': billingAddress.lastname,
                        }
                    )
                }).done(function (response) {
                    this.isAvailable(true);
                    console.log(response);
                }).fail(function (response) {
                    this.isAvailable(false);
                    console.log(response);
                });
            },
            sendBillingAddress: function () {
                const billingAddress = quote.billingAddress();
                const countryId = billingAddress.countryId;
                const countryData = customerData.get('directory-data');
                const countryName = countryData()[countryId] !== undefined ? countryData()[countryId].name : '';
                $.ajax({
                    context: this,
                    url: window.checkoutConfig.bold.billingAddressUrl,
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + window.checkoutConfig.bold.jwtToken,
                        'Content-Type': 'application/json'
                    },
                    data: JSON.stringify(
                        {
                            'business_name': billingAddress.company ? billingAddress.company : '',
                            'country_code': countryId,
                            'country': countryName,
                            'city': billingAddress.city ? billingAddress.city : '',
                            'first_name': billingAddress.firstname ? billingAddress.firstname : '',
                            'last_name': billingAddress.lastname ? billingAddress.lastname : '',
                            'phone_number': billingAddress.telephone ? billingAddress.telephone : '',
                            'postal_code': billingAddress.postcode ? billingAddress.postcode : '',
                            'province': billingAddress.region ? billingAddress.region : '',
                            'province_code': billingAddress.regionCode ? billingAddress.regionCode : '',
                            'address_line_1': billingAddress.street !== undefined && billingAddress.street[0] ? billingAddress.street[0] : '',
                            'address_line_2': billingAddress.street !== undefined && billingAddress.street[1] ? billingAddress.street[1] : '',
                        }
                    )
                }).done(function (response) {
                    this.isAvailable(true);
                    console.log(response);
                }).fail(function (response) {
                    this.isAvailable(false);
                    console.log(response);
                });
            },
        });
    });
