<?php
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
 * @copyright  Copyright (c) 2017-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Model;
use Magento\Store\Model\StoreManagerInterface;

class Product
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected  $productRepository;


    /**
     * @var StoreManagerInterface
     */
    private $storeManager;


    /**
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
    }


    /**
     * Get child product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $saleableItem
     * @return \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product|mixed|null
     */
    public function getProduct($saleableItem)
    {
        try {
            $sku = "";
            if ($saleableItem->getData("sdcp_data") && !$saleableItem->getData("preselect_data")) {
                $sku = $saleableItem->getSku();

            } elseif($saleableItem->getData("preselect_data")) {
                $sku = $saleableItem->getTypeInstance()->getProductByAttributes($saleableItem->getData("preselect_data"), $saleableItem)->getData("sku");
            }
            if ($sku) {
                $saleableItem = $this->productRepository->get($sku, false, $this->storeManager->getStore()->getId());
            }
        } catch (\Exception $exception) {

        }
        return $saleableItem;
    }
}
