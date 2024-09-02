/**
 *  Amasty Title UI Component
 *
 *  @desc Page Title Component
 */

define([
    'jquery',
    'underscore',
    'uiComponent',
    'amListName',
    'amWishlistSearch'
], function ($, _, Component, listName, amWishlistSearch) {
    'use strict';

    return Component.extend({
        defaults: {
            defaultWishlist: false,
            errors: [],
            backUrl: '',
            isNameValid: true,
            isOpened: false
        },
        selectors: {
            socials: '[data-amwishlist-js="socials"]'
        },
        shareWindowOptions: 'width=600,height=600,scrollbars=no,resizable=no',

        /**
         * @inheritDoc
         */
        initialize: function () {
            this._super();

            this.hideDropdownEvent();
        },

        /**
         * @inheritDoc
         */
        initObservable: function () {
            var self = this;

            self._super().observe([
                'isEditNameActive',
                'listName',
                'itemsQty',
                'errors',
                'isNameValid',
                'isOpened'
            ]);

            self.defaultName = self.listName().trim();

            self.listName.subscribe(function (value) {
                var successAction = function () {
                        self.errors(false);
                        self.isNameValid(true);
                    },
                    errorAction = function (response) {
                        self.errors(response.errors);
                        self.isNameValid(false);
                    };

                if (value && self.defaultName !== value.trim()) {
                    listName.validate(value, successAction, errorAction);
                }
            }, self);

            return self;
        },

        /**
         * Overlay click handler
         *
         * @returns {void}
         */
        clickOverlay: function () {
            if (!this.listName().length) {
                this.listName(this.defaultName);
                this.isEditNameActive(false);
            } else if (!this.errors().length) {
                this.isEditNameActive(false);
            }
        },

        /**
         * @returns {Boolean}
         */
        isDeleteable: function () {
            return !this.defaultWishlist;
        },

        /**
         * @returns {Boolean}
         */
        isSocials: function () {
            return this.facebook
                || this.twitter
                || this.line
                || this.linkedin
                || this.pinterest
                || this.telegram;
        },

        /**
         * @param {Object} event
         * @returns {void}
         */
        toggleSocials: function (event) {
            if (_.isUndefined(event)) {
                return;
            }

            event.preventDefault();

            this.isOpened(!this.isOpened());
        },

        /**
         * @param {Object} event
         * @param {String} socialUrl
         * @returns {void}
         */
        openShareWindow: function (event, socialUrl) {
            if (_.isUndefined(event)) {
                return;
            }

            event.preventDefault();

            if (event.type === 'readystatechange') {
                return;
            }

            window.open(socialUrl, 'popup', this.shareWindowOptions);
        },

        /**
         * @returns {void}
         */
        hideDropdownEvent: function () {
            var self = this;

            $(window).on('click', function (event) {
                if (amWishlistSearch().isOutsideClick(event, $(self.selectors.socials).children())) {
                    self.isOpened(false);
                }
            });
        }
    });
});
