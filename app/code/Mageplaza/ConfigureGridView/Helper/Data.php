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
 * @package     Mageplaza_ConfigureGridView
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ConfigureGridView\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\ConfigureGridView\Model\Config\Source\Product as Active;
use Mageplaza\Core\Helper\AbstractData;

/**
 * Class Data
 * @package Mageplaza\ConfigureGridView\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mpcpgv';

    /**
     * @var HttpContext
     */
    protected $_httpContext;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param HttpContext $httpContext
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        HttpContext $httpContext
    ) {
        $this->_httpContext = $httpContext;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * Get Tablet Columns
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getTabletColumns($storeId = null)
    {
        return $this->getDisplay('tablet', $storeId);
    }

    /**
     * @param string $field
     * @param null $storeId
     *
     * @return mixed
     */
    public function getDisplay($field = '', $storeId = null)
    {
        return $this->getModuleConfig('display/' . $field, $storeId);
    }

    /**
     * Get Mobile Columns
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getMobileColumns($storeId = null)
    {
        return $this->getDisplay('mobile', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isShowOutOfStock($storeId = null)
    {
        return $this->getDisplay('out_of_stock', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isShowDetail($storeId = null)
    {
        return $this->getDisplay('detail', $storeId);
    }

    /**
     * @param Product $product
     * @param null $storeId
     *
     * @return bool
     */
    public function checkEnableModule($product, $storeId = null)
    {
        $customerGroupConfig = explode(',', $this->getCustomerGroups($storeId) ?? '');
        $customerGroupId     = $this->_httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP);

        /** @var int $customerGroupId */
        /** @var array $customerGroupConfig */
        /** @var integer $isView */
        if (!$this->isEnabled($storeId)
            || !in_array((string) $customerGroupId, $customerGroupConfig, true)
            || $product->getData('mp_gridview') === Active::INACTIVE
        ) {
            return false;
        }

        return $this->isEnabled($storeId);
    }

    /**
     * Get customer groups
     *
     * @param null $storeId
     *
     * @return bool|mixed
     */
    public function getCustomerGroups($storeId = null)
    {
        return $this->getConfigGeneral('customer_group', $storeId);
    }

    /**
     * Get Default Columns
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getDefaultColumns($storeId = null)
    {
        return $this->getDisplay('default', $storeId);
    }

    /**
     * Check enable sort
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getSortEnabled($storeId = null)
    {
        return $this->getConfigGeneral('sort_enabled', $storeId);
    }
}
