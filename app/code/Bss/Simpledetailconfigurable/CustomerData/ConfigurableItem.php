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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Simpledetailconfigurable\CustomerData;

use Bss\Simpledetailconfigurable\Helper\ModuleConfig;
use Bss\Simpledetailconfigurable\Helper\ProductData;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Catalog\Model\Config\Source\Product\Thumbnail as ThumbnailSource;
use Magento\Catalog\Model\Product;
use Magento\Checkout\CustomerData\DefaultItem;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Msrp\Helper\Data;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;

class ConfigurableItem extends DefaultItem
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    protected $helper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var ProductData
     */
    protected $productData;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * ConfigurableItem constructor.
     * @param Image $imageHelper
     * @param Data $msrpHelper
     * @param UrlInterface $urlBuilder
     * @param ConfigurationPool $configurationPool
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param ModuleConfig $helper
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ProductData $productData
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Catalog\Helper\Image                      $imageHelper,
        \Magento\Msrp\Helper\Data                          $msrpHelper,
        \Magento\Framework\UrlInterface                    $urlBuilder,
        \Magento\Catalog\Helper\Product\ConfigurationPool  $configurationPool,
        \Magento\Checkout\Helper\Data                      $checkoutHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig  $helper,
        \Magento\Framework\App\RequestInterface            $request,
        \Magento\Store\Model\StoreManagerInterface         $storeManager,
        \Magento\Framework\Api\FilterBuilder               $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder       $searchCriteriaBuilder,
        \Magento\Eav\Api\AttributeRepositoryInterface      $attributeRepository,
        \Bss\Simpledetailconfigurable\Helper\ProductData   $productData,
        \Magento\Catalog\Api\ProductRepositoryInterface    $productRepository
    ) {
        parent::__construct(
            $imageHelper,
            $msrpHelper,
            $urlBuilder,
            $configurationPool,
            $checkoutHelper
        );
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->productData = $productData;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Item $item
     * @return array
     * @throws NoSuchEntityException
     */
    public function getItemData(Item $item)
    {
        $result = parent::getItemData($item);
        if ($this->helper->isModuleEnable() && $this->helper->isShowName() && $child = $this->getChildProduct()) {
            $result['product_name'] = $child->getName();
        }
        return $result;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProductForThumbnail()
    {
        if (version_compare($this->helper->getMagentoVersion(), '2.3.0', '<')) {
            $config = $this->scopeConfig->getValue(
                \Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable::CONFIG_THUMBNAIL_SOURCE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $thumbnail = $this->getChildProduct()->getThumbnail();
            if ($config == ThumbnailSource::OPTION_USE_PARENT_IMAGE || (!$thumbnail || $thumbnail == 'no_selection')) {
                return $this->getProduct();
            }
            return $this->getChildProduct();
        } else {
            return parent::getProductForThumbnail();
        }
    }

    /**
     * Get item configurable child product
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function getChildProduct()
    {
        if ($option = $this->item->getOptionByCode('simple_product')) {
            return $option->getProduct();
        }
        return $this->getProduct();
    }

    /**
     * Get child product by preselect
     *
     * @return ProductInterface|Product|null
     * @throws NoSuchEntityException
     */
    public function getChildByPreselect()
    {
        $action = $this->request->getFullActionName();
        $productId = $this->request->getParam('id');

        if ($action === 'catalog_product_view' && $this->helper->isModuleEnable()) {
            $urlWithPreselect = $this->urlBuilder->getCurrentUrl();
            if ($this->helper->getSuffix() != null) {
                $urlWithPreselect = substr($urlWithPreselect, 0, strpos($urlWithPreselect, $this->helper->getSuffix()));
            }
            $preselect = explode('+', $urlWithPreselect);
            array_shift($preselect);
            $attributesPreselect = [];
            if ($preselect) {
                foreach ($preselect as $preselectStr) {
                    $attributePairs = explode('-', $preselectStr);
                    if (is_array($attributePairs)) {
                        $code = $attributePairs[0];
                        $value = substr($preselectStr, strlen($code) + 1);
                        $value = preg_replace('/~/i', '', $value);
                        if ($value) {
                            $attributesPreselect[$code] = $value;
                        }
                    }
                }
                $preselect = [];
                $filter = $this->filterBuilder->setField('attribute_code')
                    ->setValue(array_keys($attributesPreselect))
                    ->setConditionType('in')
                    ->create();
                $searchCriteria = $this->searchCriteriaBuilder->addFilters([$filter])->create();

                $attributes = $this->attributeRepository->getList(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $searchCriteria
                )->getItems();

                foreach ($attributes as $attribute) {
                    if (isset($attributesPreselect[$attribute->getAttributeCode()])) {
                        $options = $attribute->getOptions();

                        foreach ($options as $option) {
                            if ($option->getLabel() == $attributesPreselect[$attribute->getAttributeCode()]) {
                                $preselect[$attribute->getAttributeId()] = $option->getValue();
                            }
                        }
                    }
                }
            } else {
                $preselect = $this->productData->getSelectingDataWithConfig($productId)['data'];
            }

            if (!empty($preselect)) {
                $product = $this->productRepository->getById($productId);
                $productTypeInstance = $product->getTypeInstance();
                $child = $productTypeInstance->getProductByAttributes($preselect, $product);
                if ($child && ($child instanceof \Magento\Catalog\Model\Product || $child instanceof \Magento\Catalog\Api\Data\ProductInterface) && $child->getSku()) {
                    return $child;
                }
            }
        }
        return null;
    }
}
