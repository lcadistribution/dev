define([
    "jquery",
    'mage/translate',
    'underscore',
    'Magento_Ui/js/modal/modal',
    'mage/url',
    'jquery/ui'
], function ($,$t, _,modal,url) {
    'use strict';
    url.setBaseUrl(BASE_URL);
    $.widget('mage.mdFormPopup', {

    	mdoptions: {
            fields: {
                popupContainer: '[data-md-js="md-specialprice-container"]',
                askPriceButton: '.special-price-button',
                form:'#specialprice-form',
            },
            getFormUrl:url.build('md_customerprice/customerspecialprice/specialpriceform')
        },

        _create: function () {
            this.initObservable();
        },

        initObservable: function () {

            if($('.md-tooltip').length){
                $('[data-toggle="tooltip"]').tooltip(); 
            }
            
        	var self = this;

            /* ask price button*/
            $(self.mdoptions.fields.askPriceButton).on('click', function (event) {
                var pid = $(this).data('product-id');
                $.ajax({
                    context: '#ajaxresponse',
                    url: self.mdoptions.getFormUrl,
                    type: "POST",
                    data: {pid:$(this).data('product-id')},
                    showLoader: true,
                }).done(function (data) {
                    $(self.mdoptions.fields.popupContainer).html(data.output);
                    $("#pid").val(pid);
                    self.openPopupModal();
                    event.preventDefault();
                    return true;
                });
            });

            /* ajax form submit */
             $(document).on("submit", self.mdoptions.fields.form, function (event) {
                event.preventDefault();
                var form = $(this);
                if (form.validation() && form.validation('isValid') === true) {
                    form.find('button.action.primary').prop('disabled', true);
                    self.submitFormWithAjax(form);
                }
                
                return false;
            });
        },

        openPopupModal: function () {


           	var mdoptions = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                buttons: false,
                modalClass: 'md-popup'
            };
            $(this.mdoptions.fields.popupContainer).modal(mdoptions).modal('openModal');  
        },

         submitFormWithAjax: function (form) {
            var self = this;
            $.ajax({
                url: form.attr('action'),
                data: form.serialize(),
                type: 'post',
                dataType: 'json',
                showLoader: true,
                success: function (response) {
                    $('.md-success').html();
                    if (response.status) {
                        $('.md-success').html(response.message);
                        setTimeout(function () {
                            $(self.mdoptions.fields.popupContainer).modal("closeModal");
                        }, 3000);
                    }else{
                        $('.md-error').html(response.message);
                    }
                },
                error: function () {
                    $('.md-error').html();
                    $('.md-error').html($t('Sorry, an unspecified error occurred. Please try again.'));
                    setTimeout(function () {
                       $(self.mdoptions.fields.popupContainer).modal("closeModal");
                    }, 3000);
                }
            });
        }
    });
    return $.mage.mdFormPopup;
});