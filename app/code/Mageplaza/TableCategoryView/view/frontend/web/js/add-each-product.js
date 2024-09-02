/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_TableCategoryView
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'mage/translate',
    'underscore',
    'Magento_Customer/js/customer-data',
    'Mageplaza_TableCategoryView/js/get-option-value'
], function ($, $t, _, CustomerData, OptionHelper) {
    'use strict';

    $.widget('mageplaza.addEachProductToCart', {
            options: {
                url: '',
                storeId: 0,
                optionsValue: '',
                mpUrl: ''
            },
            _create: function () {
                var self = this;

                self._addEachClick(self);

            },

            _addErrorNotify: function (productEL, notify) {
                productEL.next('.mp-notice-messages').find('td').html(
                    '<div class="message-error error message"><div>'
                    + $t(notify)
                    + '</div></div>'
                );
                setTimeout(function () {
                    $('.mp-notice-messages td').html('');
                }, 3000);
            },

            _addEachClick: function (self) {
                this.element.find('button.mptablecategory-add-each').on('click', function (e) {
                    var $this       = $(this),
                        buttonText  = $this.text(),
                        productEL   = $this.parents('.product-item-info'),
                        productId   = productEL.data().productid,
                        qty         = Number(productEL.find('.mptablecategory-product-qty input').val()),
                        type        = productEL.find('.mptablecategory-product-type').val(),
                        url         = self.options.url,
                        storeId     = self.options.storeId,
                        configs     = self.options.optionsValue,
                        form        = $('#mptablecategory-popup-' + productId + ' form'),
                        productForm = productEL.find('form.mp-product-options'),
                        formData    = new FormData();

                    $this.text('Adding');
                    $this.attr('disabled', 'disabled');

                    if (type === 'grouped' || type === 'downloadable' || qty > 0 || !_.isNaN(qty)) {
                        formData.set('storeId', storeId);

                        if (productEL.find('.mptablecategory-popup-click').hasClass('mp-edited')) {

                            if (form.find('#mpcpgv-attribute-table').length > 0) {
                                self._setPopupFormData(productEL, formData, qty);
                                url = self.options.mpUrl;
                            } else {
                                self._setPopupFormData(productEL, formData, qty);
                                formData.set('productId', productId);
                                formData.set('qty', qty);
                            }
                        } else {
                            e.preventDefault();
                            e.stopPropagation();

                            productForm.validation({validate:{meta: 'validate'}});
                            if (productForm.length > 0 && productForm.valid()){
                                formData.set('product_params', productEL.find('form.mp-product-options').serialize());

                                _.each(
                                    productEL.find('#product-options-wrapper .fieldset>.field'),
                                    function (optionEL) {
                                        var optionValue = OptionHelper.options(configs, optionEL),
                                            fileInputEL = '';

                                        if (optionValue.type === 'file' && optionValue.value !== '') {
                                            fileInputEL = $(optionEL).find('input[type="file"]');
                                            formData.set(fileInputEL.attr('name'), fileInputEL[0].files[0]
                                                , optionValue.value);
                                        }
                                    });
                            }

                            formData.set('productId', productId);
                            formData.set('qty', qty);
                        }

                        $.ajax({
                            url: url,
                            type: "post",
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false,
                            showLoader: true,
                            success: function (res) {
                                CustomerData.reload(['cart'], false);
                                $this.text($t('Added'));
                                if (res.status === 0) {
                                    self._addErrorNotify(productEL, res.notify);
                                }else{
                                    $('html, body').animate({
                                        scrollTop: $('body').offset().top
                                    }, 500);
                                }
                            },
                            complete: function () {
                                $this.removeAttr('disabled');
                                $this.text(buttonText);
                            }
                        });
                    } else {
                        self._addErrorNotify(productEL, $t('Qty must have a value greater than 0.'));
                        $this.removeAttr('disabled');
                        $this.text(buttonText);
                    }
                });
            },

            _setPopupFormData: function (productEL, formData, parentQty) {
                _.each(productEL.find('.mptcv-options-hide input'), function (input) {
                    var inputEL = $(input);

                    if (inputEL.data('id') === 'null') {
                        return;
                    }

                    if (inputEL.hasClass('mpcpgv')) {
                        formData.set('items[' + inputEL.data('id') + '][id]', inputEL.data('id'));
                        formData.set('items[' + inputEL.data('id') + '][qty]', Number(inputEL.data('qty')) * parentQty);
                    }

                    if (inputEL.hasClass('mpcpgv-items')) {
                        formData.set('mpcpgvItems', inputEL.val());
                    }

                    if (inputEL.hasClass('product_params')) {
                        formData.set('product_params', inputEL.data('value'));
                    }
                    if (inputEL.hasClass('product-custom-option')) {
                        formData.set(inputEL.attr('name'), input.files[0], input.files[0].name);
                    }
                });
            }
        }
    );

    return $.mageplaza.addEachProductToCart;
});
