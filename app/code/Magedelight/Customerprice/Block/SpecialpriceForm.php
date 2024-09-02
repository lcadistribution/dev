<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class SpecialpriceForm extends Template
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;


    /**
     * @var \Magedelight\Customerprice\Helper\Data
     */
    protected $helper;

    /**
     * @var ProductRepositoryInterfacea
     */
    protected $productRepository;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    protected $product;

    protected $stockRegistry;
    
    public function __construct(
        Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magedelight\Customerprice\Helper\Data $helper,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        StoreManagerInterface $storeManager,
        StockRegistryInterface $stockRegistry,
        array $data = []
    ) {

        parent::__construct($context, $data);
        $this->request = $request;
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
        $this->stockRegistry = $stockRegistry;
    }
    
    
    public function isSpecialpriceFormEnable()
    {
        if ($this->helper->isEnabled() && $this->helper->specialPriceButton()) {
            return true;
        } else {
            return false;
        }
    }

    public function getHelper()
    {
        return $this->helper;
    }

    public function getProduct()
    {
        $pid = $this->getData('productid');
        $this->product = $this->productRepository->getById($pid);
        return $this->product;
    }

    public function getFinalPriceInBaseCurrency()
    {
        $product = $this->getProduct();
        $finalPrice = $product->getPrice();
        $baseCurrencyCode = $this->storeManager->getStore()->getBaseCurrencyCode();
        $finalPriceInBaseCurrency = $this->priceCurrency->convertAndRound($finalPrice, $scope = null, $baseCurrencyCode);
        return $finalPriceInBaseCurrency;
    }

    public function getProductName()
    {
        return $this->product->getName();
    }

    public function getQtyIsDecimals(){
        $stockManager = $this->stockRegistry->getStockItem($this->product->getId());
        return $stockManager->getIsQtyDecimal();
    }
}
