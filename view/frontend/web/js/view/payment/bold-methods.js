define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'bold',
                component: 'Bold_Checkout/js/view/payment/method-renderer/bold-method'
            }
        );
        return Component.extend({});
    }
);
