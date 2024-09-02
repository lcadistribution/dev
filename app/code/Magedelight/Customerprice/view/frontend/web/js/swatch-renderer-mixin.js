define(['jquery'],function ($) {
'use strict';

return function (widget) {
    $.widget('mage.SwatchRenderer', widget, {
        _UpdatePrice: function () {
            this._super();
            
            var jsonDate = this.options.jsonConfig.date;
            var date = "";
            var productId = this.getProductId();
            if (productId) {
                date = jsonDate[productId];
            }

            $(".valid-date").remove();

            if (date) {
                $("<span class='valid-date'>"+date+"</span>").insertAfter(".product-info-main .old-price .price-container .price-wrapper");
                $("<span class='valid-date'>"+date+"</span>").insertAfter(this.element.parents('.product-item-info').find('.price-box .price-container .price-wrapper'));   
            }

            this.element.parents('.product-item-info').find('.old-price .valid-date').hide();
        }
    });   
    return $.mage.SwatchRenderer;
};
});