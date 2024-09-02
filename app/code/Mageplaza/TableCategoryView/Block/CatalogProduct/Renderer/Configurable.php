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
 * @package     Mageplaza_TableCategoryView
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\TableCategoryView\Block\CatalogProduct\Renderer;

use Magento\Swatches\Block\Product\Renderer\Configurable as MagentoConfigurable;

/**
 * Class Configurable
 * @package Mageplaza\ConfigureGridView\Block\Product\Renderer
 */
class Configurable extends MagentoConfigurable
{
    const SWATCH_RENDERER_TEMPLATE = 'Mageplaza_TableCategoryView::product/view/renderer.phtml';

    /**
     * Return renderer template
     *
     * Template for product with swatches is different from product without swatches
     *
     * @return string
     */
    protected function getRendererTemplate()
    {
        return $this->isProductHasSwatchAttribute()
            ? self::SWATCH_RENDERER_TEMPLATE
            : self::CONFIGURABLE_RENDERER_TEMPLATE;
    }

    /**
     * @return array
     */
    public function getAllowAttributes()
    {
        if ($this->getProduct()->getTypeId() !== 'configurable') {
            return [];
        }

        return parent::getAllowAttributes();
    }
}
