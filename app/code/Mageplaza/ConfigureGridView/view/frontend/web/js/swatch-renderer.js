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
 * @package     Mageplaza_ConfigureGridView
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'mage/translate',
    'underscore',
    'Magento_Customer/js/customer-data',
    'Magento_Catalog/js/price-utils',
    'Magento_Swatches/js/swatch-renderer'
], function ($, $t, _, CustomerData, priceUtils) {
    'use strict';

    $.widget('mageplaza_configurable.SwatchRenderer', $.mage.SwatchRenderer, {

        _create: function () {
            this._super();
            this.formContainer = this.element.parents('#product_addtocart_form');

            return this;


            $("a.add").click(function()
            {


                alert('nnnnnndddddnnn');

                let conditionnemment = $(this).data('conditionnemment');

                var row = $(this).closest("tr");
                let currentVal =  parseInt(row.find('input').val());
                alert(currentVal);
                let myinput = row.find('input');

                if (currentVal == '1')
                {
                    myinput.val(conditionnemment);

                } else {

                    myinput.val(currentVal + conditionnemment);
                }


                /*

            let div = $(this).closest('div.divTableRow');
            let currentVal =  parseInt(div.find('input').val());
            let myinput = div.find('input');

            if (currentVal == '1')
            {
                myinput.val(conditionnemment);

            } else {

                myinput.val(currentVal + conditionnemment);
            }
        */
                /*
                let conditionnemment = $(this).data('conditionnemment');
                let div = $(this).closest('div.divTableRow');
                let currentVal =  parseInt(div.find('input').val());
                let myinput = div.find('input');



                if (currentVal != NaN)
                {
                    myinput.val(currentVal + conditionnemment);

                }
                */

            });



        },

        /**
         * Render swatch options by part of config
         *
         * @param config
         * @param controlId
         * @param active
         * @returns {string}
         * @private
         */
        _RenderSwatchOptions: function (config, controlId, active) {
            var optionConfig    = this.options.jsonSwatchConfig[config.id],
                optionClass     = this.options.classes.optionClass,
                sizeConfig      = this.options.jsonSwatchImageSizeConfig,
                moreLimit       = parseInt(this.options.numberToShow, 10),
                moreClass       = this.options.classes.moreButton,
                moreText        = this.options.moreButtonText,
                countAttributes = 0,
                activeClass     = '',
                html            = '';

            if (!this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                return '';
            }

            $.each(config.options, function (index) {
                var id,
                    type,
                    value,
                    thumb,
                    label,
                    width,
                    height,
                    attr,
                    swatchImageWidth,
                    swatchImageHeight;

                if (!optionConfig.hasOwnProperty(this.id)) {
                    return '';
                }

                // Add more button
                if (moreLimit === countAttributes++) {
                    html += '<a href="#" class="' + moreClass + '"><span>' + moreText + '</span></a>';
                }

                id     = this.id;
                type   = parseInt(optionConfig[id].type, 10);
                value  = optionConfig[id].hasOwnProperty('value') ?
                    $('<i></i>').text(optionConfig[id].value).html() : '';
                thumb  = optionConfig[id].thumb ? optionConfig[id].thumb : '';
                width  = _.has(sizeConfig, 'swatchThumb') ? sizeConfig.swatchThumb.width : 110;
                height = _.has(sizeConfig, 'swatchThumb') ? sizeConfig.swatchThumb.height : 90;
                label  = this.label ? $('<i></i>').text(this.label).html() : '';
                attr   =
                    ' id="' + controlId + '-item-' + id + '"' +
                    ' attribute-id="' + config.id + '"' +
                    ' attribute-value="' + id + '"' +
                    ' index="' + index + '"' +
                    ' aria-checked="false"' +
                    ' aria-describedby="' + controlId + '"' +
                    ' tabindex="0"' +
                    ' option-type="' + type + '"' +
                    ' data-option-type="' + type + '"' +
                    ' option-id="' + id + '"' +
                    ' data-option-id="' + id + '"' +
                    ' option-label="' + label + '"' +
                    ' data-option-label="' + label + '"' +
                    ' aria-label="' + label + '"' +
                    ' option-tooltip-thumb="' + thumb + '"' +
                    ' option-tooltip-value="' + value + '"' +
                    ' data-option-tooltip-value="' + value + '"' +
                    ' role="option"' +
                    ' thumb-width="' + width + '"' +
                    ' data-thumb-width="' + width + '"' +
                    ' data-thumb-height="' + height + '"' +
                    ' thumb-height="' + height + '"';

                swatchImageWidth  = _.has(sizeConfig, 'swatchImage') ? sizeConfig.swatchImage.width : 30;
                swatchImageHeight = _.has(sizeConfig, 'swatchImage') ? sizeConfig.swatchImage.height : 20;

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' option-empty="true"';
                }
                if (active) {
                    activeClass = ' active';
                } else {
                    activeClass = ' inactive';
                }
                if (type === 0) {
                    // Text
                    html += '<div class="' + optionClass + activeClass + ' text" ' + attr + '>'
                        + (value ? value : label) +
                        '</div>';
                } else if (type === 1) {
                    // Color
                    html += '<div class="' + optionClass + activeClass + ' color" ' + attr +
                        ' style="background: ' + value +
                        ' no-repeat center; background-size: initial;">' + '' +
                        '</div>' + label + '';
                } else if (type === 2) {
                    // Image
                    html += '<div class="' + optionClass + activeClass + ' image" ' + attr +
                        ' style="background: url(' + value + ') no-repeat center; background-size: initial;width:70px; height:70px">' + '' +
                        '</div><div class="swatch-label" style="margin-top:25px">' + label + '</div>';
                } else if (type === 3) {
                    // Clear
                    html += '<div class="' + optionClass + activeClass + '" ' + attr + '></div>';
                } else {
                    // Default
                    html += '<div class="' + optionClass + activeClass + '" ' + attr + '>' + label + '</div>';
                }
            });

            return html;
        },

        /**
         * Render controls
         *
         * @private
         */
        _RenderControls: function () {
            var widget            = this,
                classes           = this.options.classes,
                container         = this.element.find('#mpcpgv-attribute-table>tbody'),
                inactiveContainer = this.formContainer.find('#mpcpgv-attribute-inactive');

            widget.optionsMap = {};


            $.each(this.options.jsonConfig.attributes, function () {
                var item = this;

                widget.optionsMap[item.id] = {};

                // Aggregate options array to hash (key => value)
                $.each(item.options, function () {
                    if (this.products.length > 0) {
                        widget.optionsMap[item.id][this.id] = {
                            price: parseInt(
                                widget.options.jsonConfig.optionPrices[this.products[0]].finalPrice.amount,
                                10
                            ),
                            products: this.products
                        };
                    }
                });
            });

            if (!_.isEmpty(this.options.childData.mpInActive)) {
                _.each(this.options.childData.mpInActive, function (attribute) {
                    var row            = '<div><div class="mp-title"><span>' + attribute.label + '</span></div>',
                        controlLabelId = 'option-label-' + attribute.code + '-' + attribute.id,
                        options        = widget._RenderSwatchOptions(attribute, controlLabelId, false);

                    if (options === '') {
                        options = '<div class="mp-cgv-control"><select name="super_attribute['+attribute.id+']"' +
                            'data-selector="super_attribute['+attribute.id+']"' +
                            'id="attribute'+attribute.id+'" class="super-attribute-select">' +
                            '<option value="">Choose an Option...</option>';
                        _.each(attribute.options, function (option) {
                            options += '<option value="' + option.id + '" option-id="' + option.id + '">'
                                + option.label + '</option>';
                        });
                        options += '</select></div>';
                    }

                    row += '<div class="' + classes.attributeClass + ' ' + attribute.code + '" attribute-id="'
                        + attribute.id + '" attribute-code="' + attribute.code + '">' + options + '</div></div>';
                    inactiveContainer.append(row);
                });
            } else {
                this.formContainer.find('#mpcpgv-attribute-table').css('display', 'inline-table');
                _.each(this.options.childData.mpActive, function (product) {
                    widget._getActiveAttribute(product, product.id, widget);
                    widget._CheckOutStock(widget, product.id);
                });
                widget._CheckColumn(widget);
            }
            // Connect Tooltip
            container
            .find('[option-type="1"], [option-type="2"], [option-type="0"], [option-type="3"]')
            .SwatchRendererTooltip();

            // Hide all elements below more button
            $('.' + classes.moreButton).nextAll().hide();

            // Handle events like click or change
            widget._EventListener();

            // Rewind options
            widget._Rewind(container);

            //Emulate click on all swatches from Request
            widget._EmulateSelected($.parseQuery());
            widget._EmulateSelected(widget._getSelectedAttributes());
        },

        /**
         * Check Qty product
         *
         * @param $widget
         * @param productId
         * @private
         */
        _CheckOutStock: function ($widget, productId) {
            var outProduct = $widget.options.childData.outProduct[productId],
                isBackorders,
                itemEl;

            if (typeof outProduct !== 'undefined') {
                isBackorders = outProduct.value.isBackorders;
                itemEl = $('#mpcpgv-attribute-table tr[product-id="' + productId + '"]');
                if (!isBackorders) {
                    itemEl.find('.mpcpgv-qty').css('pointer-events', 'none');
                    itemEl.find('input').attr('disabled', 'true');
                }
            }
        },

        /**
         * Check display desktop
         *
         * @param $widget
         * @private
         */
        _CheckColumn: function ($widget) {
            var tableEl = $widget.formContainer.find('#mpcpgv-attribute-table'),
                pc      = $widget.options.childData.config.columnPC,
                mobile  = $widget.options.childData.config.columnMobile,
                tablet  = $widget.options.childData.config.columnTablet,
                width   = window.screen.width;

            if (width < 482) {
                tableEl.css('overflow', 'overlay');
                $widget._HideColumn($widget, mobile);
            } else if (width < 821) {
                tableEl.css('overflow', 'overlay');
                $widget._HideColumn($widget, tablet);
            } else {
                if ($widget.formContainer.find('#mpquickview-popup #mpcpgv-attribute-table').length > 0) {
                    tableEl.css('overflow', 'auto');
                } else {
                    tableEl.css('overflow', '');
                }
                $widget._HideColumn($widget, pc);
            }
        },

        /**
         * Hide columns
         *
         * @param $widget
         * @param display
         * @private
         */
        _HideColumn: function ($widget, display) {
            var formContainer = $widget.formContainer;

            if (!_.isEmpty(display)) {
                formContainer.find('#mpcpgv-attribute-table td').css('display', '');
                _.each(display, function (columnId) {
                    formContainer.find('td[display-id="' + columnId + '"]').css('display', 'none');
                    switch (columnId){
                        case 0:
                            formContainer.find('.mpcpgv-stock').css('display', 'none');
                            break;
                        case 1:
                            formContainer.find('.mpcpgv-sku').css('display', 'none');
                            break;
                        case 2:
                            formContainer.find('.mpcpgv-price').css('display', 'none');
                            break;
                        case 3:
                            formContainer.find('.mpcpgv-subtotal').css('display', 'none');
                            break;
                        case 4:
                            formContainer.find('.mpcpgv-icon').css('display', 'none');
                            break;
                        case 5:
                            formContainer.find('.mpcpgv-special').css('display', 'none');
                            formContainer.find('.mpcpgv-unit-price').css('color', 'currentColor');
                            break;
                    }
                });
            }
        },

        /**
         * Event listener
         *
         * @private
         */
        _EventListener: function () {
            var $widget       = this,
                options       = this.options.classes,
                detail        = $widget.formContainer.find('#mpcpgv-detail'),
                formContainer = $widget.formContainer,
                target;

            $widget.element.on('click', '.' + options.optionClass, function () {
                return $widget._OnClick($(this), $widget);
            });

            $('.action.towishlist').on('click', function (event) {
                var formData  = new FormData(),
                    url       = $widget.options.addToWishlistUrl,
                    productId = $('#product_addtocart_form input[name="product"]').val(),
                    items     = [];

                _.each($('#mpcpgv-simple-product .mpcpgv-simple'), function (item) {
                    var productId             = item.getAttribute('product-id'),
                        attributes            = $(item).find('td.attributes .swatch-option'),
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
                    product = {
                        attributes: attributeValues,
                        qty: $('.mpcpgv-simple[product-id="' + productId + '"] .mpcpgv-qty').text()
                    };

                    items.push(product);
                });

                if (items.length) {
                    event.preventDefault();
                    event.stopPropagation();

                    formData.append('product', productId);
                    formData.append('items', JSON.stringify(items));

                    $.ajax({
                        url: url,
                        type: "post",
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        showLoader: true,
                        success: function (res) {
                            if (res.error) {
                                if (res.backUrl) {
                                    window.location.href = res.backUrl;
                                }
                            } else {
                                window.location.href = res.backUrl;
                            }
                        }
                    });
                }
            });

            formContainer.find('#mpcpgv-attribute-inactive').on('click', '.' + options.optionClass, function () {
                return $widget._OnInActiveClick($(this), $widget, false);
            });

            formContainer.find('#mpcpgv-attribute-inactive').on('change', '.super-attribute-select', function () {
                return $widget._OnInActiveSelect($(this), $widget);
            });

            detail.on('click', '.mpcpgv-reset', function () {
                return $widget._ResetAll($widget);
            });

            detail.on('click', '.mpcpgv-delete-detail', function () {
                return $widget._RemoveItem($(this), $widget);
            });

            $widget.element.on('click', '.mpcpgv-inc', function () {
                return $widget._OnIncClick($(this), $widget);
            });

            $widget.element.on('mouseover', '.mpcpgv-icon', function () {
                return $widget._OverTierPrice($(this), $widget);
            });

            $widget.element.on('mouseout', '.mpcpgv-icon', function () {
                return $widget._OutTierPrice($(this));
            });

            $widget.element.on('click', '.mpcpgv-dec', function () {
                return $widget._OnDecClick($(this), $widget);
            });

            $widget.element.on('focusin', 'input', function () {
                $(this).data('val', $(this).val());
            }).on('change', 'input.mpcpgv-input', function () {
                return $widget._KeyupChange($(this), $widget);
            });

            formContainer.find('.product-options-bottom').on('click', '#product-addtocart-button', function () {
                this.setAttribute('disabled', 'disabled');
                $('#product-addtocart-button span').text($t('Adding...'));
                return $widget._AddProductToCart($widget);
            });

            $widget.element.on('change', '.' + options.selectClass, function () {
                return $widget._OnChange($(this), $widget);
            });

            $widget.element.on('click', '.' + options.moreButton, function (e) {
                e.preventDefault();
                return $widget._OnMoreClick($(this));
            });

            $widget.element.on('keydown', function (e) {
                if (e.which === 13) {
                    target = $(e.target);

                    if (target.is('.' + options.optionClass)) {
                        return $widget._OnClick(target, $widget);
                    } else if (target.is('.' + options.selectClass)) {
                        return $widget._OnChange(target, $widget);
                    } else if (target.is('.' + options.moreButton)) {
                        e.preventDefault();

                        return $widget._OnMoreClick(target);
                    }
                }
            });

            formContainer.find('.mpcpgv-input').keypress(function (e) {
                if (!(e.which >= 48 && e.which <= 57)) {
                    return false;
                }
            });

            formContainer.find('.product-custom-option').on('change', function () {
                $widget._UpdateQty($widget);
            });

            formContainer.find('.mpcpgv-attribute-sort').on('click', function () {
                var attribute = $(this).attr('id'),
                    icon      = formContainer.find('.mpcpgv-sort-asc, .mpcpgv-sort-desc'),
                    ascIcon   = $(this).find('.mpcpgv-sort-asc'),
                    descIcon  = $(this).find('.mpcpgv-sort-desc');

                if ($widget.options.childData.config.enableSort
                    && $widget.options.childData.config.enableSort !== '0') {
                    if (ascIcon.is(':visible')) {
                        icon.hide();
                        $widget._sortByAttribute(attribute, 'desc');
                        descIcon.show();
                    } else {
                        icon.hide();
                        $widget._sortByAttribute(attribute, 'asc');
                        ascIcon.show();
                    }
                }
            });
        },

        /**
         * Sort product by attribute
         *
         * @param attr
         * @param sortBy
         * @private
         */
        _sortByAttribute: function (attr, sortBy) {
            var products,
                items     = [],
                $widget   = this,
                container = $widget.formContainer.find('#mpcpgv-attribute-table>tbody');

            container.find('tr').remove();

            products = $.map(this.options.childData.mpActive, function(value) {
                return [value];
            });

            products.sort(function (item1, item2) {
                if (sortBy === 'desc') {
                    return item1[attr + '_text'] > item2[attr + '_text'] ? -1 : 1;
                }

                return item1[attr + '_text'] > item2[attr + '_text'] ? 1 : -1;
            });

            _.each(products, function (product) {
                $widget._getActiveAttribute(product, product.id, $widget);
                $widget._CheckOutStock($widget, product.id);
            });


            _.each($('#mpcpgv-simple-product .mpcpgv-simple'), function (item) {
                var product,
                    productId = item.getAttribute('product-id'),
                    price     = 0,
                    qty       = $('.mpcpgv-simple[product-id="' + productId + '"] .mpcpgv-qty').text();

                _.each(products, function (prod) {
                    if (prod.id === productId) {
                        price = prod.price;
                    }
                });

                product   = {
                    id: productId,
                    price: price,
                    qty: qty
                };

                items.push(product);
            });

            _.each(items, function (item) {
                var it = $('#mpcpgv-attribute-table>tbody');

                it.find('tr[product-id=' + item.id + '] .mpcpgv-qty input').val(item.qty);
                it.find('tr[product-id=' + item.id + '] .mpcpgv-subtotal span#subtotal-' +
                    item.id).text($widget.getFormattedPrice(item.price * item.qty));
            });

            $widget._CheckColumn($widget);
        },

        /**
         * Add product to cart
         *
         * @param $widget
         * @private
         */
        _AddProductToCart: function ($widget) {
            var url      = $widget.options.childData.config.url,
                storeId  = $widget.options.childData.config.storeId,
                form     = $('#product_addtocart_form'),
                formData = new FormData(form[0]),
                items    = [];

            _.each($('#mpcpgv-attribute-table tr'), function (item) {
                var productId             = item.getAttribute('product-id'),
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
               }

            });
            formData.append('items', JSON.stringify(items));
            formData.append('storeId', storeId);
            $.ajax({
                url: url,
                type: "post",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                showLoader: true,
                success: function (response) {
                    if (response.status) {
                        CustomerData.reload(['cart'], false);
                        $widget._ResetAll($widget);
                        $('.mpcpgv-input').val(0);
                        if ($('#mpquickview-popup .mfp-close').length > 0) {
                            $.magnificPopup.close();
                        }
                        $('#product-addtocart-button span').text($t('Added'));
                        $("body,html").animate({scrollTop: $('.header-container').offset().top-100}, "slow");
                    } else {
                        $('#product-addtocart-button').removeAttr('disabled');
                    }
                },
                complete: function () {
                    $('#product-addtocart-button span').text($t('Add to Cart'));
                }
            });
        },

        /**
         * Remove a item in detail
         *
         * @param $this
         * @param $widget
         * @private
         */
        _RemoveItem: function ($this, $widget) {
            var product,
                productId  = $this.parent().attr('product-id'),
                totalQtyEl = $('.mpcpgv-detail-qty'),
                totalSumEl = $('.mpcpgv-detail-sum'),
                qty        = Number($this.parent().children('.mpcpgv-qty').text()),
                inputEl    = $widget.element.find('input[product-id=' + productId + ']'),
                newQtyValue,
                newTotalValue;

            _.each($widget.options.childData.mpActive, function (productActive) {
                if (productActive.id === productId) {
                    product = productActive;
                }
            });

            // remove and change in detail
            $this.parent().remove();
            newQtyValue = Number(totalQtyEl.text()) - qty;
            totalQtyEl.text(newQtyValue);
            newTotalValue = Number(totalSumEl.text()) - qty * Number(product.price);
            totalSumEl.text(newTotalValue);

            // change in attribute table
            inputEl.val(0);
            $('#subtotal-' + productId).text($widget.getFormattedPrice(0));
            $widget._UpdateQty($widget);
        },

        /**
         * Reset All Item
         *
         * @private
         */
        _ResetAll: function ($widget) {
            $widget.formContainer.find('.mpcpgv-simple').remove();
            $widget.formContainer.find('.mpcpgv-subtotal span').text($widget.getFormattedPrice(0));
            $widget._UpdateQty($widget);
        },

        /**
         * get a product price
         *
         * @param $widget
         * @param productId
         * @param qty
         * @returns {PaymentCurrencyAmount}
         * @private
         */
        _GetMpPrice: function ($widget, productId, qty) {
            var outProduct, tierPriceAll, finalPrice;

            if (typeof qty === 'undefined') {
                outProduct = $widget.options.childData.outProduct[productId];

                if (typeof outProduct !== 'undefined') {
                    return Number(outProduct.value.price);
                }
                return $widget.options.jsonConfig.optionPrices[productId].finalPrice.amount;
            }

            if (typeof $widget.options.jsonConfig.optionPrices[productId] !== 'undefined'){
                tierPriceAll = $widget.options.jsonConfig.optionPrices[productId].tierPrices;
                finalPrice   = $widget.options.jsonConfig.optionPrices[productId].finalPrice.amount;
                if (!_.isEmpty(tierPriceAll)) {
                    _.some(tierPriceAll, function (tierPrice) {
                        if (qty >= tierPrice.qty) {
                            finalPrice = tierPrice.price;
                        } else {
                            return true;
                        }
                    });
                }
                $widget.formContainer.find('#mpcpgv-attribute-table')
                .find('tr[product-id="' + productId + '"] .mpcpgv-price .mpcpgv-unit-price')
                .text(
                    $widget.getFormattedPrice(finalPrice)
                );
            } else {
                _.each($widget.options.childData.mpActive, function (productActive) {
                    if (productId === productActive.id) {
                        finalPrice = productActive.price;
                    }
                });
            }

            return finalPrice;

        },

        /**
         * Get all product price
         *
         * @param $widget
         * @param productId
         * @returns {*}
         * @private
         */
        _GetMpAllPrice: function ($widget, productId) {
            var outProduct = $widget.options.childData.outProduct[productId],
                price;

            if (typeof outProduct !== 'undefined') {
                price = Number(outProduct.value.price);
                return {
                    oldPrice: {amount: price},
                    finalPrice: {amount: price}

                };
            }
            return $widget.options.jsonConfig.optionPrices[productId];
        },

        /**
         * Input change
         *
         * @param $this
         * @param $widget
         * @param status
         * @private
         */
        _KeyupChange: function ($this, $widget, status) {
            var qty          = Number($this.val()),
                oldQty       = Number($this.data('val')),
                productId    = $this.attr('product-id'),
                subtotal     = $widget.formContainer.find('#subtotal-' + productId),
                productPrice = $widget._GetMpPrice($widget, productId, qty);

            if (qty < 0) {
                subtotal.html($widget.getFormattedPrice(0));
                $this.val(0);
            } else {
                subtotal.html($widget.getFormattedPrice(qty * productPrice));
                if (typeof status === "undefined") {
                    $widget._ChangeDetail($this, $widget, oldQty < qty);
                } else {
                    $widget._ChangeDetail($this, $widget, status);
                }
            }
        },

        /**
         * Event mouseover in Tier Price
         *
         * @param $this
         * @param $widget
         * @private
         */
        _OverTierPrice: function ($this, $widget) {
            var productId = $this.parent().parent().attr('product-id'),
                allPrice  = $widget._GetMpAllPrice($widget, productId),
                html      = '<div class="mpcpgv-tooltip">',
                element   = $('#mpcpgv-attribute-table');

            _.each(allPrice.tierPrices, function (tierPrice) {
                html += '<div>' + 'x' + tierPrice.qty + ' = ' + $widget.getFormattedPrice(tierPrice.price)
                    + '&nbsp;<strong>(-' + tierPrice.percentage + '%' + ')</strong></div>';
            });
            html += '</div>';
            $this.parent().append(html);

        },
        _OutTierPrice: function ($this) {
            $this.parent().find('.mpcpgv-tooltip').remove();
        },

        /**
         * Click to Inc
         *
         * @param $this
         * @param $widget
         * @private
         */
        _OnIncClick: function ($this, $widget) {
            var input = $this.parent().parent().find('.mpcpgv-input'),
                old   = Number(input.val());

            input.val(old + 1);
            $widget._KeyupChange(input, $widget, true);
        },

        /**
         * Click to dec
         *
         * @param $this
         * @param $widget
         * @private
         */
        _OnDecClick: function ($this, $widget) {
            var input = $this.parent().parent().find('.mpcpgv-input'),
                old   = Number(input.val());

            input.val(old - 1);
            $widget._KeyupChange(input, $widget, false);
        },

        /**
         * update Item to detail
         *
         * @param $this
         * @param $widget
         * @param status
         * @private
         */
        _ChangeDetail: function ($this, $widget, status) {
            var input     = $this.parent().parent().find('.mpcpgv-input'),
                productId = input.attr('product-id'),
                product   = $widget.options.childData.all[productId],
                old       = Number(input.val()),
                productHtml,
                attributes;

            if (old >= 1) {
                if (status) {
                    productHtml = '<tr class="mpcpgv-simple" product-id="' + productId + '"><td>'
                        + product.sku + '</td>';
                    attributes  = '<td class="attributes">';
                    _.each(product, function (attribute) {
                        var controlLabelId = 'option-label-' + attribute.code + '-' + attribute.id,
                            options        = $widget._RenderSwatchOptions(attribute, controlLabelId, false);

                        if (options === '' && attribute instanceof Object) {
                            options = '<div id="option-label-size-' + attribute.id + '-item-' + attribute.options[0].id
                                + '" class="swatch-option" attribute-id="' + attribute.id + '" attribute-value="'
                                + attribute.options[0].id + '" option-id="' + attribute.options[0].id + '">'
                                + attribute.options[0].label + '</div>';
                        }

                        attributes += options;
                    });
                    productHtml += attributes + '</td><td class="mpcpgv-qty">' + old + '</td>'
                        + '<td class="mpcpgv-delete-detail"></td></tr>';
                    if ($('.mpcpgv-simple[product-id="' + productId + '"]').length < 1) {
                        $widget.formContainer.find('#mpcpgv-simple-product').append(productHtml);
                    } else {
                        $('.mpcpgv-simple[product-id="' + productId + '"] .mpcpgv-qty').text(old);
                    }
                } else {
                    $widget.formContainer.find('#mpcpgv-simple-product')
                    .find('[product-id=' + productId + ']')
                    .children('.mpcpgv-qty')
                    .html(old);
                }
            } else {
                $widget.formContainer.find('#mpcpgv-simple-product').find('[product-id=' + productId + ']').remove();
            }
            $widget._UpdateQty($widget);
        },

        /**
         * Update Total
         *
         * @param $widget
         * @private
         */
        _UpdateQty: function ($widget) {
            var detailQty           = $widget.formContainer.find('.mpcpgv-detail-qty'),
                detailSum           = $widget.formContainer.find('.mpcpgv-detail-sum'),
                addCartBt           = $widget.formContainer.find('#product-addtocart-button'),
                form                = $('#product_addtocart_form'),
                isShowDetail        = $widget.options.childData.config.isShowSummary,
                customizableOptions = $widget.options.childData.config.customizableOptions,
                finalPrice;

            detailQty.text(0);
            detailSum.text(0);
            _.each($widget.formContainer.find('.mpcpgv-simple'), function (trEl) {
                var productId    = trEl.getAttribute('product-id'),
                    qty          = Number($('.mpcpgv-simple[product-id="' + productId + '"] .mpcpgv-qty').text()),
                    price        = $widget._GetMpPrice($widget, productId, qty),
                    total        = Number(detailQty.text()),
                    sum          = Number(detailSum.text()),
                    optionSelect = form.serializeArray();

                _.each(optionSelect, function (option) {
                    if (option.name.includes('options') && option.value) {
                        _.each(customizableOptions, function (customizableOption, i) {
                            if (option.name.includes('[' + i + ']')) {
                                if (typeof customizableOption === 'object') {
                                    _.each(customizableOption, function (opt, i) {
                                        if (option.value === i) {
                                            price += parseFloat(opt);
                                        }
                                    });
                                } else {
                                    price += parseFloat(customizableOption);
                                }
                            }
                        });
                    }
                });

                detailQty.text(total + qty);
                detailSum.text(sum + qty * price);
            });
            detailSum.text($widget.getFormattedPrice(detailSum.text()));
            if (Number(detailQty.text()) > 0) {
                if (isShowDetail !== '0') {
                    $widget.formContainer.parent().parent().find('.price-box.price-final_price').css('display', 'none');
                    $widget.formContainer.find('#mpcpgv-detail').css('display', 'block');
                }
                addCartBt.removeAttr('disabled');
            } else {
                finalPrice = $widget.formContainer.parent().parent().find('.price-box.price-final_price');
                finalPrice.css('display', 'table-cell');
                $widget.formContainer.find('#mpcpgv-detail').css('display', 'none');
                addCartBt.attr('disabled', 'disabled');
            }
        },

        /**
         * Event for swatch options
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnClick: function ($this, $widget) {
            var $parent        = $this.parents('.' + $widget.options.classes.attributeClass),
                $wrapper       = $this.parents('.' + $widget.options.classes.attributeOptionsWrapper),
                $label         = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                attributeId    = $parent.attr('attribute-id'),
                productId      = $this.parent().parent().attr('product-id'),
                productPrice   = $widget._GetMpPrice(
                    $widget,
                    productId,
                    $('.mpcpgv-input[product-id="' + productId + '"]').val()
                ),
                optionSelected = $('.' + $widget.options.classes.optionClass + '.active'),
                $input         = $parent.find('.' + $widget.options.classes.attributeInput);

            if ($widget.inProductList) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }

            if ($this.hasClass('disabled')) {
                return;
            }

            if (optionSelected.hasClass('selected')) {
                optionSelected.removeClass('selected');
            }

            $widget.formContainer.find('.normal-price .price-label').text($t('Unit Price'));
            $widget.formContainer.find('.normal-price .price-wrapper .price')
            .text($widget.getFormattedPrice(productPrice));

            $parent.attr('option-selected', $this.attr('option-id')).find('.selected').removeClass('selected');
            $label.text($this.attr('option-label'));
            $input.val($this.attr('option-id'));
            $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
            if (!$this.hasClass('inactive')) {
                $this.parent().parent().find('.swatch-option').addClass('selected');
            } else {
                $this.addClass('selected');
            }
            $widget._toggleCheckedAttributes($this, $wrapper);

            $widget._loadMedia();
            $input.trigger('change');
        },
        _OnInActiveSelect: function($this, $widget) {
            var $parent        = $this.parents('.' + $widget.options.classes.attributeClass),
                optionId = [],
                tableEl        = $widget.formContainer.find('#mpcpgv-attribute-table'),
                attributeId = $this.parents('.swatch-attribute').attr('attribute-id'),
                outProducts    = $widget.options.childData.outProduct,
                productActives = [],
                container      = $widget.formContainer.find('#mpcpgv-attribute-table>tbody'),
                products       = [],
                currentProduct = [],
                $input         = $parent.find('.' + $widget.options.classes.attributeInput),
                show           = true,
                options        = {},
                productSuccess = {};


            optionId[attributeId] = $this.children("option:selected").val();

            if ($this.children("option:selected").val()) {
                $this.parent().parent().attr('option-selected', $this.children("option:selected").val());
            } else {
                $this.parent().parent().removeAttr('option-selected');
            }

            if ($widget.formContainer.find('#mpcpgv-attribute-inactive > div').length >= 1) {
                $widget.formContainer.find('#mpcpgv-attribute-inactive').find('.swatch-attribute').each(function () {
                    if (!$(this).is('[option-selected]')) {
                        show = false;
                        return false;
                    }
                });
            }

            if (show) {
                tableEl.css('display', 'block');
            }

            if ($widget.formContainer.find('#mpcpgv-attribute-inactive > div').length >= 1) {
                $widget.formContainer.find('#mpcpgv-attribute-inactive').find('div').each(function () {
                    if ($(this).is('[option-selected]')) {
                        options[$(this).attr('attribute-id')] = $(this).attr('option-selected');
                    }
                });
            }

            _.each(this.options.childData.mpInActive, function (attribute) {
                _.each(attribute.options, function (option) {
                    _.each(options, function (v, k) {
                        if (attribute.id === k && option.id === v) {
                            currentProduct.push(option.products);
                        }
                    });
                    if (!(optionId.indexOf(option.id) < 0)) {
                        _.each(outProducts, function (outProduct, outProductId) {
                            if (outProduct['attribute'][attribute.code]['options'][0]['id'] === option.id) {
                                option.products.push(outProductId);
                            }
                        });
                        products.push(option.products);
                    }
                });
            });

            _.each(currentProduct, function (product) {
                if (_.isEmpty(productActives)) {
                    productActives = product;
                } else {
                    productActives = _.intersection(productActives, product);
                }
            });

            // reset attribute table
            container.empty();

            if (_.isEmpty(productActives)) {
                tableEl.css('display', 'none');
            }  else if (!_.isEmpty(productActives) && show){
                _.each(this.options.childData.mpActive, function (productActive) {
                    if (!(productActives.indexOf(productActive.id) < 0)) {
                        productSuccess[productActive.id] = productActive;
                    }
                });

                // render item
                _.each(productSuccess, function (product, productId) {
                    $widget._getActiveAttribute(product, productId, $widget);
                    $widget._CheckOutStock($widget, product.id);
                    _.each($widget.formContainer.find('.mpcpgv-simple'), function (productDetail) {
                        var inputEl, qty, price;

                        if (productDetail.getAttribute('product-id') === productId) {
                            inputEl = $widget.element.find('input[product-id=' + productId + ']');
                            qty     = Number($('.mpcpgv-simple[product-id="' + productId + '"] .mpcpgv-qty').text());
                            price   = $widget._GetMpPrice($widget, productId, qty);
                            inputEl.val(qty);
                            $widget.element.find('tr[product-id="' + productId + '"] .mpcpgv-subtotal').text(
                                $widget.getFormattedPrice(qty * price)
                            );
                            $widget._UpdateQty($widget);
                        }
                    });
                });
                $widget._CheckColumn($widget);
                $widget._loadMedia();

            } else {
                tableEl.css('display', 'none');
            }
            // Connect Tooltip
            container
            .find('[option-type="1"], [option-type="2"], [option-type="0"], [option-type="3"]')
            .SwatchRendererTooltip();

            $widget._Rebuild();
            $input.trigger('change');
            if ($widget.formContainer.find('#mpcpgv-attribute-inactive > div').length > 1
                && $widget.formContainer.find('#mpcpgv-attribute-inactive').find('.swatch-option').length > 0) {
                $widget._OnInActiveClick($('.' + this.options.classes.optionClass + '.selected'), $widget, true);
            }
        },

        /**
         *
         * @param $this
         * @param $widget
         * @param reset
         * @private
         */
        _OnInActiveClick: function ($this, $widget, reset) {
            var $parent        = $this.parents('.' + $widget.options.classes.attributeClass),
                $wrapper       = $this.parents('.' + $widget.options.classes.attributeOptionsWrapper),
                $label         = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                attributeId    = $parent.attr('attribute-id'),
                optionId       = [],
                products       = [],
                currentProduct = [],
                container      = $widget.formContainer.find('#mpcpgv-attribute-table>tbody'),
                tableEl        = $widget.formContainer.find('#mpcpgv-attribute-table'),
                outProducts    = $widget.options.childData.outProduct,
                $input         = $parent.find('.' + $widget.options.classes.attributeInput),
                productActives = [],
                show           = true,
                options        = {},
                productSuccess = {};

            if ($this.hasClass('disabled')) {
                return;
            }

            if ($this.hasClass('selected') && !reset) {
                $parent.removeAttr('option-selected').find('.selected').removeClass('selected');
                $input.val('');
                $label.text('');
                $this.attr('aria-checked', false);
            } else {
                $parent.attr('option-selected', $this.attr('option-id')).find('.selected').removeClass('selected');
                $label.text($this.attr('option-label'));
                $input.val($this.attr('option-id'));
                $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
                if (!$this.hasClass('inactive')) {
                    $this.parent().parent().find('.swatch-option').addClass('selected');
                } else {
                    $this.addClass('selected');
                }
                $widget._toggleCheckedAttributes($this, $wrapper);
            }

            $widget.formContainer.find('#mpcpgv-attribute-inactive').find('.selected').each(function () {
                optionId[this.parentElement.getAttribute('attribute-id')] = this.getAttribute('option-id');
            });

            if ($widget.formContainer.find('#mpcpgv-attribute-inactive > div').length >= 1) {
                $widget.formContainer.find('#mpcpgv-attribute-inactive').find('.swatch-attribute').each(function () {
                    if (!$(this).is('[option-selected]')) {
                        show = false;
                        return false;
                    }
                });
            }

            if (show) {
                tableEl.css('display', 'block');
            }

            if ($widget.formContainer.find('#mpcpgv-attribute-inactive > div').length >= 1) {
                $widget.formContainer.find('#mpcpgv-attribute-inactive').find('div').each(function () {
                    if ($(this).is('[option-selected]')) {
                        options[$(this).attr('attribute-id')] = $(this).attr('option-selected');
                    }
                });
            }

            _.each(this.options.childData.mpInActive, function (attribute) {
                _.each(attribute.options, function (option) {
                    _.each(options, function (v, k) {
                        if (attribute.id === k && option.id === v) {
                            currentProduct.push(option.products);
                        }
                    });
                    if (!(optionId.indexOf(option.id) < 0)) {
                        _.each(outProducts, function (outProduct, outProductId) {
                            if (outProduct['attribute'][attribute.code]['options'][0]['id'] === option.id) {
                                option.products.push(outProductId);
                            }
                        });
                        products.push(option.products);
                    }
                });
            });

            _.each(currentProduct, function (product) {
                if (_.isEmpty(productActives)) {
                    productActives = product;
                } else {
                    productActives = _.intersection(productActives, product);
                }
            });

            // reset attribute table
            container.empty();

            if (!_.isEmpty(productActives) && show) {
                _.each(this.options.childData.mpActive, function (productActive, productId) {
                    if (!(productActives.indexOf(productId) < 0)) {
                        productSuccess[productActive.id] = productActive;
                    }
                });

                // render item
                _.each(productSuccess, function (product, productId) {
                    $widget._getActiveAttribute(product, productId, $widget);
                    $widget._CheckOutStock($widget, productId);
                    _.each($widget.formContainer.find('.mpcpgv-simple'), function (productDetail) {
                        var inputEl, qty, price;

                        if (productDetail.getAttribute('product-id') === productId) {
                            inputEl = $widget.element.find('input[product-id=' + productId + ']');
                            qty     = Number($('.mpcpgv-simple[product-id="' + productId + '"] .mpcpgv-qty').text());
                            price   = $widget._GetMpPrice($widget, productId, qty);
                            inputEl.val(qty);
                            $widget.element.find('tr[product-id="' + productId + '"] .mpcpgv-subtotal').text(
                                $widget.getFormattedPrice(qty * price)
                            );
                            $widget._UpdateQty($widget);
                        }
                    });
                });
                $widget._CheckColumn($widget);
                $widget._loadMedia();

            } else {
                tableEl.css('display', 'none');
            }
            // Connect Tooltip
            container
            .find('[option-type="1"], [option-type="2"], [option-type="0"], [option-type="3"]')
            .SwatchRendererTooltip();
            $widget._Rebuild();
            $input.trigger('change');
        },

        /**
         * get attribute html
         *
         * @param product
         * @param productId
         * @param widget
         * @private
         */
        _getActiveAttribute: function (product, productId, widget) {
            var row       = '<tr product-id="' + productId + '">',
                classes   = widget.options.classes,
                container = widget.formContainer.find('#mpcpgv-attribute-table>tbody'),
                controlLabelId,
                options, allPrice, oldPrice, finalPrice, style, tierPrice, specialPrice;

            if(widget.options.jsonConfig.images[productId]){
                var mythumb = widget.options.jsonConfig.images[productId][0].thumb;
            } else {
                var mythumb = '';
            }


                row += '<td><img width="80px" src="'+mythumb+'"></td>';

            _.each(product, function (item, key) {
                if (typeof item === 'object') {
                    controlLabelId = 'option-label-' + item.code + '-' + item.id;
                    options        = widget._RenderSwatchOptions(item, controlLabelId, true);



                        if (options === '') {

                        options = '<div class="swatch-option" id="option-label-size-' + item.id + '-item-' + item.options[0].id + '" attribute-id="' + item.id + '" attribute-value="' + item.options[0].id + '" option-id="' + item.options[0].id + '">' + item.options[0].label + '</div>';
                    }


                    row += '<td class="' + classes.attributeClass +
                        '" attribute-id="' + item.id + '" attribute-code="' + item.code + '" >' + options + '</td>';
                } else if (key === 'price') {
                    allPrice     = widget._GetMpAllPrice(widget, productId);
                    oldPrice     = allPrice.oldPrice.amount;
                    finalPrice   = widget.getFormattedPrice(widget._GetMpPrice(widget, productId));
                    style        = '';
                    tierPrice    = '';
                    specialPrice = '';

                    if (allPrice.finalPrice.amount !== oldPrice) {
                        specialPrice = '<br><span class="mpcpgv-special">'
                            + widget.getFormattedPrice(oldPrice) + '</span>';
                        style        = 'style="color: #f11919"';

                    }
                    if (!_.isEmpty(allPrice.tierPrices)) {
                        tierPrice = '<br><span class="mpcpgv-icon"><img style="height: 25px;width: 25px" src="/media/icons/tierprice.png"></span>';
                    }
                    row += '<td class="mpcpgv-' + key + '"><span class="mpcpgv-unit-price" ' + style + '>'
                        + finalPrice + '</span>' + specialPrice + tierPrice + '</td>';
                } else if (key == 'subtotal'){
                    row += '';
                } else if (key === 'stock'){
                    row += '<td class="mpcpgv-' + key + '">' + item + '</td>';
                } else if (key == 'sku'){



                    row += '<td class="mpcpgv-' + key + '">' + item;



                    if(product.conditionnement){
                        row += '<br>';
                        var data = product.conditionnement+ '';
                        var arr = data.split(',');
                        $.each(arr, function( index, value ) {

                            var isconditionnement = $.trim(value);

                            row +='<a href="#" data-conditionnemment="' + isconditionnement + '" class="add"><img src="/media/icons/carton.png"> ' + isconditionnement + 'pcs</a>';
                        });

                    }


                    if(product.precommande == 'Oui'){
                        row += '<br><div class="preorder">Précommande</div>';
                    }
                    row += '</td>';
                } else if (key !== 'isBackorders' && key !== 'precommande' && !key.includes('_text') && key !== 'id' && key !== 'conditionnement'){
                    row += '<td class="mpcpgv-' + key + '">' + item + '</td>';
                }
            });
            row += '</tr>';




            container.append(row);
        },

        /**
         * Get selected product list
         *
         * @returns {Array}
         * @private
         */
        _CalcProducts: function ($skipAttributeId) {
            var $widget  = this,
                products = [];

            // Generate intersection of products
            $widget.formContainer.find('#mpcpgv-attribute-inactive').find('.' + $widget.options.classes.attributeClass
                + '[option-selected]').each(function () {
                products = $widget.getProducts(this, $skipAttributeId);
            });

            $widget.formContainer.find('#mpcpgv-attribute-table, #mpcpgv-attribute-inactive').find('.'
                + $widget.options.classes.attributeClass).each(function () {
                if ($(this).find('.swatch-option.selected').length) {
                    products = $widget.getProducts(this, $skipAttributeId);
                }
            });

            return products;
        },

        getProducts: function (el, $skipAttributeId) {
            var $widget  = this,
                products = [],
                id       = $(el).attr('attribute-id'),
                option   = $(el).find('.swatch-option.selected').attr('attribute-value');

            if ($skipAttributeId !== undefined && $skipAttributeId === id) {
                return;
            }

            if (!$widget.optionsMap.hasOwnProperty(id) || !$widget.optionsMap[id].hasOwnProperty(option)) {
                return;
            }

            if (products.length === 0) {
                products = $widget.optionsMap[id][option].products;
            } else {
                products = _.intersection(products, $widget.optionsMap[id][option].products);
            }

            return products;
        },

        getFormattedPrice: function (price) {
            return priceUtils.formatPrice(price);
        },

        _Rebuild: function () {
            var $widget  = this,
                controls = $widget.formContainer.find('#mpcpgv-attribute-inactive').find('.' + $widget.options.classes.attributeClass + '[attribute-id]'),
                selected = controls.filter('[option-selected]');

            // Enable all options
            $widget._Rewind(controls);

            // done if nothing selected
            if (selected.length <= 0) {
                return;
            }

            // Disable not available options
            controls.each(function () {
                var $this    = $(this),
                    id       = $this.attr('attribute-id'),
                    products = $widget._CalcProducts(id);

                if (selected.length === 1 && selected.first().attr('attribute-id') === id) {
                    return;
                }

                $this.find('[option-id]').each(function () {
                    var $element = $(this),
                        option   = $element.attr('option-id');

                    if (!$widget.optionsMap.hasOwnProperty(id) || !$widget.optionsMap[id].hasOwnProperty(option) ||
                        $element.hasClass('selected') ||
                        $element.is(':selected')) {
                        return;
                    }

                    if (_.intersection(products, $widget.optionsMap[id][option].products).length <= 0) {
                        $element.attr('disabled', true).addClass('disabled');
                    }
                });
            });
        },
    });





    return $.mageplaza_configurable.SwatchRenderer;
});
