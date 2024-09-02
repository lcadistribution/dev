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
 * @category  Mageplaza
 * @package   Mageplaza_ConfigureGridView
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ConfigureGridView\Plugin\Helper;

use Magento\ConfigurableProduct\Helper\Data as ConfigurableProductHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\ConfigureGridView\Helper\Data as ConfigureHelper;

/**
 * Class Data
 * @package Mageplaza\ConfigureGridView\Plugin\Helper
 */
class Data
{
    /**
     * @var ConfigureHelper
     */
    protected $helperData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Data constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param ConfigureHelper $helperData
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ConfigureHelper $helperData
    ) {
        $this->helperData   = $helperData;
        $this->storeManager = $storeManager;
    }

    /**
     * @param ConfigurableProductHelper $subject
     * @param $currentProduct
     * @param $allowedProducts
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function beforeGetOptions(
        ConfigurableProductHelper $subject,
        $currentProduct,
        $allowedProducts
    ) {
        $storeId = $this->storeManager->getStore()->getId();

        if ($this->helperData->getDisplay('out_of_stock', $storeId)) {
            foreach ($allowedProducts as $product) {
                $product->setData('is_salable', true);
            }
        }

        return [$currentProduct, $allowedProducts];
    }
}
