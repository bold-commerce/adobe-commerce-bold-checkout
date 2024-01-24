define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert'
], function ($, modalConfirm, modalAlert) {
    return function (config, element) {
        const url = config.url,
            title = config.title,
            content = config.content,
            successMessage = config.successMessage,
            failedMessage = config.failedMessage,
            clear = () => {
                $(element).prop('disabled', true)
                new Ajax.Request(
                    url, {
                        method: 'POST',
                        parameters: {
                            'form_key': window.FORM_KEY,
                        },
                        onComplete: (transport) => {
                            if (transport.responseText.isJSON()) {
                                const response = transport.responseText.evalJSON();
                                if (response.success) {
                                    modalAlert({
                                        content: successMessage,
                                        actions: {
                                            always: function () {
                                                location.reload();
                                            }
                                        }
                                    });

                                    return;
                                }
                            }
                            modalAlert({
                                content: failedMessage
                            });
                            $(element).prop('disabled', false)
                        }
                    }
                );
            };
        $(element).click(function () {
            modalConfirm({
                title: title,
                content: content,
                actions: {
                    confirm: clear,
                    cancel: () => {
                        // Do nothing
                    }
                }
            });
        });
    }
});
