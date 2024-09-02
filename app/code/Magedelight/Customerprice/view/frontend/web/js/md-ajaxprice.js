define([
    "jquery",
    'mage/url'
], function ($,url) {
    'use strict';
    url.setBaseUrl(BASE_URL);

    $.widget('mage.mdAjaxPrice', {

    	mdoptions: {
            fields: {
            	productContainer : "ol.product-items li.product-item"
            }
        },

    	_create: function () {
            this.initObservable();
        },

        initObservable: function () {

        	var self = this;

        	var listItems = $(self.mdoptions.fields.productContainer);
			listItems.each(function(idx, li) {

				var productId = $(li).find('.product-item-info').attr('id');
				if(productId){
			    	productId = productId.replace("product-item-info_", "");
			    	var urlPath = url.build('md_customerprice/index/customerprice');
			    	$(".price-loader-"+productId).show();
				    $.ajax({
		                /*showLoader: true,*/
		                url: urlPath,
		                type: "POST",
		                data:{
		                    product_id: productId,
		                },
		                dataType: "json",
		                success: function (data) {
		                    if (data.status) {
		                        $("#product-item-info_"+productId+" .price-box").html(data.message);
		                    }
		                    $(".price-loader-"+productId).hide(); 
		                },
		                error: function (error) {
		                    console.log(error);
		                    $(".price-loader-"+productId).hide();
		                }
	                });
	            }else{
	            	$(li).find(".price-loader").hide();
	            } 
			});
        }
    });
    return $.mage.mdAjaxPrice;
});