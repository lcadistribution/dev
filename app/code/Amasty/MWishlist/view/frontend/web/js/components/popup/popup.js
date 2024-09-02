/**
 *  Amasty Popup UI Component
 *
 *  @desc Popup Component for Multiple Wishlist Module
 *
 *  @copyright 2009-2020 Amasty Ltd
 *  @license   https://amasty.com/license.html
 */

define([
    'jquery',
    'ko',
    'uiComponent',
], function ($, ko, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            selectors: {
                body: 'body',
                wrapper: '[data-ampopup-js="popup"]',
            },
            classes: {
                active: '-active',
                openPopup: '-popup-opened',
            },
        },
        nodes: {},

        /**
         * Amasty Popup Ui Component Init
         */
        initialize: function () {
            var self = this;

            self._super();

            self.nodes.body = $(self.selectors.body);
            self.nodes.wrapper = $(self.selectors.wrapper);
            self.nodes.wrapper.click(function (event) {
                if (self.nodes.wrapper.is(event.target)) {
                    self.hide();
                }
            });

            self.closeButton(true);
        },

        /**
         * Init Observes
         */
        initObservable: function () {
            return this._super().observe([
                'closeButton',
                'isActive',
                'header',
                'contentTmpl',
                'description',
                'buttons',
                'type'
            ]);
        },

        /**
         * Amasty Popup Show method
         */
        show: function () {
            var self = this;

            self.isActive(true);
            self.nodes.wrapper.addClass(self.classes.active);
            self.nodes.body.addClass(self.classes.openPopup);
        },

        /**
         * Amasty Popup Hide method
         */
        hide: function () {
            var self = this;

            self.isActive(false);
            self._clear();
            self.nodes.wrapper.removeClass(self.classes.active);
            self.nodes.body.removeClass(self.classes.openPopup);
        },

        /**
         * Amasty Popup Clear method
         */
        _clear: function () {
            var self = this;

            self.header(false);
            self.description(false);
            self.contentTmpl(false);
            self.buttons([]);
            self.type(false);
        },
    });
});
