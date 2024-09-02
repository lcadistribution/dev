<?php
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
 * @package     Mageplaza_LayeredNavigationPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationPro\Block\Adminhtml\System;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\LayeredNavigationPro\Helper\Data;

/**
 * Class CategoryRating
 * @package Mageplaza\LayeredNavigationPro\Block\Adminhtml\System
 */
class CategoryRating extends AbstractCategory
{
    const CONFIG_DATA = 'rating';

    /**
     * @inheritdoc
     */
    public function _getElementHtml(AbstractElement $element)
    {
        $html = '<div class="admin__field-control">';

        $html .= '<div id="layered_navigation_rating_categories"  class="admin__field" data-bind="scope:\'rating_categories\'" data-index="index">';
        $html .= '<!-- ko foreach: elems() -->';
        $html .= '<input class="input-text admin__control-text" type="text" name="groups[filter][groups][rating][fields][categories][value]" data-bind="value: value" style="display: none;"/>';
        $html .= '<!-- ko template: elementTmpl --><!-- /ko -->';
        $html .= '<!-- /ko -->';
        $html .= '</div>';

        $html .= $this->getScriptHtml() . $this->getScripHtmlAddDisable();

        return $html;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getScriptHtml()
    {
        return '<script type="text/x-magento-init">
            {
                "#layered_navigation_rating_categories": {
                    "Magento_Ui/js/core/app": {
                        "components": {
                            "rating_categories": {
                                "component": "uiComponent",
                                "children": {
                                    "select_category": {
                                        "component": "Magento_Catalog/js/components/new-category",
                                        "config": {
                                            "filterOptions": true,
                                            "disableLabel": true,
                                            "chipsEnabled": true,
                                            "levelsVisibility": "1",
                                            "elementTmpl": "ui/grid/filters/elements/ui-select",
                                             "options": ' . Data::jsonEncode($this->getOptions()) . ',
                                            "value": ' . Data::jsonEncode($this->getValues(self::CONFIG_DATA)) . ',
                                            "listens": {
                                                "index=create_category:responseData": "setParsed",
                                                "newOption": "toggleOptionSelected"
                                            },
                                            "config": {
                                                "dataScope": "select_category",
                                                "sortOrder": 10
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        </script>';
    }
}
