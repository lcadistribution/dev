define([
    'jquery'
], function ($) {
    'use strict'; // eslint-disable-line

    var mixin = {
        isWishlistAjax: function () {
            return this.source().data['isWishlistAjax'] ?? false;
        },

        isMWishlistEnabled: function () {
            return this.source().data['isMWishlistEnabled'] ?? false;
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
