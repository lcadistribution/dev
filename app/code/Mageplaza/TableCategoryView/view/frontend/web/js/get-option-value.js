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
    'underscore'
], function ($, $t, _) {
    'use strict';

    return {
        options: function (optionsConfig, optionEL) {
            var optionLabel = $(optionEL).find('.label'),
                optionID    = null,
                selectEl    = null,
                radioEl     = null,
                checkboxEls = null,
                textEl      = null,
                inputEl     = null,
                value       = null,
                type        = null,
                checkboxArr = [],
                dateTime    = {},
                label       = {},
                clone       = '',
                status      = true;

            if ($(optionEL).hasClass('date')) {
                // date option
                selectEl = $(optionEL).find('select');
                optionID = selectEl.attr('id').split('_')[1];
                _.each(selectEl, function (select) {
                    dateTime[$(select).data('calendarRole')] = $(select).val();
                    if ($(select).val() === '') {
                        status = false;
                    }
                });
                type  = optionsConfig[optionID].type;
                value = dateTime;
            } else if ($(optionEL).hasClass('option')) {
                type = 'option';
            } else if ($(optionEL).hasClass('downloads')) {
                status      = false;
                checkboxEls = $(optionEL).find('input[type="checkbox"]:checked');

                if (checkboxEls !== undefined && checkboxEls.length > 0) {
                    status = true;
                    _.each(checkboxEls, function (checkboxEl) {
                        if ($(checkboxEl).attr('id') === 'links_all') {
                            return true;
                        }
                        checkboxArr.push($(checkboxEl).val());
                        label[$(checkboxEl).val()] = $($('.label[for="'
                            + $(checkboxEl).attr('id') + '"] span')[0]).text();
                    });
                }
                optionID = 'links';
                type     = 'downloads';
                value    = checkboxArr;
            } else if (optionLabel.attr('for').split('_').length === 2) {
                // option select
                optionID    = optionLabel.attr('for').split('_')[1];
                selectEl    = $(optionEL).find('select');
                radioEl     = $(optionEL).find('input[type="radio"]:checked');
                checkboxEls = $(optionEL).find('input[type="checkbox"]:checked');

                if (
                    selectEl !== undefined && (selectEl.val() === '' || selectEl.val() === null)
                    || radioEl !== undefined && radioEl.val() === ''
                    || checkboxEls === undefined
                ) {
                    status = false;
                }

                if (selectEl.length > 0) {
                    value = selectEl.val();
                }

                if (radioEl.length > 0) {
                    value = radioEl.val();
                }

                if (checkboxEls.length > 0) {
                    _.each(checkboxEls, function (checkboxEl) {
                        checkboxArr.push($(checkboxEl).val());
                    });
                    value = checkboxArr;

                }
                type = optionsConfig[optionID].type;

            } else if (optionLabel.attr('for').split('_')[2] === 'text') {
                // option text
                textEl = $(optionEL).find('input');

                if (textEl.length < 1) {
                    textEl = $(optionEL).find('textarea');
                }
                optionID = textEl.attr('id').split('_')[1];

                if (textEl.val() === '') {
                    status = false;
                }

                type  = optionsConfig[optionID].type;
                value = textEl.val();
            } else if ($(optionEL).find('select').length > 0) {
                type     = 'select';
                optionID = $(optionEL).find('select').val();
                value    = $(optionEL).find('select option[value="'+optionID+'"]').text();
                label    = $(optionEL).find('.label span').text();
            } else {
                // option File
                inputEl  = $(optionEL).find('input[type="file"]');
                optionID = inputEl.attr('name').split('_')[1];
                type     = 'file';
                if (inputEl[0].files.length < 1) {
                    value  = '';
                    status = false;
                }else{
                    value = inputEl[0].files[0].name;
                    clone = inputEl.clone();
                }
            }

            if (value === null){
                status = false;
            }

            return {
                id: optionID,
                type: type,
                value: value,
                label: label,
                clone: clone,
                status: status
            };
        },

        bundle: function (field) {
            var checkboxArr = null,
                labelArr    = {},
                optionId    = null,
                value       = null,
                qty         = 0;

            if ($(field).find('input.qty').length > 0) {
                qty = $(field).find('input.qty').val();
            }

            if (Number(qty) < 1) {
                qty = 1;
            }

            _.each($(field).find('.bundle.option'), function (option) {
                var optionEl = $(option),
                    sub      = [];

                if (optionEl.parent().find('.field.hidden').length > 0) {
                    optionId = optionEl.parent().find('.field.hidden .bundle.option').attr('id').split('-')[2];
                } else {
                    optionId = optionEl.attr('id').split('-')[2];
                }

                if (optionEl.is('input.radio:checked') || optionEl.is('[type="hidden"]')) {
                    value           = optionEl.val();
                    labelArr[value] = {
                        label: qty + ' x ' + optionEl.parent().find('.product-name').text(),
                        span: $(field).children('label.label').find('span').text()
                    };
                }
                if (optionEl.is('select')) {
                    value = optionEl.val();
                    if (_.isString(value)) {
                        labelArr[value] = {
                            label: qty + ' x ' + optionEl.find('[value="' + value + '"]').text().split('+')[0].trim(),
                            span: $(field).children('label.label').find('span').text()
                        };
                    } else {
                        _.each(value, function (subValue) {
                            labelArr[subValue] = {
                                label: optionEl.find('[value="' + subValue + '"]').text().split('+')[0].trim(),
                                span: $(field).children('label.label').find('span').text()
                            };
                        });
                    }
                }
                if (optionEl.is('input.checkbox:checked')) {
                    if (checkboxArr === null) {
                        checkboxArr = {};
                    }
                    sub = optionEl.attr('name')
                        .slice(14, optionEl.attr('name').length - 1).split('][');

                    checkboxArr[sub[1]] = optionEl.val();
                    labelArr[sub[1]]    = {
                        label: optionEl.parent().find('.product-name').text(),
                        span: $(field).children('label.label').find('span').text()
                    };
                }
            });

            return {
                id: optionId,
                value: value || checkboxArr,
                status: !!(value || checkboxArr),
                labels: labelArr,
                qty: qty
            };
        },
        giftcard: function (field) {
            var label = '',
                id    = $(field).attr('name'),
                value = $(field).val();

            switch (id){
                case 'giftcard_sender_name':
                    label = $t('Sender Name');
                    break;
                case 'giftcard_sender_email':
                    label = $t('Sender Email');
                    break;
                case 'giftcard_recipient_name':
                    label = $t('Recipient Name');
                    break;
                case 'giftcard_recipient_email':
                    label = $t('Recipient Email');
                    break;
                case 'giftcard_message':
                    label = $t('Message');
                    break;
                case 'custom_giftcard_amount':
                    label = $t('Amount');
            }

            return {
                label: label,
                id: id,
                value: value
            };
        },
        html: function (form, configs, productEL) {
            var html      = '',
                inputHtml = '<div class="mptcv-options-hide">',
                filesArr  = [],
                items     = [],
                self      = this;


            html += '<ul class="mptablecategoryview-list-options">';


            _.each(form.find('.giftcard.form .field .input-text '), function (field) {
                var giftcard = self.giftcard(field);

                if (giftcard.value !== '') {
                    html += '<li><span>' + giftcard.label + ' :</span> ' + giftcard.value + '</li>';
                }
            });

            _.each(form.find('.fieldset-bundle-options .field.option'), function (field) {
                var optionsValue = self.bundle(field);

                if (optionsValue.status) {
                    _.each(optionsValue.labels, function (label) {
                        html += '<li><span>' + label.span + '</span> ' + label.label + '</li>';
                    });
                }
            });

            _.each(form.find('.swatch-attribute'), function (attributeEL) {
                var optionChoose = $(attributeEL).find('.selected'),
                    attributeLabel,
                    optionLabel  = '',
                    subAttribute = '',
                    optionId     = '';


                if (optionChoose.length < 1) {
                    optionId    = $(attributeEL).attr('option-selected');
                    optionLabel = $(attributeEL).find('.swatch-attribute-options select [value="'
                        + optionId + '"]').text();
                } else {
                    if (productEL !== undefined) {
                        subAttribute = productEL.find('.mptcv-options-hide [data-id="'
                            + $(attributeEL).attr('attribute-id') + '"]');

                        optionChoose = $(attributeEL).find('[option-id="' + subAttribute.data('value') + '"]');
                    }

                    optionLabel = optionChoose.attr('data-option-label');
                }

                attributeLabel = $(attributeEL).find('.swatch-attribute-label').text();

                if (!$(attributeEL).is('td')
                    && $(attributeEL).parents('#mpcpgv-attribute-inactive').length < 1
                    && optionLabel !== '') {
                    html += '<li><span>' + attributeLabel + ':</span>  '
                        + optionLabel + '</li>';
                }
            });

            _.each(form.find('#mpcpgv-attribute-table tr'), function (item) {
                var mpcpgvProductEL       = $(item),
                    productId             = item.getAttribute('product-id'),
                    configurableProductId = $('#product_addtocart_form input[name="product"]').val(),
                    attributes            = $(item).find('td.swatch-attribute .swatch-option'),
                    attributeValues       = [],
                    attributeId,
                    attributeValue,
                    product;

                _.each(attributes, function (attribute) {
                    attributeId    = attribute.getAttribute('attribute-id');
                    attributeValue = attribute.getAttribute('attribute-value');
                    attributeValues.push({
                        attributeId: attributeId,
                        attributeValue: attributeValue
                    });
                });

                let qte = $('input[product-id="' + productId + '"]').val();

                if(qte > 0){
                    product   = {
                        configurableProductId: configurableProductId,
                        id: productId,
                        attributes: attributeValues,
                        qty: $('input[product-id="' + productId + '"]').val()
                    };

                    items.push(product);



                    var str = '';
                    mpcpgvProductEL.find('td.swatch-attribute .swatch-option').text(function(i, t){


                //mpcpgvProductEL.find('.swatch-label').text(function(i, t){
                        str += i == 0 ? t : '/' + t;
                    });


                    html += '<li><span>' + str + '</span> x '
                        + mpcpgvProductEL.find('.mpcpgv-input').val() + '</li>';
                    _.each(mpcpgvProductEL.find('td.swatch-attribute .swatch-option'), function (option) {
                        var optionArr = $(option).attr('id').split('-');


                    inputHtml += '<input type="hidden" class="mptcv-value mpcpgv" data-id="'
                        + mpcpgvProductEL.attr('product-id') + '" data-qty="'
                        + mpcpgvProductEL.find('.mpcpgv-input').val()
                        + '" data-attribute="' + optionArr[3] + ',' + optionArr[5] + '" >';
                });
                }
            });

            inputHtml += "<input type='hidden' class='mpcpgv-items' value='" + JSON.stringify(items) + "'>";

            _.each(form.find('#super-product-table tbody tr'), function (product) {
                var superProductEL = $(product);

                html += '<li><span>' + superProductEL.find('.product-item-name').text() + '</span> * '
                    + superProductEL.find('.qty input').val() + '</li>';
            });

            _.each(form.find(''), function (download) {
                var optionValue = self.options(configs, download);

                if (optionValue.status) {
                    html += '<li><span>' + $t("Downloads") + ':</span>  ';

                    _.each(optionValue.label, function (label) {
                        html += label + ' ';
                    });
                    html += '</li>';
                }
            });

            _.each(form.find('#product-options-wrapper .fieldset>.field, .mp-product-options .field.downloads')
                , function (optionEL) {
                    var optionValue = self.options(configs, optionEL);

                    if (optionValue.status) {
                        if (optionValue.type === 'file') {
                            filesArr.push(optionValue.clone);
                        }
                        switch (optionValue.type){
                            case 'field':
                            case "area":
                                html += '<li><span>' + $t(configs[optionValue.id].title) + ':</span>  '
                                    + optionValue.value + '</li>';
                                break;
                            case "drop_down":
                            case "radio":
                                html += '<li><span>' + $t(configs[optionValue.id].title) + ':</span>  '
                                    + configs[optionValue.id].values[optionValue.value].title + '</li>';

                                break;
                            case "checkbox":
                            case "multiple":
                                html += '<li><span>' + $t(configs[optionValue.id].title) + ':</span>  ';
                                _.each(optionValue.value, function (ovValue) {
                                    html += configs[optionValue.id].values[ovValue].title + ',';
                                });
                                html = html.substring(0, html.length - 1);
                                html += '</li>';

                                break;
                            case "date":
                                html += '<li><span>' + $t(configs[optionValue.id].title) + ':</span>  '
                                    + optionValue.value['month'] + '/'
                                    + optionValue.value['day'] + '/'
                                    + optionValue.value['year']
                                    + '</li>';
                                break;
                            case "date_time":
                                html += '<li><span>' + $t(configs[optionValue.id].title) + ':</span>  '
                                    + optionValue.value['month'] + '/'
                                    + optionValue.value['day'] + '/'
                                    + optionValue.value['year'] + ', '
                                    + optionValue.value['minute'] + ':'
                                    + optionValue.value['hour'] + ' '
                                    + optionValue.value['day_part'].toUpperCase()
                                    + '</li>';
                                break;
                            case "time":
                                html += '<li><span>' + $t(configs[optionValue.id].title) + ':</span>  '
                                    + optionValue.value['minute'] + ':'
                                    + optionValue.value['hour'] + ' '
                                    + optionValue.value['day_part'].toUpperCase()
                                    + '</li>';
                                break;
                            case "file":
                                html += '<li><span>' + $t(configs[optionValue.id].title) + ':</span>  '
                                    + optionValue.value + '</li>';
                                break;
                            case "downloads":
                                html += '<li><span>' + $t("Downloads") + ':</span>  ';

                                _.each(optionValue.label, function (label) {
                                    html += label + ' ';
                                });
                                html += '</li>';
                                break;
                            case "select":
                                html += '<li><span>' + $t(optionValue.label) + ':</span> '+ optionValue.value + '</li>';
                                break;
                        }

                    }
                });

            html      += '</ul>';
            inputHtml += '<input type="hidden" class="product_params" data-value="' + form.serialize() + '"></div>';

            return {html: html, inputHtml: inputHtml, files: filesArr};
        },
        chart: function jsUcfirst (string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
    };
});
