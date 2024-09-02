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

namespace Mageplaza\ConfigureGridView\Block\Product\Renderer;

use Closure;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\ConfigurableProduct\Helper\Data as ConfigurableData;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\Helper\Data as Price;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Swatches\Block\Product\Renderer\Configurable as MagentoConfigurable;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Mageplaza\ConfigureGridView\Helper\Data;
use Mageplaza\ConfigureGridView\Model\Config\Source\Display;

/**
 * Class Configurable
 * @package Mageplaza\ConfigureGridView\Block\Product\Renderer
 */
class Configurable extends MagentoConfigurable
{
    const SWATCH_RENDERER_TEMPLATE = 'Mageplaza_ConfigureGridView::product/view/renderer.phtml';

    /**
     * @var AttributeRepository
     */
    protected $_attributeRepository;

    /**
     * @var Data
     */
    protected $_moduleHelper;

    /**
     * @var Display
     */
    protected $_display;

    /**
     * @var StockStateInterface
     */
    protected $_stockState;

    /**
     * @var Price
     */
    protected $_priceData;

    /**
     * @var ProductFactory
     */
    protected $_productLoader;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Configurable constructor.
     *
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param EncoderInterface $jsonEncoder
     * @param ConfigurableData $helper
     * @param Product $catalogProduct
     * @param CurrentCustomer $currentCustomer
     * @param PriceCurrencyInterface $priceCurrency
     * @param ConfigurableAttributeData $configurableAttributeData
     * @param SwatchData $swatchHelper
     * @param Media $swatchMediaHelper
     * @param AttributeRepository $_attributeRepository
     * @param Data $_moduleHelper
     * @param Display $display
     * @param Price $price
     * @param ProductFactory $_productLoader
     * @param StockStateInterface $stockState
     * @param ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        ConfigurableData $helper,
        Product $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        SwatchData $swatchHelper,
        Media $swatchMediaHelper,
        AttributeRepository $_attributeRepository,
        Data $_moduleHelper,
        Display $display,
        Price $price,
        ProductFactory $_productLoader,
        StockStateInterface $stockState,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->_attributeRepository = $_attributeRepository;
        $this->_moduleHelper        = $_moduleHelper;
        $this->_display             = $display;
        $this->_stockState          = $stockState;
        $this->_priceData           = $price;
        $this->_productLoader       = $_productLoader;
        $this->productRepository    = $productRepository;

        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
            $configurableAttributeData,
            $swatchHelper,
            $swatchMediaHelper,
            $data
        );
    }

    /**
     * @return string
     */
    protected function getRendererTemplate()
    {
        return self::SWATCH_RENDERER_TEMPLATE;
    }

    /**
     * @return array
     */
    public function getDefaultColumns()
    {
        $column = $this->_display->toArray();
        foreach ($column as $key => $value) {
            if (in_array($key, [Display::TIER_PRICE, Display::SPECIAL_PRICE], false)) {
                continue;
            }

            $defaultColumns[$key] = $value;
        }

        /** @var array $defaultColumns */
        return $defaultColumns;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getChildProductData()
    {
        $data                = [];
        $dataActive          = [];
        $dataInActive        = [];
        $dataAll             = [];
        $outStock            = [];
        $currentStore        = $this->getCurrentStore()->getId();
        $currentProduct      = $this->getProduct();
        $customOptions       = $currentProduct ->getOptions();
        $customizableOptions = [];

        foreach ($customOptions as $optionVal) {
            if ($optionVal->getValues()) {
                foreach ($optionVal->getValues() as $value) {
                    $customizableOptions[$optionVal->getOptionId()][$value->getOptionTypeId()] = $value->getPrice();
                }
            } else {
                $customizableOptions[$optionVal->getOptionId()] = $optionVal->getPrice();
            }
        }

        if ($this->_moduleHelper->isShowOutOfStock($currentStore)) {
            $childIds        = $currentProduct->getTypeInstance()->getChildrenIds($currentProduct->getId())[0];
            $childCollection = [];
            /** @var array $childIds */
            foreach ($childIds as $childId) {
                $childCollection[] = $this->_productLoader->create()->load($childId);
            }
        } else {
            $childCollection = $currentProduct->getTypeInstance()->getSalableUsedProducts($currentProduct);
        }

        $keySort        = [];
        $options        = $this->helper->getOptions($currentProduct, $this->getAllowProducts());
        $attributesData = $this->configurableAttributeData->getAttributesData($currentProduct, $options);

        /** @var \Magento\Catalog\Model\Product $child */
        foreach ($childCollection as $child) {
            if (!$child->isSaleable() && !$this->_moduleHelper->getDisplay('out_of_stock')) {
                continue;
            }

            $productId = $child->getId();
            foreach ($this->getAttributeGridView() as $id => $value) {
                $attribute                              = $this->getProductOption(
                    $attributesData['attributes'][$id],
                    $child,
                    $value
                );
                $dataActive[$productId][$value['code']]           = $attribute;
                $dataActive[$productId][$value['code'] . '_text'] = $attribute['options'][0]['label'];
                $keySort[]                                        = $value['code'] . '_text';
                $dataAll[$productId][$value['code']]              = $attribute;
                $dataAll[$productId][$value['code'] . '_text']    = $attribute['options'][0]['label'];
            }
            foreach ($this->getAttributeGridView(false) as $id => $value) {
                $dataAll[$productId][$value['code']] = $this->getProductOption(
                    $attributesData['attributes'][$id],
                    $child,
                    $value
                );
            }

            $currentChildProduct                    = $this->productRepository->getById($child->getId());
            $productStock                           = $currentChildProduct->getExtensionAttributes()->getStockItem();
            $isBackorders                           = $productStock->getBackorders();

            $dataAll[$productId]['sku']             = $child->getSku();
            $dataActive[$productId]['id']           = $child->getId();
            $dataActive[$productId]['stock']        = $this->getProductQty($child);
            $dataActive[$productId]['sku']          = $child->getSku();
            $dataActive[$productId]['price']        = $child->getPrice();
            $dataActive[$productId]['isBackorders'] = $isBackorders;

            $conditionnement = $child->getResource()->getAttribute('conditionnement')->getFrontend()->getValue($child);
            $precommande = $child->getResource()->getAttribute('pre_order_status')->getFrontend()->getValue($child);

            $dataActive[$productId]['conditionnement'] = $conditionnement;
            $dataActive[$productId]['precommande'] = $precommande;
            $dataActive[$productId]['qty']          = '<input type="number" value="0"
            class="mpcpgv-input" product-id="' . $productId . '"
            min="0" max="' . $this->getProductQty($child) . '" >' .
                '<div id="mpcpgv-number"><div class="mpcpgv-inc"></div>'
                . '<div class="mpcpgv-dec"></div></div>';
            $dataActive[$productId]['subtotal'] = '<span id="subtotal-' . $productId . '">' .
                $this->_priceData->currency(0.00, true, false) .
                '</span>';
            if ($this->getProductQty($child) < 1) {
                $outStock[$productId] = [
                    'attribute' => $dataAll[$productId],
                    'value'     => $dataActive[$productId]
                ];
            }
        }


        $data['mpActive']   = $dataActive;
        $data['all']        = $dataAll;
        $data['outProduct'] = $outStock;

        //get InActive Attribute
        foreach ($this->getAttributeGridView(false) as $attributeId => $attribute) {
            $dataInActive[$attributeId] = $attributesData['attributes'][$attributeId];
        }

        $data['mpInActive'] = $dataInActive;

        $columnAll    = array_keys($this->_display->toArray());
        $columnMobile = explode(',', $this->_moduleHelper->getMobileColumns($currentStore) ?? '');
        $columnTablet = explode(',', $this->_moduleHelper->getTabletColumns($currentStore) ?? '');
        $columnPC     = explode(',', $this->_moduleHelper->getDefaultColumns($currentStore) ?? '');

        $data['config'] = [
            'storeId'             => $currentStore,
            'url'                 => $this->getUrl('mpcpgv/cart/addtocart'),
            'isShowSummary'       => $this->_moduleHelper->isShowDetail($currentStore),
            'columnMobile'        => array_diff($columnAll, $columnMobile),
            'columnTablet'        => array_diff($columnAll, $columnTablet),
            'columnPC'            => array_diff($columnAll, $columnPC),
            'customizableOptions' => $customizableOptions,
            'enableSort'          => $this->_moduleHelper->getSortEnabled($currentStore)
        ];

        return $data;
    }

    /**
     * @param $fields
     *
     * @return Closure
     */
    public function sortByAttribute($fields)
    {
        return function ($a, $b) use (&$fields) {
            foreach ($fields as $field) {
                $diff = strcmp($a[$field], $b[$field]);
                if ($diff !== 0) {
                    return $diff;
                }
            }

            return false;
        };
    }

    /**
     * @param bool $active
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAttributeGridView($active = true)
    {
        $attribute      = $this->getProductAttribute();
        $attributeArray = [];
        /** @var array $attribute */
        foreach ($attribute as $id => $value) {
            if ($this->checkActiveAttribute($value['code'], $active)) {
                $attributeArray[$id] = [
                    'code'  => $value['code'],
                    'label' => $value['label']
                ];
            }
        }

        return $attributeArray;
    }

    /**
     * @return mixed
     */
    public function getProductAttribute()
    {
        $currentProduct = $this->getProduct();
        $options        = $this->helper->getOptions($currentProduct, $this->getAllowProducts());
        $attributesData = $this->configurableAttributeData->getAttributesData($currentProduct, $options);

        return $attributesData['attributes'];
    }

    /**
     * @param $attributeCode
     * @param bool $active
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function checkActiveAttribute($attributeCode, $active)
    {
        $selectOptions = $this->_attributeRepository->get($attributeCode);
        if ($active) {
            return $selectOptions->getData('mp_grid_view') ? true : false;
        }

        return !$selectOptions->getData('mp_grid_view') ? true : false;
    }

    /**
     * @param $attributeData
     * @param ProductFactory $child
     * @param $value
     *
     * @return mixed
     */
    protected function getProductOption($attributeData, $child, $value)
    {
        $attribute = $attributeData;
        $options   = $attributeData['options'];
        if (isset($options)) {
            /** @var array $options */
            foreach ($options as $option) {
                if ($child->getData($value['code']) === $option['id']) {
                    $attribute['options'] = [$option];
                }
            }
        }

        return $attribute;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return float
     */
    public function getProductQty($product)
    {
        return $this->_stockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
    }

    /**
     * @return bool
     */
    public function isSubtotal()
    {
        $defaultColumns = explode(',', $this->_moduleHelper->getDefaultColumns($this->getCurrentStore()->getId()) ?? '');

        return in_array(Display::SUBTOTAL, $defaultColumns, true);
    }

    /**
     * @return bool
     */
    public function isEnable()
    {
        $storeId = $this->getCurrentStore()->getId();
        $product = $this->getProduct();

        return $this->_moduleHelper->checkEnableModule($product, $storeId);
    }

    /**
     * @return mixed
     */
    public function sortEnabled()
    {
        $storeId = $this->getCurrentStore()->getId();

        return $this->_moduleHelper->getSortEnabled($storeId);
    }
}
