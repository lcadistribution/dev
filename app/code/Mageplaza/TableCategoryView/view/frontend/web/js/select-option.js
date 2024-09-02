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
    'Mageplaza_TableCategoryView/js/get-option-value'
], function ($, $t, _, OptionHelper) {
    'use strict';

    $.widget('mageplaza.mpTableCategorySelect', {
            options: {
                optionsValue: []
            },
            _create: function () {
                var self = this;

                this.element.on('click', '.mptablecategory-select', function () {
                    var form      = $(this).parents('#product_addtocart_form'),
                        productID = form.find('input[name="product"]').val(),
                        clickEl   = $('.product-item-info[data-productid="'
                            + productID + '"] .mptablecategory-popup-click'),
                        checkEL   = $('.product-item-info[data-productid="'
                            + productID + '"] .mptablecategory-checkbox'),
                        configs   = self.options.optionsValue,
                        htmlPopup = $('#mptablecategory-popup-' + productID),
                        html;

                    if (!self._checkOptions(form)) {
                        return false;
                    }

                    html = OptionHelper.html(form, configs);

                    if (clickEl.parent().find('.mptablecategoryview-list-options').length > 0) {
                        clickEl.parent().find('.mptablecategoryview-list-options').remove();
                    }

                    if (clickEl.parent().find('ul.bundle.items').length > 0) {
                        clickEl.parent().find('ul.bundle.items').remove();
                    }
                    clickEl.parent().find('.mptcv-options-hide').remove();
                    clickEl.parent().append(html.html + html.inputHtml);
                    _.each(html.files, function (file) {
                        clickEl.parent().find('.mptcv-options-hide').append(file);
                    });

                    if (!clickEl.hasClass('mp-edited')) {
                        clickEl.addClass('mp-edited');
                        clickEl.text($t('Modifier Options'));
                    }

                    form.find('.swatch-opt .message-error.error.message').remove();

                    if (checkEL.length > 0) {
                        checkEL.prop('checked', true);
                    }

                    htmlPopup.data('mageModal').closeModal();

                    $('html, body').animate({
                        scrollTop: $('.product-item-info[data-productid="' + productID + '"]').offset().top-150
                    }, 500);

                });

                this.element.on('click', '#bundle-slide', function () {
                    self.element.find('.box-tocart').appendTo(self.element.find('#product_addtocart_form'));
                });
            },

            _checkOptions: function (form) {
                var status  = true,
                    htmlEr  = '';

                form.validation({meta: 'validate'});
                if (!form.valid()) {
                    return false;
                }

                if (form.find('#mpcpgv-attribute-table').length > 0) {
                    status = false;
                    _.each(form.find('#mpcpgv-attribute-table tbody .mpcpgv-input'), function (input) {
                        if (Number($(input).val()) > 0) {
                            status = true;
                        }
                    });

                    if (!status) {
                        form.find('.swatch-opt .message-error.error.message').remove();
                        htmlEr = '<div class="message-error error message">'
                            + $t("Please choose a product") + '</div>';
                        $('.swatch-opt').append(htmlEr);
                    }
                }

                return status;
            }
        }
    );

    return $.mageplaza.mpTableCategorySelect;
});