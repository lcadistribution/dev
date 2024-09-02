/**
 *  Amasty Search UI Component
 *
 *  @desc Search Component
 *
 *  @copyright 2009-2020 Amasty Ltd
 *  @license   https://amasty.com/license.html
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'mage/url',
    'rjsResolver',
    'Amasty_MWishlist/js/action/reload-blocks',
    'Amasty_MWishlist/js/action/reload-components'
], function ($, ko, Component, urlBuilder, resolver, reloadBlocks, reloadComponents) {
    'use strict';

    return Component.extend({
        defaults: {
            wishlist_id: null,
            minChars: 2,
            maxChars: 128,
            requestTimeout: 800,
            searchUrl: urlBuilder.build('/mwishlist/product/search'),
            addUrl: urlBuilder.build('/mwishlist/item/add'),
            readyForRequest: true,
            request: null,
            timeOut: null,
            isEmpty: ko.observable(false),
            isEmptyInput: ko.observable(true)
        },
        selectors: {
            qty: '[data-amwishlist-js="input"]',
            search: '[data-amwishlist-js="search-wrapper"]',
            input: '[data-amwishlist-js="search-input"]',
            formKeyInputSelector: 'input[name="form_key"]'
        },
        nodes: {},

        /**
         * Initializes component
         */
        initialize: function () {
            var self = this;

            self._super();

            resolver(function () {
                self.nodes.wrapper = $(self.selectors.search);
                self.nodes.input = self.nodes.wrapper.find(self.selectors.input);

                self.nodes.wrapper.on('keydown', function (event) {
                    if (event.keyCode == 13) {
                        event.preventDefault();

                        self.search();
                    }
                });
            });

            $(window).on('click', function (event) {
                if (self.isOutsideClick(event, $(self.selectors.search).children())) {
                    self.closeSearchResults();
                }
            });
        },

        /**
         * Return true if click is happened outside of target elements
         *
         * @param {Object} event - listener event
         * @param {Object} target - jQuery element
         * @returns {Boolean}
         */
        isOutsideClick: function (event, target)	{
            var clickedOut = true,
                i;

            for (i = 0; i < target.length; i++)  {
                if (event.target === target[i] || target[i].contains(event.target)) {
                    clickedOut = false;
                }
            }

            return clickedOut;
        },

        /**
         * Init Observes
         */
        initObservable: function () {
            this._super().observe([
                'isSearchActive'
            ]);

            return this
        },

        /**
         * Search request method
         *
         * @desc search by sku or product name and set the received parameters in the template
         */
        search: function () {
            var self = this,
                value = self.nodes.input.val();

            self.elems([]);
            self.isEmpty(false);
            clearTimeout(self.timeOut);

            if (self.request) {
                self.request.abort();
            }

            if (value.length) {
                self.isEmptyInput(false);
            } else {
                self.isEmptyInput(true);
            }

            if (value && value.length >= self.minChars && value.length <= self.maxChars) {
                self.timeOut = setTimeout(function () {
                    self.request = $.ajax({
                        url: self.searchUrl,
                        showLoader: true,
                        data: {
                            form_key: window.FORM_KEY,
                            'q': value
                        },
                        type: 'GET',
                        dataType: 'json',
                        error: function () {
                            self.isEmpty(true);
                        },
                        success: function (item) {
                            self.elems(item.items);

                            if (!item.length) {
                                self.isEmpty(true);
                            }
                        }
                    });
                }, self.requestTimeout);
            }
        },

        /**
         * Clear method
         *
         * @desc Clearing search input and elems
         */
        clear: function () {
            this.isEmptyInput(true);
            this.isEmpty(false);
            this.nodes.input.val('');
            this.elems([]);
        },

        /**
         * close search results method
         *
         * @desc Shrink search field, close dropdown
         */
        closeSearchResults: function () {
            this.isEmptyInput(true);
            this.isEmpty(false);
            this.elems([]);
        },

        /**
         * Add request method
         *
         * @desc adding product to the wishlist through the server
         */
        add: function (elem) {
            var self = this,
                $elem = $(elem);

            $elem
                .attr({
                    'qty': $elem.attr('qty') || 1
                })
                .hide();

            $.ajax({
                url: self.addUrl,
                showLoader: true,
                data: {
                    form_key: $(self.selectors.formKeyInputSelector).val(),
                    'product': elem.id,
                    'qty': elem.qty,
                    'block': 'customer.wishlist',
                    'wishlist_id': self.wishlist_id,
                    'component': 'itemsQty'
                },
                type: 'POST',
                dataType: 'json',
                error: function (result) {
                    console.log(result.errors);
                },
                success: function (result) {
                    if (result.blocks) {
                        reloadBlocks(result.blocks);
                    }
                    if (result.components) {
                        reloadComponents(result.components);
                    }
                }
            });
        }
    });
});
