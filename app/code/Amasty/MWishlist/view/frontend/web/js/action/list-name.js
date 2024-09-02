/**
 *  Amasty List Name UI Component
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return {
        validateTimeOut: 1000,
        selectors: {
            formKeyInputSelector: 'input[name="form_key"]',
        },
        actions: {
            addNewList: '/mwishlist/wishlist/create',
            validateNewName: '/mwishlist/wishlist/validateWishlistName'
        },

        /**
         * Validate new wishlist name with API
         */
        validate: function (name, successAction, errorAction) {
            var self = this,
                data = {
                    'wishlist[name]': name,
                    'custom': true
                };

            _.debounce(self.ajaxAction(self.actions.validateNewName, data, successAction, errorAction, self.validateTimeOut));
        },

        /**
         * Abstract Ajax sending method
         *
         * @param {string} action - url to controller
         * @param {object} additionalData - custom data
         * @param {object} successAction - callback function for success method
         * @param {object} errorAction - callback function for error method
         */
        ajaxAction: function (action, additionalData, successAction, errorAction) {
            var self = this,
                formKey = $(self.selectors.formKeyInputSelector).val(),
                formData = {
                    action: action,
                    data: additionalData
                };

            if (formKey) {
                formData.data['form_key'] = formKey;
            }

            self.request = $.ajax({
                url: formData.action,
                type: 'post',
                dataType: 'json',
                data: formData.data,
                success: function (response) {
                    if (errorAction && response.errors) {
                        errorAction(response);

                        return;
                    }

                    if (successAction) {
                        successAction(response);
                    }
                }
            });
        },
    };
});
