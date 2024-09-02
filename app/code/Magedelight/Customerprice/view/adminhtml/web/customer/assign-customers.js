/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@Magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Customerprice
 * @copyright Copyright (c) 2017 Mage Delight (http://www.Magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@Magedelight.com>
 */

/* global $, $H */

define([
    'mage/adminhtml/grid'
], function () {
    'use strict';

    return function (config) {
        var selectedProducts = '',
            categoryProducts = $H(selectedProducts),
            gridJsObject = window[config.gridJsObjectName],
            tabIndex = 1000;
            
        //$('in_category_customer').value = Object.toJSON(categoryProducts);

        /**
         * Register Category Product
         *
         * @param {Object} grid
         * @param {Object} element
         * @param {Boolean} checked
         */
        function registerCategoryProduct(grid, element, checked)
        {
            var  customcategoryProducts = {};
            var count = 0;
            jQuery('#catalog_category_customer_table tr .customercustom').each(function () {
                if (jQuery(this).is(':checked')) {
                    var temp = {
                        'name' : jQuery(this).attr('rel'),
                        'email' : jQuery(this).attr('relcust'),
                        'value' : jQuery(this).attr('value')
                    }
                    customcategoryProducts[count] = temp;
                    count++;
                }
            });
            console.log(customcategoryProducts);
//            if (checked) {
//                categoryProducts.set('name'+element.value  , element.getAttribute('rel'));
//                categoryProducts.set('email'+element.value ,element.getAttribute('relcust'));
//                categoryProducts.set(element.value ,element.value);
//            } else {
//               categoryProducts.unset('name'+element.value);
//               categoryProducts.unset('email'+element.value);
//               categoryProducts.unset(element.value);
//            }
            console.log(Object.toJSON(customcategoryProducts));
            $('in_category_customer').value = Object.toJSON(customcategoryProducts);
            grid.reloadParams = {
                'selected_products[]': customcategoryProducts.keys()
            };
        }

        /**
         * Click on product row
         *
         * @param {Object} grid
         * @param {String} event
         */
        function categoryProductRowClick(grid, event)
        {
            var trElement = Event.findElement(event, 'tr'),
                isInput = Event.element(event).tagName === 'INPUT',
                checked = false,
                checkbox = null;

            if (trElement) {
                checkbox = Element.getElementsBySelector(trElement, 'input');

                if (checkbox[0]) {
                    checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                    gridJsObject.setCheckboxChecked(checkbox[0], checked);
                }
            }
        }

        /**
         * Change product position
         *
         * @param {String} event
         */
        function positionChange(event)
        {
            var element = Event.element(event);

            if (element && element.checkboxElement && element.checkboxElement.checked) {
                //categoryProducts.set(element.checkboxElement.value, element.value);
                //$('in_category_customer').value = Object.toJSON(categoryProducts);
            }
        }

        /**
         * Initialize category product row
         *
         * @param {Object} grid
         * @param {String} row
         */
        function categoryProductRowInit(grid, row)
        {
            var checkbox = $(row).getElementsByClassName('checkbox')[0],
                position = $(row).getElementsByClassName('input-text')[0];

            if (checkbox && position) {
                checkbox.positionElement = position;
                position.checkboxElement = checkbox;
                position.disabled = !checkbox.checked;
                position.tabIndex = tabIndex++;
                Event.observe(position, 'keyup', positionChange);
            }
        }

        gridJsObject.rowClickCallback = categoryProductRowClick;
        //gridJsObject.initRowCallback = categoryProductRowInit;
        gridJsObject.checkboxCheckCallback = registerCategoryProduct;

        if (gridJsObject.rows) {
            gridJsObject.rows.each(function (row) {
               // categoryProductRowInit(gridJsObject, row);
            });
        }
    };
});
