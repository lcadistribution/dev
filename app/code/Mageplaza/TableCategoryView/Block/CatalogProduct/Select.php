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

namespace Mageplaza\TableCategoryView\Block\CatalogProduct;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Framework\View\Element\Template;
use Mageplaza\TableCategoryView\Helper\Data;

/**
 * Class Select
 * @package Mageplaza\TableCategoryView\Block\CatalogProduct
 */
class Select extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_TableCategoryView::product/select.phtml';

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Select constructor.
     *
     * @param Template\Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     *
     * @return bool|ProductCustomOptionInterface[]
     */
    public function getOptionsProduct()
    {
        return Data::jsonEncode($this->helper->getOptionsProduct());
    }

    /**
     *
     */
    public function getProductId()
    {
        return $this->getRequest()->getParam('id');
    }
}
