/**
 *  Amasty Wishlist List UI Component
 *
 *  @desc Recently edited Wishlist List
 *
 *  @copyright 2009-2020 Amasty Ltd
 *  @license   https://amasty.com/license.html
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'rjsResolver',
], function ($, ko, Component, customerData, resolver) {
    'use strict';

    return Component.extend({
        defaults: {
            list: [],
            maxListQty: 6,
            modules: {
                title: 'ampagetitle'
            }
        },

        /**
         * Initializes component
         */
        initialize: function () {
            var self = this;

            resolver(function () {
                self._initList();
            });

            self._super();
        },

        /**
         * Init Observes
         */
        initObservable: function () {
            this._super().observe([
                'list'
            ]);

            return this
        },

        /**
         * Init Recently List
         */
        _initList: function () {
            var self = this;

            $.each(customerData.get('mwishlist')().recently_list, function (index, item) {
                if (index >= self.maxListQty) {
                    return false;
                }

                if (self.title().listName() === item.name) {
                    return true;
                }

                self.list.push(item);
            });
        },
    });
});
