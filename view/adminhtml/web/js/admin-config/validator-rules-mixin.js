define(['jquery'], function($) {
    'use strict';
    return function(targetWidget) {
        let filedKeyValues = [],
            locationIndexValues = [];
        $.validator.addMethod(
            'field-key-unique',
            function(value, element) {
                const result = !filedKeyValues.includes(value);
                filedKeyValues.push(value);
                if (element.parentElement.parentElement.parentElement.lastElementChild
                    === element.parentElement.parentElement) {
                    filedKeyValues = [];
                }

                return result;
            },
            $.mage.__('Field key value should be unique.')
        );
        $.validator.addMethod(
            'location-index-unique',
            function(value, element) {
                const locationElementName = element.name.replace('order_asc', 'location');
                const locationElement = $(`[name='${locationElementName}']`);
                const locationValue = locationElement.val();
                const combinedValue = locationValue + value;
                const result = !locationIndexValues.includes(combinedValue);
                if (result) {
                    locationIndexValues.push(combinedValue);
                }
                if (element.parentElement.parentElement.parentElement.lastElementChild
                    === element.parentElement.parentElement) {
                    locationIndexValues = [];
                }

                return result;
            },
            $.mage.__('Location and Index value pair should be unique.')

        );

        return targetWidget;
    }
});
