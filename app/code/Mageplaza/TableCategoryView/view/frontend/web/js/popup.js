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
    'Magento_Ui/js/modal/modal'
], function ($, $t, _, modal) {
    'use strict';

    $.widget('mageplaza.mpTableCategoryPopup', {
            options: {
                url: '',
                storeId: 0
            },
            _create: function () {
                var self = this;

                $('.mptablecategory-popup-click').on('click', function (e) {
                    var $this     = $(this),
                        product   = $this.parents('.product-item-info'),
                        productId = product.data().productid,
                        url       = self.options.url + 'id/' + productId + '?mptable=1',
                        storeId   = self.options.storeId,
                        options  = {},
                        popupModal = {},
                        htmlPopup = $('#mptablecategory-popup-'+productId);

                    e.preventDefault();
                    e.stopPropagation();

                    if (htmlPopup.data('mageModal')){
                        htmlPopup.data('mageModal').openModal();
                    }else{
                        $.ajax({
                            url: url,
                            type: "post",
                            showLoader: false,
                            data: {
                                productIds: productId,
                                storeId: storeId
                            },
                            success: function (html) {
                                options = {
                                    'type': 'popup',
                                    'title': $t('Sélectionnez les options'),
                                    'responsive': true,
                                    'innerScroll': true,
                                    'buttons': []
                                };
                                htmlPopup.html(html);
                                popupModal = modal(options, htmlPopup);
                                popupModal.openModal();

                                htmlPopup.trigger('contentUpdated');
                            },
                            complete: function () {
                                $this.removeAttr('disabled');
                            }
                        });
                    }
                });
            }
        }
    );

    return $.mageplaza.mpTableCategoryPopup;
});
