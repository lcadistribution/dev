define([
    'jquery'
], function ($) {
    'use strict';

    var selectorsMap = {
        'mwishlist.list.contrainer': [
            '[data-amwishlist-js="content-tabs-block"]',
            '[data-amwishlist-js="tabs-block"]'
        ],
        'customer.wishlist': [
            '[data-amwishlist-js="items-block"]',
            '[data-amwishlist-js="pager"]'
        ]
    };

    /**
     * Received selectors from block object
     *
     * @param {string} blockName
     * @return {array}
     */
    function getSelectors(blockName) {
        return selectorsMap[blockName];
    }

    /**
     * Reloading Html blocks which received from backend
     *
     * @param {object} blocks - custom keys which need to reload
     * @return {void}
     */
    return function (blocks) {
        $.each(blocks, function (blockName, blockContent) {
            var newBlockNode = $('<div>').append(blockContent);

            $.each(getSelectors(blockName), function (index, selector) {
                $(selector).html(newBlockNode.find(selector).html());
            });
        });

        $('body').trigger('contentUpdated');
    };
});
