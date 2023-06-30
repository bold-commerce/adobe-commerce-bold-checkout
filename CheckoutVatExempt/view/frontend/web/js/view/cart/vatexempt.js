define(
    [
        'ko',
        'uiComponent',
        'underscore',
        'Milople_Vatexempt/js/fancybox/jquery.fancybox',
        'Milople_Vatexempt/js/model/vatexempt',
        'jquery',
        'mage/url',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        "Magento_Checkout/js/model/quote",
        'Magento_Checkout/js/model/resource-url-manager',
    ],
    function (
        ko,
        Component,
        _,
        fancybox,
        vatexempt,
        $,
        mageUrl1,
        urlBuilder,
        storage,
        errorProcessor,
        quote,
        resourceUrlManager
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Bold_CheckoutVatExempt/cart/vatexempt'
            },
            vatStatus: window.vatexemptConfig.vatexemptStatus,
            vatFile: window.vatexemptConfig.vatexemptFile,
            vatApplyTo: window.vatexemptConfig.vatexemptApplyTo,
            vatShowLink: window.vatexemptConfig.vatexemptShowLink,
            vatLinkText: window.vatexemptConfig.vatexemptLinkText,
            vatTermsandconditions: window.vatexemptConfig.vatexemptTermsandconditions,
            vatProductList: window.vatexemptConfig.vatexemptProductList,
            vatMedicalConditions: window.vatexemptConfig.vatexemptConditions,
            VatStatus: [
                {
                    value: 1,
                    text: 'Yes'
                },
                {
                    value: 0,
                    text: 'No'
                }
            ],
            selectedVatStatus: ko.observable('Please Select'),
            applientName: ko.observable(null),
            selectedReason: ko.observable('Please Select'),
            selectedFile: ko.observable(null),
            agreeTermsandconditions: ko.observable(null),

            /**
             *
             * @returns {*}
             */
            initialize: function () {
                this._super();
                this.selectedVatStatus.subscribe(function (value) {
                    vatexempt.setSelectedVatStatus(value);
                });
                this.applientName.subscribe(function (value) {
                    vatexempt.setApplientName(value);
                });
                this.selectedReason.subscribe(function (value) {
                    vatexempt.setSelectedReason(value);
                });
                this.agreeTermsandconditions.subscribe(function (value) {
                    vatexempt.setAgreeTermsandconditions(value);
                });
                return this;
            },
            showFormPopUp: function () {
                $.fancybox({
                    'type': 'inline',
                    'href': '#dialogContent'
                });
            },
            hideOptions: function () {
                if (vatexempt.getSelectedVatStatus() == '1') {
                    if (vatexempt.getFile() == '0') {
                        jQuery('#file').hide();
                    }
                    $('#applientNameId').show();
                    $('#applientBox').prop('required', true);
                    $('#applientFile').show();
                    $('#file').prop('required', true);
                    $('#vatTermsandconditionsId').show();
                    $('#termCheck').prop('required', true);
                    $("#vatMedicalConditionsId").show();
                    $('#medicalSelect').prop('required', true);
                } else {
                    if (vatexempt.getFile() == '0') {
                        jQuery('#file').hide();
                    }
                    $('#applientNameId').hide();
                    $('#applientFile').hide();
                    $('#applientBox').prop('required', false);
                    $('#applientBox').val('');
                    $("#vatMedicalConditionsId").hide();
                    $('#termCheck').prop('required', false);
                    $('#termCheck').val('');
                    $('#vatTermsandconditionsId').hide();
                    $('#termCheck').attr('checked', false);
                    $('#medicalSelect').prop('required', false);
                }
            },
            /**
             * @returns void
             */
            applyExempt: function () {
                $(document.body).trigger('processStart');
                const selectedStatus = vatexempt.getSelectedVatStatus();
                const applientName = (vatexempt.getApplientName()) ? vatexempt.getApplientName() : null;
                const selectedReason = vatexempt.getSelectedReason();
                const agreeTermsandconditions = vatexempt.getAgreeTermsandconditions();
                const serviceUrl = urlBuilder.createUrl('/vatexempt/setdata', {});
                const payload = {
                    vatexempt: {
                        selectedStatus: selectedStatus,
                        applientName: applientName,
                        selectedReason: selectedReason,
                        selectedFile: null,
                        agreeTermsandconditions: agreeTermsandconditions,
                    }
                };
                storage.post(
                    serviceUrl, JSON.stringify(payload) //, global, contentType
                ).done(
                    function () {
                        storage.get(resourceUrlManager.getUrlForCartTotals(quote), false).done(function (response) {
                            quote.setTotals(response);
                            $(document.body).trigger('processStop');
                            $('#block-shipping').collapsible('deactivate');
                        }).fail(function () {
                            $(document.body).trigger('processStop');
                        });
                    }
                ).fail(
                    function (response) {
                        $(document.body).trigger('processStop');
                        errorProcessor.process(response);
                    }
                );
            }
        });
    }
);
