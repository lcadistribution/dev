define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'Magento_Catalog/js/price-utils',
    'ko',
    'jquery/ui',
    'mage/validation/validation'

], function ($, $t, modal, priceUtils, ko) {
    "use strict";

    $.widget('magebees.mbconfigurable_product', {
        options: {
            priceFormat: '',
            actionUrl: ''
        },

        _create: function () {
            var opt = this.options;
            var self = this;

          
            $(document).on("change", '.config_qty', function (e) {
				e.stopImmediatePropagation();
                var qty = parseInt($(this).val());
				
                if (qty > 0) {
	                var els = $(this).closest('tr.cws-config-line'),
                    productid = $(els).find('.productid').val();
					
					$.ajax({
						url : opt.actionUrl,
						data: { qty : qty , productid:productid } ,
						dataType: 'json',
						type: 'get',
						showLoader:false,
						success: function(data){
							var finalPrices = data * qty,
							totalsPriceFomat = self.getFormattedPrice(finalPrices, opt.priceFormat),
							finalcwsprice = self.getFormattedPrice(data, opt.priceFormat);
							$(els).find('.config-subtotal span').html(totalsPriceFomat);
							$(els).find('.finalcwsprice').html(finalcwsprice);

							var sumTotal = 0;
							$(".config-subtotal span").each(function () {
								var sumsTotal = parseFloat($(this).text().replace(/[^\d\.]/g, ''));
								sumTotal += (sumsTotal);
							});
							var grandTotalFomat = self.getFormattedPrice(sumTotal, opt.priceFormat);
							$(".grandtotal span").text(grandTotalFomat);
						
						}
					});
				}
            });


            $(document).on("click", '.config-up-qty-button', function (e) {
				e.stopImmediatePropagation();
                var els = $(this).closest('tr.cws-config-line'),
                    config_qty = $(els).find('.config_qty').val();
				if(!config_qty){
					config_qty = 0;
				}
                
				if (config_qty >= 0) {
                    $(els).find('.config_qty').val(parseInt(config_qty) + 1);
                    var newqty = parseInt(config_qty) + 1,
					productid = $(els).find('.productid').val();

					$.ajax({
						url : opt.actionUrl,
						data: { qty : newqty , productid:productid } ,
						dataType: 'json',
						type: 'get',
						showLoader:false,
						success: function(data){

							var finalPrices = data * newqty,
							totalsPriceFomat = self.getFormattedPrice(finalPrices, opt.priceFormat),
							finalcwsprice = self.getFormattedPrice(data, opt.priceFormat);
							$(els).find('.config-subtotal span').html(totalsPriceFomat);
							$(els).find('.finalcwsprice').html(finalcwsprice);

							var sumTotal = 0;
							$(".config-subtotal span").each(function () {
								var sumsTotal = parseFloat($(this).text().replace(/[^\d\.]/g, ''));
								sumTotal += (sumsTotal);
							});
							var grandTotalFomat = self.getFormattedPrice(sumTotal, opt.priceFormat);
							$(".grandtotal span").text(grandTotalFomat);
								
						}
					});
                }

            });


            $(document).on("click", '.config-down-qty-button', function (e) {
				e.stopImmediatePropagation();
                var els = $(this).closest('tr.cws-config-line'),
                    config_qty = $(els).find('.config_qty').val();
				if(!config_qty){
					config_qty = 1;
				}				
                if (config_qty > 0) {
                    $(els).find('.config_qty').val(parseInt(config_qty) - 1);
                    var newqty = parseInt(config_qty) - 1,
					productid = $(els).find('.productid').val();

					$.ajax({
						url : opt.actionUrl,
						data: { qty : newqty , productid:productid } ,
						dataType: 'json',
						type: 'get',
						showLoader:false,
						success: function(data){

						var finalPrices = data * newqty,
						totalsPriceFomat = self.getFormattedPrice(finalPrices, opt.priceFormat),
						finalcwsprice = self.getFormattedPrice(data, opt.priceFormat);
						$(els).find('.config-subtotal span').html(totalsPriceFomat);
						$(els).find('.finalcwsprice').html(finalcwsprice);

						var sumTotal = 0;
						$(".config-subtotal span").each(function () {
							var sumsTotal = parseFloat($(this).text().replace(/[^\d\.]/g, ''));
							sumTotal += (sumsTotal);
						});
						var grandTotalFomat = self.getFormattedPrice(sumTotal, opt.priceFormat);
						$(".grandtotal span").text(grandTotalFomat);
								
						}
					});		
							
                }

            });
			
			var sumTotal = 0;
			$(".config-subtotal span").each(function () {
				var sumsTotal = parseFloat($(this).text().replace(/[^\d\.]/g, ''));
				sumTotal += (sumsTotal);
			});
			var grandTotalFomat = self.getFormattedPrice(sumTotal, opt.priceFormat);
			$(".grandtotal span").text(grandTotalFomat);

        
		},

        getFormattedPrice: function (price, priceFormat) {
            return priceUtils.formatPrice(price, priceFormat);
        },


    });

    return $.magebees.mbconfigurable_product;
});