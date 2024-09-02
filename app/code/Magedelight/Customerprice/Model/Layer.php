<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel;

class Layer extends \Magento\Catalog\Model\Layer
{
    
    protected $_productCollection = [];
     
    protected $catalogConfig;
      
    protected $productVisibility;
     
    /**
     * @param ContextInterface $context
     * @param StateFactory $layerStateFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $catalogProduct
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Framework\Registry $registry
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface $categoryprice
     * @param  \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerprice
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable
     * @param \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped
     * @param \Magento\Bundle\Model\Product\Type $type
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Catalog\Model\ProductFactory $productcollectionFactory
     * @param \Magedelight\Customerprice\Model\ResourceModel\CustomerpriceDiscount\CollectionFactory $discountFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\ContextInterface $context,
        \Magento\Catalog\Model\Layer\StateFactory $layerStateFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $catalogProduct,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface $categoryprice,
        \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerprice,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped,
        \Magento\Bundle\Model\Product\Type $type,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Registry $registry,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\ProductFactory $productcollectionFactory,
        \Magedelight\Customerprice\Model\ResourceModel\CustomerpriceDiscount\CollectionFactory $discountFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $layerStateFactory,
            $attributeCollectionFactory,
            $catalogProduct,
            $storeManager,
            $registry,
            $categoryRepository,
            $data
        );
        $this->request = $request;
        $this->catalogConfig = $catalogConfig;
        $this->productVisibility = $productVisibility;
        $this->customerSession = $customerSession;
        $this->configurable = $configurable;
        $this->grouped = $grouped;
        $this->categoryFactory = $categoryFactory;
        $this->type = $type;
        $this->customerprice = $customerprice;
        $this->categoryprice = $categoryprice;
        $this->mdproductcollection = $productcollectionFactory;
        $this->discountFactory = $discountFactory;
    }

    public function getProductCollection()
    {

        $customerId = $this->customerSession->create()->getCustomerId();

        if (isset($this->_productCollection[$customerId])) {
            $collection = $this->_productCollection[$customerId];
        } else {
            $productIds = $this->getCustomerProductIds();
            $collection = $this->mdproductcollection->create()->getCollection();
            if (!empty($productIds)) {
                $collection->addAttributeToSelect('*')->addIdFilter($productIds);
            } else {
                $discountLoad = $this->discountFactory->create()
                        ->addFieldToFilter('customer_id', ['eq' => $customerId])
                        ->getFirstItem();
                if ($discountLoad->getDiscountId()) {
                    $collection->addFieldToFilter('entity_id', '0');
                } else {
                    $collection->addFieldToFilter('entity_id', '0');
                }
            }
            /* else {
                $collection->addFieldToFilter('entity_id', '0');
            }*/
            $this->prepareProductCollection($collection);
            if (!empty($collection)) {
                $this->_productCollection[$customerId] = $collection;
            } else {
                $this->_productCollection[$customerId] = '';
            }
        }
        return $collection->distinct(true);
    }

    public function prepareProductCollection($collection)
    {
        $collection
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->setStore($this->_storeManager->getStore())
            ->addMinimalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->setVisibility($this->productVisibility->getVisibleInCatalogIds());

        return $this;
    }
    
    public function getCategories()
    {
        $customerId = $this->customerSession->create()->getCustomerId();
        $collections = $this->categoryprice->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $customerId])
                ->getColumnValues('category_id');
        return $collections;
    }
    
    public function getCustomerProductIds()
    {
        $customerId = $this->customerSession->create()->getCustomerId();
        $collections = $this->customerprice->getCollection()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        $product_ids = [];
        $parent_product_ids = [];
        if ($collections->getSize() > 0) {
            foreach ($collections as $product) {
                $product_ids[] = $product->getProductId();
                $_product = $this->mdproductcollection->create()->load($product->getProductId());
                if ($_product->getVisibility() == 1) {
                    $parent_product_ids[] = $this->configurable->getParentIdsByChild($product->getProductId());
                    $product_ids = array_diff($product_ids, [$product->getProductId()]);
                }
                //Grouped Product
                $parent_product_ids[] = $this->grouped->getParentIdsByChild($product->getProductId());
                //Bundle Product
                $parent_product_ids[] = $this->type->getParentIdsByChild($product->getProductId());
            }
            foreach ($parent_product_ids as $ids) {
                if (is_array($ids) && !empty($ids)) {
                    $product_ids[] = $ids[0];
                }
            }
            $product_ids = array_unique($product_ids);
        }
        $categoryIds = $this->getCategories();
//        $productCatIds = [];
//        //echo "<pre>";
//        //print_r($categoryIds);
//
//        foreach ($categoryIds as $categoryId) {
//            //echo $categoryId;
//            $product_collection = $this->categoryFactory->create()->load($categoryId)->getProductCollection()->addAttributeToSelect('*')->getColumnValues('entity_id');
//            //print_r($product_collection);
//            $productCatIds = array_merge($productCatIds,$product_collection);
//        }
        
        
        $collectioncat = $this->mdproductcollection->create()->getCollection()->addAttributeToSelect('entity_id');
        $collectioncat->addCategoriesFilter(['in'=> $categoryIds]);
        $prod = [];
        foreach ($collectioncat as $collectionprod) {
            $prod[] = $collectionprod->getId();
        }
        $product_ids_cat = [];
        $parent_product_ids_cat = [];
        if (count($prod) > 0) {
            foreach ($prod as $productid) {
                $product_ids_cat[] = $productid;
            
                $_product = $this->mdproductcollection->create()->load($productid);
            
                if ($_product->getVisibility() == 1) {
                    $parent_product_ids_cat[] = $this->configurable->getParentIdsByChild($productid);
                    $product_ids_cat = array_diff($product_ids_cat, [$productid]);
                }
                //Grouped Product
                $parent_product_ids_cat[] = $this->grouped->getParentIdsByChild($productid);
                //Bundle Product
                $parent_product_ids_cat[] = $this->type->getParentIdsByChild($productid);
            }
            foreach ($parent_product_ids_cat as $ids) {
                if (is_array($ids) && !empty($ids)) {
                    $product_ids_cat[] = $ids[0];
                }
            }
            $product_ids_cat = array_unique($product_ids_cat);
        }
        $mergeProductids = array_unique(array_merge($product_ids_cat, $product_ids));

        return $mergeProductids;
    }
}
