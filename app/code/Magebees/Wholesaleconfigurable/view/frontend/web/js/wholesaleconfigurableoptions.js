define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'ko',
    'jquery/ui',
    'mage/validation/validation'
    
], function ($,$t,modal,ko) {
    "use strict";

    $.widget('magebees_stock.wholesaleconfigurableoptions', {
    
        _create: function () {
            $("#loadingImage").insertBefore(".page-header");
        },
		productOptions: function (actionUrl) {
            var self = this;
            $.ajax({
                url: actionUrl,
                dataType: 'json',
                success: function (result) {
                    document.getElementById('loadingImage').style['display']='none';
                    if (result.product_detail) {
                        $('body').append('<div id="product_options_content"></div>');
                        self.popupModal(result);
                    }
                    
                
                }
            });
        },
        
        popupModal: function (result) {
            var self = this,
                modelClass = "stockDetails";
                if (result.product_detail) {
                    modelClass = "stockDetails viewBox";
                }
                
            var options =
            {
                type: 'popup',
                modalClass: modelClass,
                responsive: true,
                innerScroll: true,
                title: false,
                buttons: false
            };

            if (result.product_detail) {
                var popup = modal(options, $('#product_options_content'));
                $('#product_options_content').html(result.product_detail);
                $('#product_options_content').trigger('contentUpdated');
                $('#product_options_content').modal('openModal');
                
                
                 $('body').on('click','#product_options_content .action-close',function () {
                       $('#product_options_content').modal('closeModal');
                       $('#product_options_content').remove();
                    });
            }
        }
    });

    return $.magebees_stock.wholesaleconfigurableoptions;
});

