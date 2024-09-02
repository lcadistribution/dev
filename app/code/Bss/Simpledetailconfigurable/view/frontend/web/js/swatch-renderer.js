/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'underscore',
    'jquery/ui',
    'jquery/jquery.parsequery'
], function ($, _) {
    'use strict';

    window.sdcp_swatch_renderer = true;

    return function (widget) {

        $.widget('bss.SwatchRenderer', widget, {

            /**
             * Event for swatch options
             *
             * @param {Object} $this
             * @param {Object} $widget
             * @private
             */
            _OnClick: function ($this, $widget) {
                var options = $widget.options;
                if (!$widget.inProductList) {
                    var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                        $wrapper = $this.parents('.' + $widget.options.classes.attributeOptionsWrapper),
                        $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                        attributeId = $widget.getDataofAttr($parent, 'attribute-id'),
                        $input = $parent.find('.' + $widget.options.classes.attributeInput),
                        checkAdditionalData = this.options.jsonSwatchConfig[attributeId]['additional_data'] ?
                            JSON.parse(this.options.jsonSwatchConfig[attributeId]['additional_data']) : [];

                    if ($widget.inProductList) {
                        $input = $widget.productForm.find(
                            '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                        );
                    }

                    if ($this.hasClass('disabled')) {
                        return;
                    }

                    if ($this.hasClass('selected')) {
                        $parent.removeAttr('data-option-selected option-selected').find('.selected').removeClass('selected');
                        $input.val('');
                        $label.text('');
                        $this.attr('aria-checked', false);
                    } else {
                        var optionId = $widget.getDataofAttr($this, 'option-id');
                        $parent.attr({'data-option-selected': optionId, 'option-selected': optionId}).find('.selected').removeClass('selected');
                        $label.text($widget.getDataofAttr($this, 'option-label'));
                        $input.val(optionId);
                        $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
                        $this.addClass('selected');
                        $widget._toggleCheckedAttributes($this, $wrapper);
                    }

                    $widget._Rebuild();

                    if ($widget.element.parents($widget.options.selectorProduct)
                        .find(this.options.selectorProductPrice).is(':data(mage-priceBox)')
                    ) {
                        $widget._UpdatePrice();
                    }

                    $(document).trigger('updateMsrpPriceBlock',
                        [
                            _.findKey($widget.options.jsonConfig.index, $widget.options.jsonConfig.defaultValues),
                            $widget.options.jsonConfig.optionPrices
                        ]);
                    if (!window.bssGallerySwitchStrategy || window.bssGallerySwitchStrategy != 'disabled') {
                        if ((typeof checkAdditionalData['update_product_preview_image'] !== 'undefined') &&
                            parseInt(checkAdditionalData['update_product_preview_image'], 10) === 1) {
                            $widget._loadMedia();
                        }
                    }

                    $input.trigger('change');

                    window.selectedSwatch = true;
                    if (window.compatible_improved > 0) {
                        $widget._super($this, $widget);
                    }
                    window.selectedSwatch = false;
                }
                if ($widget.inProductList) {
                    $widget._super($this, $widget);
                    var childProductData = this.options.jsonConfig.bss_simple_detail;
                    if (!$.isEmptyObject(childProductData)
                        && childProductData && childProductData.child
                        && !$.isEmptyObject(childProductData.child)
                        && childProductData.child.default.enable_sdcp // Check enable module on this product
                    ) {
                        if (options.jsonConfig.is_enable_swatch_name){
                            $widget._UpdateProductName($this);
                        }
                        $widget._UpdateUrl($widget, $this.parent().parent().parent());
                    }
                    if (!$.isEmptyObject(childProductData)
                        && childProductData && childProductData.child
                        && !$.isEmptyObject(childProductData.child)
                        && this.options.jsonConfig.is_enable_rating_reviews
                    ) {
                        $widget._UpdateReviewsChild($this);
                    }
                }
            },

            /**
             * Update product name
             *
             * @param ele
             * @private
             */
            _UpdateProductName: function (ele) {
                var index = '',
                    childProductData = this.options.jsonConfig.bss_simple_detail,
                    $productName,
                    $widget = this;

                ele.parents(".product-item-details").find(".super-attribute-select").each(function () {
                    var option_id = $widget.getDataofAttr($(this), "option-selected");
                    if (typeof option_id === "undefined" && $(this).val() !== "") {
                        option_id = $(this).val();
                    }
                    if (option_id !== null && $(this).val() !== "") {
                        index += option_id + '_';
                    }
                });

                if (!childProductData['child'].hasOwnProperty(index)) {
                    this._ResetName(ele);
                    return false;
                }
                $productName = childProductData['child'][index]['name'];
                if ($productName) {
                    ele.parents(".product-item-details").find('.product-item-link').text($productName);
                }
            },

            _UpdateUrl: function ($widget, ele)
            {
                var options = {};
                var prefix = '';
                var urlCustom = '';
                if (ele.find('.swatch-attribute[data-option-selected]')) {
                    prefix = 'data-';
                }
                var selectedElement = ele.find('.swatch-attribute[' + prefix + 'option-selected]');
                selectedElement.each(function () {
                    var attributeId = $widget.getDataofAttr($(this), prefix + 'attribute-id');
                    options[attributeId] = $widget.getDataofAttr($(this), prefix + 'option-selected');
                    var optionLabel;
                    $(this).find('.swatch-option.selected, .swatch-select option:selected').each(function () {
                        if ($(this).hasClass('swatch-option')) {
                            optionLabel = $widget.getDataofAttr($(this), prefix + 'option-label');
                        } else {
                            optionLabel = $(this).text();
                        }
                    });

                    urlCustom += '+' + $widget.getDataofAttr($(this), prefix + 'attribute-code') + '-' + optionLabel;
                });

                var urlSelector = ele.parents('.product-item-info').find('a.product-item-photo, a.product-item-link');
                var suffix = this.options.jsonConfig.url_suffix === null ? "" : this.options.jsonConfig.url_suffix;
                urlSelector.each(function() {
                    var urlOld = $(this).attr('href').replace(suffix, '');
                    var urlChild = urlOld.split('+')[0] + urlCustom + suffix;
                    $(this).attr('href', urlChild);
                });
            },

            /**
             * Update product name
             *
             * @param ele
             * @private
             */
            _UpdateReviewsChild: function (ele) {
                var index = '',
                    childProductData = this.options.jsonConfig.bss_simple_detail,
                    $summary,
                    $reviewsCount,
                    $path,
                    $name,
                    $widget = this;

                ele.parents(".product-item-details").find(".super-attribute-select").each(function () {
                    var option_id = $widget.getDataofAttr($(this), "option-selected");
                    if (typeof option_id === "undefined" && $(this).val() !== "") {
                        option_id = $(this).val();
                    }
                    if (option_id !== null && $(this).val() !== "") {
                        index += option_id + '_';
                    }
                });

                if (!childProductData['child'].hasOwnProperty(index)) {
                    this._ResetParentReviews(ele);
                    return false;
                }
                $summary = childProductData['child'][index]['summary'];
                $reviewsCount = childProductData['child'][index]['reviews'];
                $path = ele.parents(".product-item-details").parents(".product-item-info").find(".product-item-photo").attr("href").concat('#reviews');
                this._renderReviewChild(ele, $summary, $reviewsCount, $path);
            },

            /**
             * Reset default product name
             * @param ele
             * @param $summary
             * @param $reviewsCount
             * @param $path
             * @private
             */
            _renderReviewChild: function (ele, $summary, $reviewsCount, $path) {
                if ($reviewsCount === 0) {
                    ele.parents(".product-item-details").find('.product-reviews-summary.short').find('.rating-summary').remove();
                    ele.parents(".product-item-details").find('.product-reviews-summary.short').find('.reviews-actions').remove();
                } else {
                    if(!ele.parents(".product-item-details").find('.product-reviews-summary.short').length) {
                        ele.parents(".product-item-details").find('.product-item-name').after(this.parentReviewRender());
                    }
                    ele.parents(".product-item-details").find('.product-reviews-summary.short').find('.rating-summary').remove();
                    ele.parents(".product-item-details").find('.product-reviews-summary.short').find('.reviews-actions').remove();
                    ele.parents(".product-item-details").find('.product-reviews-summary.short').append(this.reviewRender());
                    $summary = $summary + '%';
                    ele.parents(".product-item-details").find('.product-reviews-summary.short').find('#countReview').text($reviewsCount);
                    ele.parents(".product-item-details").find('.product-reviews-summary.short').find('#ratingValue').text($reviewsCount);
                    ele.parents(".product-item-details").find('.product-reviews-summary.short').find('.rating-result').css("width", $summary);
                    ele.parents(".product-item-details").find('.product-reviews-summary.short').find('.reviews-actions').find('.action.view').prop("href", $path);
                }
            },

            /**
             * Reset default product name
             * @param ele
             * @private
             */
            _ResetParentReviews: function (ele) {
                var childProductData = this.options.jsonConfig.bss_simple_detail,
                    $summary = childProductData['child']['default']['summary'],
                    $reviewCount = childProductData['child']['default']['reviews'],
                    $path = ele.parents(".product-item-details").parents(".product-item-info").find(".product-item-photo").attr("href").concat('#reviews'),
                    productName = childProductData['child']['default']['name'];
                if (productName) {
                    this._renderReviewChild(ele, $summary, $reviewCount, $path);
                }
            },

            /**
             * Reset default product name
             * @param ele
             * @private
             */
            _ResetName: function (ele) {
                var childProductData = this.options.jsonConfig.bss_simple_detail,
                    productName = childProductData['child']['default']['name'];
                if (productName) {
                    ele.parents(".product-item-details").find('.product-item-link').text(productName);
                }
            },

            /**
             * Get attribute value,
             * Compatible with M2.3x and M2.4
             * Reason: Some important attributes were changed format (data-attribute in stead of attribute)
             *
             * @param element
             * @param name
             * @returns {*}
             */
            getDataofAttr(element, name) {
                var attr = element.attr(name);
                if (undefined !== attr && attr && attr.length) {
                    return attr;
                }
                return element.data(name);
            },

            reviewRender() {
                return  '  <div class="rating-summary">' +
                    '       <div class="rating-result">' +
                    '           <span style="width: 100%">' +
                    '               <span>' +
                    '                   <span itemprop="ratingValue" id ="valueRating">' +
                    '                   </span>% of <span itemprop="bestRating">100</span>' +
                    '               </span>' +
                    '           </span>' +
                    '       </div>' +
                    '     </div>' +
                    '     <div class="reviews-actions">' +
                    '   <a class="action view">' +
                    '  <span itemprop="reviewCount" id="countReview"></span>&nbsp;' +
                    '<span>Reviews                </span>' +
                    ' </a>' +
                    '     </div>';
            },

            parentReviewRender() {
                return '<div class="product-reviews-summary short"></div>'
            }
        });

        return $.bss.SwatchRenderer;
    }
});
