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
    'Mageplaza_TableCategoryView/js/get-option-value',
    'Magento_Ui/js/modal/modal',
    'mage/url',
    'Magento_Ui/js/modal/alert'
], function ($, $t, _, CustomerData, OptionHelper, modal, urlBuilder, alert) {
    'use strict';

    var bodyEl = $('body');

    $.widget('mageplaza.mptcvAddAllToCart', {
            options: {
                url: '',
                storeId: 0,
                optionsValue: {},
                isLogin: false
            },
            _create: function () {
                var self = this;

                this._addAllToCart(self);
                this._addToQuote(self);
                this._changeButtonCallForPrice();
                this._popupAction();
                this.checkQtyInputObs();
            },
            checkQtyInputObs: function () {
                $('body').on('change', '.mptablecategory-qty-input', function () {
                    $(this).val(!isNaN(Number($(this).val())) ? Number($(this).val()) : 1);
                    if ($(this).val() < 0) {
                        $(this).val('1');
                    }
                });
            },

            _changeButtonCallForPrice: function () {
                var productEL = $('.product-item-info');

                _.each(productEL, function (element) {
                    var callForPrice = $(element).find('div.callforprice-action'),
                        action       = $(element).find('td.mptablecategory-add-to-cart-button');

                    callForPrice.prependTo(action);
                });
            },

            _addToQuote: function (self) {
                $('.action.toquote').on('click', function (e) {
                    var $this       = $(this),
                        productEL   = $this.parents('.product-item-info'),
                        productId   = productEL.data().productid,
                        qty         = Number(productEL.find('.mptablecategory-product-qty input').val()),
                        type        = productEL.find('.mptablecategory-product-type').val(),
                        configs     = self.options.optionsValue,
                        form        = $('#mptablecategory-popup-' + productId + ' form'),
                        productForm = productEL.find('form.mp-product-options'),
                        url         = self.options.urlToQuote + 'product/' + productId,
                        formKey     = $('input[name="form_key"]').val(),
                        formData    = new FormData();

                    if (!self.options.isLogin) {
                        e.preventDefault();

                        alert({
                            title: '',
                            content: $t('Please log-in to create a quote request.')
                        });

                        return;
                    }

                    if (type === 'grouped' || type === 'downloadable' || qty > 0 || !_.isNaN(qty)) {
                        formData.set('form_key', formKey);
                        formData.set('from_mp_tcv', true);

                        if (productEL.find('.mptablecategory-popup-click').hasClass('mp-edited')) {

                            if (form.find('#mpcpgv-attribute-table').length > 0) {
                                self.setPopupFormData(productEL, formData, qty);
                            } else {
                                self.setPopupFormData(productEL, formData, qty);
                                formData.set('product', productId);
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

                            formData.set('product', productId);
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
                                if (res.error) {
                                    self._addErrorNotify(productEL, res.message);
                                    return;
                                } else {
                                    $('html, body').animate({
                                        scrollTop: $('body').offset().top
                                    }, 500);
                                }
                            }
                        });
                    } else {
                        self._addErrorNotify(productEL, $t('Qty must have a value greater than 0.'));
                    }
                });
            },

            setPopupFormData: function (productEL, formData, parentQty) {
                _.each(productEL.find('.mptcv-options-hide input'), function (input) {
                    var inputEL = $(input);

                    if (inputEL.data('id') === 'null') {
                        return;
                    }

                    if (inputEL.hasClass('mpcpgv')) {
                        formData.set('items[' + inputEL.data('id') + '][id]', inputEL.data('id'));
                        formData.set('items[' + inputEL.data('id') + '][qty]', Number(inputEL.data('qty')) * parentQty);
                    }
                    if (inputEL.hasClass('product_params')) {
                        formData.set('product_params', inputEL.data('value'));
                    }
                    if (inputEL.hasClass('product-custom-option')) {
                        formData.set(inputEL.attr('name'), input.files[0], input.files[0].name);
                    }
                });
            },

            _addAllToCart: function (self) {
                $('.mptablecategory-add-all button').on('click', function () {
                    var $this      = $(this),
                        url        = self.options.url,
                        buttonText = $this.text(),
                        storeId    = self.options.storeId,
                        popupEL    = $('#mptablecategory-add-all-popup'),
                        formData   = new FormData(),
                        isValid    = true;

                    formData.set('storeId', storeId);
                    _.each($('#mptablecategory-table-view .product-item-info'), function (product) {
                        var productEL = $(product),
                            productId = productEL.data().productid,
                            isAdd     = productEL.find('input.mptablecategory-checkbox:checked'),
                            qty       = Number(productEL.find('.mptablecategory-product-qty input').val()),
                            configs   = self.options.optionsValue[productId],
                            form      = productEL.find('form.mp-product-options');

                        if (isAdd.length < 1) {
                            return false;
                        }

                        if (Number(qty) < 1) {
                            isValid = false;
                            self._addErrorNotify(productEL, $t('Qty must have a value greater than 0.'));
                        }

                        $this.text('Adding');
                        $this.attr('disabled', 'disabled');
                        formData.set('products[' + productId + '][qty]', qty);

                        if (productEL.find('.mptablecategory-popup-click').length > 0) {
                            if (productEL.find('.mptablecategory-popup-click').hasClass('mp-edited')) {
                                self._setPopupFormData(productEL, productId, formData, qty);
                            }
                        } else {
                            self._setDataToFormData(productEL, productId, configs, formData, form);
                        }
                    });
                    if (isValid) {
                        $.ajax({
                            url: url,
                            type: "post",
                            data: formData,
                            showLoader: true,
                            cache: false,
                            contentType: false,
                            processData: false,
                            success: function (html) {
                                var options = {
                                    'type': 'popup',
                                    'title': $t('Vos achats'),
                                    'responsive': true,
                                    'innerScroll': true,
                                    'buttons': []
                                };

                                if (html === '') {
                                    return false;
                                }
                                CustomerData.reload(['cart'], false);
                                $this.text($t('Added'));
                                popupEL.html(html);
                                if (popupEL.data('mageModal')) {
                                    popupEL.data('mageModal').openModal();
                                } else {
                                    modal(options, popupEL).openModal();
                                }
                            },
                            complete: function () {
                                $this.removeAttr('disabled');
                                $this.text(buttonText);
                            }
                        });
                    } else {

                        $this.removeAttr('disabled');
                        $this.text(buttonText);
                    }
                });
            },

            _popupAction: function () {
                var popupEL = $('#mptablecategory-add-all-popup');

                bodyEl.on('click', 'button.mptcv-button-continue', function () {
                    popupEL.data('mageModal').closeModal();
                });
                bodyEl.on('click', 'button.mptcv-button-cart', function () {
                    window.location.href = urlBuilder.build('checkout/cart');
                });
            },
            _setPopupFormData: function (productEL, productId, formData, parentQty) {
                _.each(productEL.find('.mptcv-options-hide input'), function (input) {
                    var inputEL      = $(input),
                        attributeArr = '';

                    if (inputEL.data('id') === 'null') {
                        return;
                    }

                    if (inputEL.hasClass('mpcpgv')) {
                        attributeArr = inputEL.data('attribute').split(',');

                        formData.set('products[' + productId + '][mp_cpgv]['
                            + inputEL.data('id') + '][qty]', inputEL.data('qty'));
                        formData.set('products[' + productId + '][mp_cpgv]['
                            + inputEL.data('id') + '][super_attribute][' + attributeArr[0] + ']', attributeArr[1]);
                    }
                    if (inputEL.hasClass('product_params')) {
                        formData.set('products[' + productId + '][product_params]', inputEL.data('value'));
                    }
                    if (inputEL.hasClass('product-custom-option')) {
                        formData.set(inputEL.attr('name'), input.files[0], input.files[0].name);
                    }
                    formData.set('products[' + productId + '][qty]', parentQty);
                });
                formData.set('products[' + productId + '][html]',
                    productEL.find('.mptablecategoryview-list-options').html());
            },
            _setDataToFormData: function (parentEL, productId, configs, formData, form) {
                form.validation({validate: {meta: 'validate'}});

                _.each(parentEL.find('#mpcpgv-simple-product .mpcpgv-simple'), function (product) {
                    var productEL  = $(product),
                        attributes = productEL.find('.swatch-option');

                    formData.set('products[' + productId + '][mp_cpgv][' + productEL.attr('product-id') + '][qty]'
                        , productEL.find('.mpcpgv-qty').text());
                    _.each(attributes, function (attribute) {
                        var attributeArr = $(attribute).attr('id').split('-');

                        formData.set('products[' + productId + '][mp_cpgv][' + productEL.attr('product-id')
                            + '][super_attribute][' + attributeArr[3] + ']', attributeArr[5]);
                    });
                });

                if (form.length > 0 && form.valid()) {
                    _.each(parentEL.find('#product-options-wrapper .fieldset>.field'), function (optionEL) {
                        var optionValue = OptionHelper.options(configs, optionEL),
                            fileInputEL = '';

                        if (optionValue.status) {
                            if (optionValue.type === 'file') {
                                fileInputEL = $(optionEL).find('input[type="file"]');
                                formData.set(fileInputEL.attr('name'), fileInputEL[0].files[0], optionValue.value);
                            }
                        }
                    });
                    formData.set('products[' + productId + '][product_params]', form.serialize());
                }


                formData.set('products[' + productId + '][html]', OptionHelper.html(parentEL, configs).html);
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
            }
        }
    );

    return $.mageplaza.mptcvAddAllToCart;
});
