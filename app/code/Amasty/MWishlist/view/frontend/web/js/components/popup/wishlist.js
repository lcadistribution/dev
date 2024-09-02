/**
 *  Amasty Wishlist for Popup UI Component
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'rjsResolver',
    'Magento_Customer/js/customer-data',
    'amListName',
    'uiRegistry'
], function ($, ko, Component, resolver, customerData, listName) {
    'use strict';

    return Component.extend({
        defaults: {
            validateTimeOut: 1000,
            selectors: {
                formKeyInputSelector: 'input[name="form_key"]'
            },
            actions: {
                addNewList: '/mwishlist/wishlist/create',
                validateNewName: '/mwishlist/wishlist/validateWishlistName'
            },
            modules: {
                popup: 'ampopup'
            },
            typesMap: [],
            excludeIds: [],
            isNameValid: false,
            currentListType: 0
        },

        /**
         * Clearing state for new list area
         *
         * @return {void}
         */
        clearNewList: function () {
            this.newListActive(false);
            this.newListName(null);
            this.newNameErrors(false);
            this.isNameValid(true);
        },

        /**
         * @inheritDoc
         */
        initObservable: function () {
            var self = this;

            this._super().observe([
                'currentListType',
                'currentListId',
                'newListActive',
                'newListName',
                'newNameErrors',
                'isNameValid',
                'tabs',
                'excludeIds'
            ]);

            resolver(function () {
                self.tabs(self._getWishlistSection()()['wishlist_list'] || {});

                self.newListName.extend({
                    rateLimit: {
                        method: 'notifyWhenChangesStop', timeout: self.validateTimeOut
                    }
                });

                self.popup().isActive.subscribe(function (value) {
                    if (!value) {
                        self.clearNewList();
                    }
                });
            });

            self._getWishlistSection().subscribe(function (value) {
                self.tabs(value['wishlist_list']);
            }, self);

            self.newListName.subscribe(function (value) {
                var successAction = function () {
                        self.newNameErrors(false);
                        self.isNameValid(true);
                    },
                    errorAction = function (response) {
                        self.newNameErrors(response.errors);
                        self.isNameValid(false);
                    };

                if (value) {
                    listName.validate(value, successAction, errorAction);
                }
            }, self);

            return self;
        },

        /**
         * Adding new wishlist with new list name
         *
         * @return {void}
         */
        addNewList: function () {
            var self = this,
                data = {
                    'wishlist[name]': self.newListName(),
                    'wishlist[type]': self.currentListType()
                },
                successAction = function () {
                    self.clearNewList();
                };

            listName.ajaxAction(this.actions.addNewList, data, successAction);
        },

        /**
         * Bundle State for wishlist visibility
         *
         * @param {Number} wishlistId - id of wishlist
         * @return {Boolean}
         */
        isWishlistVisible: function (wishlistId) {
            return this.excludeIds().indexOf(wishlistId) === -1;
        },

        /**
         * Take label for list type
         *
         * @param {Number} index - url to controller
         * @return {String}
         */
        getTypeLabel: function (index) {
            return this.typesMap[index];
        },

        /**
         * @return {Object}
         */
        _getWishlistSection: function () {
            return customerData.get('mwishlist');
        }
    });
});
