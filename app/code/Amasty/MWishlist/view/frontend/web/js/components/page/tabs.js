/**
 *  Amasty Wishlist Tabs Component
 *
 *  @copyright 2009-2020 Amasty Ltd
 *  @license   https://amasty.com/license.html
 */

define([
    'jquery',
], function ($) {
    'use strict';

    $.widget('mage.amPageTabs', {
        options: {
            selectors: {
                tabLabel: '[data-amwishlist-js="tab-label"]',
                contentTabsBlock: '[data-amwishlist-js="content-tabs-block"]',
                contentTabs: '[data-amwishlist-js="content-tab"]',
                contentTabId: '[data-tab-id="%id"]'
            },
        },
        classes: {
            active: '-active',
        },
        nodes: {},

        _create: function () {
            var self = this;

            self.nodes.contentTabsBlock = $(self.options.selectors.contentTabsBlock);
            self.nodes.contentTabs = self.nodes.contentTabsBlock.find(self.options.selectors.contentTabs);

            self.nodes.tabs = self.element.find(self.options.selectors.tabLabel);
            self.nodes.tabs.click(function () {
                self._toggleTab(this);
            });
        },

        /**
         * Toggle Tabs
         *
         * @params {object} item - clicked node element
         * @returns {Void}
         * @private
         */
        _toggleTab: function (item) {
            var targetTabId = item.getAttribute('data-tab-id'),
                targetTab = this.nodes.contentTabs.filter(this.options.selectors.contentTabId.replace('%id', targetTabId));

            this.nodes.tabs.removeClass(this.classes.active);
            $(item).addClass(this.classes.active);

            this.nodes.contentTabs.removeClass(this.classes.active);
            targetTab.addClass(this.classes.active);
        },

        /**
         * Provide current active type id.
         *
         * @returns {number}
         */
        getActiveTabId: function () {
            return +$(this.options.selectors.contentTabs + '.' + this.classes.active).attr('data-tab-id');
        }
    });

    return $.mage.amPageTabs;
});
