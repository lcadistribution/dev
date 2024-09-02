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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Simpledetailconfigurable\Plugin\Catalog\Controller\Product;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;

class View
{
    /**
     * Const
     */
    const CACHE_INSTANCE_USED_PRODUCT_ATTRIBUTES = '_cache_instance_used_product_attributes';

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $resultRedirectFactory;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ProductData
     */
    protected $productData;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    protected $helper;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * View construct.
     *
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
     * @param ProductAttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        \Magento\Framework\Controller\Result\RedirectFactory         $resultRedirectFactory,
        \Magento\Catalog\Helper\Product                              $productHelper,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\Catalog\Model\ProductRepository                     $productRepository,
        ProductAttributeRepositoryInterface                          $attributeRepository,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig            $helper,
        \Bss\Simpledetailconfigurable\Helper\ProductData             $productData
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->productHelper = $productHelper;
        $this->configurable = $configurable;
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->helper = $helper;
        $this->productData = $productData;
    }

    /**
     * Product view action
     *
     * @param \Magento\Catalog\Controller\Product\View $subject
     * @param \Closure $proceed
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundExecute(
        \Magento\Catalog\Controller\Product\View $subject,
        \Closure                                 $proceed
    ) {
        if (!$this->helper->isModuleEnable()) {
            return $proceed();
        }

        try {
            $productId = (int)$subject->getRequest()->getParam('id');
            $productChild = $this->productRepository->getById($productId);

            if ($productChild->getRedirectToConfigurableProduct()) {
                $parentIds = $this->configurable->getParentIdsByChild($productId);
                $parentId = array_shift($parentIds);
                if ($parentId && $this->productData->getEnabledModuleOnProduct($parentId)->getEnabled()) {
                    $productParentId = (int)$parentId;
                    $product = $this->productRepository->getById($productParentId);

                    if ($product) {
                        return $this->setRedirectUrl($product, $productChild);
                    }
                }
            }

            return $proceed();
        } catch (\Exception $e) {
            return $proceed();
        }
    }

    /**
     * Set url before redirect product.
     *
     * @param \Magento\Catalog\Model\Product|mixed $product
     * @param \Magento\Catalog\Model\Product|mixed $productChild
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setRedirectUrl($product, $productChild)
    {
        $attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
        $paramRedirect = '?+';
        foreach ($attributes as $attribute) {
            $attrCode = $this->getAttributeCode($attribute->getAttributeId());
            $valueAttribute = str_replace("/", "", $productChild->getAttributeText($attrCode));
            $paramRedirect .= $attrCode . '-' . $valueAttribute . '+';
        }
        $paramRedirect .= 'sdcp-redirect';

        $resultRedirect = $this->resultRedirectFactory->create();
        $url = $this->getUrlRedirect($product, $paramRedirect);
        $resultRedirect->setUrl($url);

        return $resultRedirect;
    }

    /**
     * Get url redirect, compatible with module quick view.
     *
     * @param \Magento\Catalog\Model\Product|mixed $product
     * @param string $paramRedirect
     * @return string
     */
    public function getUrlRedirect($product, $paramRedirect)
    {
        return $product->getProductUrl() . $paramRedirect;
    }

    /**
     * Get attribute code
     *
     * @param int|string $attributeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAttributeCode($attributeId)
    {
        return $this->attributeRepository->get($attributeId)->getAttributeCode();
    }
}
