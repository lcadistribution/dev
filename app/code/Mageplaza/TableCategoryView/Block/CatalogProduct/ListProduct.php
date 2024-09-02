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

use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle;
use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Checkbox;
use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Multi;
use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Radio;
use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Select as BundleSelect;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ListProduct as CatalogListProduct;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Block\Product\View\Options;
use Magento\Catalog\Block\Product\View\Options\Type\Date;
use Magento\Catalog\Block\Product\View\Options\Type\DefaultType;
use Magento\Catalog\Block\Product\View\Options\Type\File;
use Magento\Catalog\Block\Product\View\Options\Type\Select as ProductSelect;
use Magento\Catalog\Block\Product\View\Options\Type\Text;
use Magento\Catalog\Helper\Output;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Downloadable\Block\Catalog\Product\Links;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\GroupedProduct\Block\Product\View\Type\Grouped;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Observer\PredispatchReviewObserver;
use Magento\Store\Model\ScopeInterface;
use Mageplaza\TableCategoryView\Block\CatalogProduct\View\Giftcard;
use Mageplaza\TableCategoryView\Helper\Data as MpData;
use Magento\CatalogInventory\Api\StockStateInterface;

/**
 * Class ListProduct
 * @package Mageplaza\TableCategoryView\Block\CatalogProduct
 */
class ListProduct extends CatalogListProduct
{

    /**
     * @var StockStateInterface
     */
    protected $_stockStateInterface;


    /**
     * @var StockItemRepository
     */
    protected $_stockItemRepository;

    /**
     * @var array|MpData
     */
    protected $helperData;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ThemeProviderInterface
     */
    private $themeProvider;

    /**
     * @var Output
     */
    protected $_helperOutput;

    /**
     * @var ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * ListProduct constructor.
     *
     * @param Context $context
     * @param PostHelper $postDataHelper
     * @param Resolver $layerResolver
     * @param StockItemRepository $stockItemRepository
     * @param StockStateInterface $stockStateInterface
     * @param CategoryRepositoryInterface $categoryRepository
     * @param MpData $helperData
     * @param Data $urlHelper
     * @param ThemeProviderInterface $themeProvider
     * @param ProductRepositoryInterface $productRepository
     * @param ReviewFactory $reviewFactory
     * @param Output $output
     * @param array $data
     */
    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        StockItemRepository $stockItemRepository,
        StockStateInterface $stockStateInterface,
        CategoryRepositoryInterface $categoryRepository,
        MpData $helperData,
        Data $urlHelper,
        ThemeProviderInterface $themeProvider,
        ProductRepositoryInterface $productRepository,
        ReviewFactory $reviewFactory,
        Output $output,
        array $data = []
    ) {
        $this->helperData           = $helperData;
        $this->_stockItemRepository = $stockItemRepository;
        $this->_stockStateInterface = $stockStateInterface;
        $this->productRepository    = $productRepository;
        $this->themeProvider        = $themeProvider;
        $this->_helperOutput        = $output;
        $this->_reviewFactory       = $reviewFactory;
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    /**
     * @return Output
     */
    public function getOutputHelper()
    {
        return $this->_helperOutput;
    }

    /**
     * @return bool
     */
    public function checkTheme()
    {
        return $this->themeProvider->getThemeById($this->helperData->getCurrentThemeId())
                ->getCode() === 'Smartwave/porto';
    }

    /**
     * @param Product $product
     *
     * @return string
     * @throws LocalizedException
     */
    public function getGiftCardHtml($product)
    {
        if ($product->getTypeId() === 'giftcard') {
            /** @var Links $linksBlock */
            $giftBlock = $this->getLayout()->createBlock(Giftcard::class);
            $giftBlock->setTemplate('Mageplaza_TableCategoryView::product/view/giftcard.phtml');

            return $giftBlock->toHtml();
        }

        return '';
    }

    /**
     * @param Product $product
     *
     * @return string
     * @throws LocalizedException
     */
    public function getBundleHtml($product)
    {
        if ($product->getTypeId() === 'bundle') {
            /** @var Bundle $groupBlock */
            $groupBlock = $this->getLayout()->createBlock(Bundle::class);
            $groupBlock->setTemplate('Magento_Bundle::catalog/product/view/type/bundle/options.phtml');

            $groupBlock->addChild(
                'select',
                BundleSelect::class
            );

            $groupBlock->addChild(
                'multi',
                Multi::class
            );
            $groupBlock->addChild(
                'radio',
                Radio::class
            );
            $groupBlock->addChild(
                'checkbox',
                Checkbox::class
            );

            return $groupBlock->toHtml();
        }

        return '';
    }

    /**
     * @param Product $product
     *
     * @return string
     * @throws LocalizedException
     */
    public function getLinksHtml($product)
    {
        if ($product->getTypeId() === 'downloadable') {
            /** @var Links $linksBlock */
            $linksBlock = $this->getLayout()->createBlock(Links::class);
            $linksBlock->setTemplate('Magento_Downloadable::catalog/product/links.phtml');

            return $linksBlock->toHtml();
        }

        return '';
    }

    /**
     * @param Product $product
     *
     * @return string
     * @throws LocalizedException
     */
    public function getGroupProductHtml($product)
    {
        if ($product->getTypeId() === 'grouped') {
            /** @var Links $linksBlock */
            $groupBlock = $this->getLayout()->createBlock(Grouped::class);
            $groupBlock->setTemplate('Magento_GroupedProduct::product/view/type/grouped.phtml');

            return $groupBlock->toHtml();
        }

        return '';
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getOptionsHtml()
    {
        /** @var View $attributeBlog */
        $attributeBlog = $this->getLayout()->createBlock(View::class);
        $attributeBlog->setTemplate('Magento_Catalog::product/view/options/wrapper.phtml');
        $attributeBlog->addChild(
            'product_options',
            Options::class,
            ['template' => 'Magento_Catalog::product/view/options.phtml']
        );
        $attributeBlog->getChildBlock('product_options')->addChild(
            'default',
            DefaultType::class,
            ['template' => 'Magento_Catalog::product/view/options/type/default.phtml']
        );
        $attributeBlog->getChildBlock('product_options')->addChild(
            'text',
            Text::class,
            ['template' => 'Magento_Catalog::product/view/options/type/text.phtml']
        );
        $attributeBlog->getChildBlock('product_options')->addChild(
            'file',
            File::class,
            ['template' => 'Magento_Catalog::product/view/options/type/file.phtml']
        );
        $attributeBlog->getChildBlock('product_options')->addChild(
            'select',
            ProductSelect::class,
            ['template' => 'Magento_Catalog::product/view/options/type/select.phtml']
        );
        $attributeBlog->getChildBlock('product_options')->addChild(
            'date',
            Date::class,
            ['template' => 'Magento_Catalog::product/view/options/type/date.phtml']
        );

        return $attributeBlog->toHtml();
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getConfigurableOptionsHtml($product)
    {

        if($product->getTypeId() == "configurable"){


            $data = $product->getTypeInstance()->getConfigurableOptions($product);

            $options = array();
            $attributes = [];
            $property_types = array();
            $html = '';
            foreach ($data as $attributes) {
                foreach ($attributes as $attribute) {

                    if($attribute['attribute_code'] != 'conditionnement'){

                        if (in_array($attribute['option_title'], $property_types)) {
                            continue;
                        }
                        $property_types[] = $attribute['option_title'];

                        $html .= '<div class="badge badge-secondary ' . $attribute['attribute_code'] . '">' . $attribute['option_title'] . '</div>';

                    }
                }
            }

            return $html;
        }




    }





    /**
     * @param Product $product
     *
     * @return float
     */
    public function getProductStockQty($product)
    {
        if ($product->getTypeId() == 'configurable') {
            try {

                $productTypeInstance = $product->getTypeInstance();
                $usedProducts = $productTypeInstance->getUsedProducts($product);
                $total_stock = 0;
                foreach ($usedProducts as $simple) {
                    $total_stock += $this->_stockStateInterface->getStockQty($simple->getId(), $simple->getStore()->getWebsiteId());
                }
                return $total_stock;

            } catch (NoSuchEntityException $exception) {
                $this->_logger->critical($exception);

                return null;
            }
        } else if ($product->getTypeId() == 'bundle') {

            try {

                $total_stock = 0;
                $typeInstance = $_product->getTypeInstance();
                $requiredChildrenIds = $typeInstance->getChildrenIds($product->getId(), false);

                foreach ($requiredChildrenIds as $Childrenkey => $Childrenvalue) {
                    foreach ($Childrenvalue as $key => $value) {
                        $total_stock += $this->_stockStateInterface->getStockQty($value);
                    }
                }

                return $total_stock;

            } catch (NoSuchEntityException $exception) {
                $this->_logger->critical($exception);

                return null;
            }

        } else {

           return $this->_stockStateInterface->getStockQty($product->getId());

        }
    }

    /**
     * @param $code
     *
     * @return mixed
     */
    public function checkEnable($code)
    {
        switch ($code) {
            case 'image':
                return $this->helperData->isImage();
            case 'description':
                return $this->helperData->isShortDesc();
            case 'review':
                return $this->helperData->isReview();
            case 'stock':
                return $this->helperData->isStock();
            case 'mpcpgv':
                return $this->helperData->isMpcpgv();
            case 'add_button':
                return $this->helperData->getAddButtonMode();
        }

        return '';
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function isQty($product)
    {
        $productType = $product->getTypeId();

        return $productType !== 'downloadable' && $productType !== 'grouped';
    }

    /**
     * @param $productId
     *
     * @return bool
     */
    public function setMpProduct($productId)
    {
        try {
            $product = $this->productRepository->getById(
                $productId,
                false,
                $this->_storeManager->getStore()->getId()
            );
        } catch (NoSuchEntityException $exception) {
            $this->_logger->critical($exception);

            return false;
        }

        if (in_array($product->getTypeId(), ['grouped', 'bundle', 'downloadable', 'giftcard'], true)
            || count($product->getOptions()) > 0) {
            if ($this->_coreRegistry->registry('product')) {
                $this->resetProduct();
            }
            $this->_coreRegistry->register('product', $product);
            $this->_coreRegistry->register('current_product', $product);

            return true;
        }

        return false;
    }

    /**
     * reset Register
     */
    public function resetProduct()
    {
        $this->_coreRegistry->unregister('current_product');
        $this->_coreRegistry->unregister('product');
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->helperData->getStoreId();
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function isPopup($product)
    {
        $productType = $product->getTypeId();
        $popupArray  = $this->helperData->getPopupOption();

        return empty($popupArray) || in_array($productType, $popupArray, true);
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function checkPopup($product)
    {
        $productType = $product->getTypeId() ?: '';

        try {
            $productRep = $this->productRepository->getById(
                $product->getId(),
                false,
                $this->_storeManager->getStore()->getId()
            );
        } catch (NoSuchEntityException $exception) {
            $this->_logger->critical($exception);

            return false;
        }

        if (count($productRep->getOptions()) > 0) {
            return true;
        }

        if ($productType === 'downloadable' && !$product->getLinksPurchasedSeparately()) {
            return false;
        }

        return in_array($productType, ['downloadable', 'bundle', 'grouped', 'giftcard', 'configurable'], true)
            || $this->getProductDetailsHtml($product) !== '';
    }

    /**
     * @param $productId
     *
     * @return bool|ProductCustomOptionInterface[]
     */
    public function getOptions($productId)
    {
        return MpData::jsonEncode($this->helperData->getOptionsProduct($productId));
    }

    /**
     * @param $type
     *
     * @return mixed
     */
    public function getButtonInf($type)
    {
        switch ($type) {
            case 'text':
                return $this->helperData->getAddButtonText();
            case 'color':
                return $this->helperData->getAddButtonColor();
            case 'background':
                return $this->helperData->getAddButtonBackground();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getAllProductOptions()
    {
        $options            = [];
        $_productCollection = $this->getLoadedProductCollection();

        /** @var Product $_product */
        foreach ($_productCollection as $_product) {
            $productId = $_product->getId();

            $options[$productId] = $this->helperData->getOptionsProduct($productId);
        }

        return MpData::jsonEncode($options);
    }

    /**
     * @param Product $product
     *
     * @return mixed
     */
    public function getProductDetailsHtml(Product $product)
    {
        if ($this->helperData->isEnabled() && $this->getToolbarBlock()->getCurrentMode() === 'table') {
            $this->_layout->getBlock('category.product.type.details.renderers.configurable')
                ->setTemplate('Mageplaza_TableCategoryView::product/listing/renderer.phtml');
        }

        return parent::getProductDetailsHtml($product); // TODO: Change the autogenerated stub
    }

    /**
     * @param Product $product
     * @param bool $templateType
     * @param bool $displayIfNoReviews
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getReviewsSummaryHtml(
        Product $product,
        $templateType = false,
        $displayIfNoReviews = false
    ) {
        if (!$product->getRatingSummary()) {
            $this->_reviewFactory->create()->getEntitySummary($product, $this->_storeManager->getStore()->getId());
        }

        if (!$product->getRatingSummary() && !$displayIfNoReviews) {
            return '';
        }

        $this->setTemplate('Mageplaza_TableCategoryView::helper/summary_short.phtml');

        $this->setDisplayIfEmpty($displayIfNoReviews);

        $this->setProduct($product);

        return $this->toHtml();
    }

    /**
     * Get review product list url
     *
     * @param bool $useDirectLink allows to use direct link for product reviews page
     *
     * @return string
     */
    public function getReviewsUrl($useDirectLink = false)
    {
        $product = $this->getProduct();
        if ($useDirectLink) {
            return $this->getUrl(
                'review/product/list',
                ['id' => $product->getId(), 'category' => $product->getCategoryId()]
            );
        }

        return $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]);
    }

    /**
     * Review module availability
     *
     * @return string
     */
    public function isReviewEnabled(): string
    {
        return $this->_scopeConfig->getValue(
            PredispatchReviewObserver::XML_PATH_REVIEW_ACTIVE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get count of reviews
     *
     * @return int
     */
    public function getReviewsCount()
    {
        if ($this->helperData->versionCompare('2.3.5')) {
            return $this->getProduct()->getReviewsCount();
        }

        return $this->getProduct()->getRatingSummary()->getReviewsCount();
    }

    /**
     * Get ratings summary
     *
     * @return string
     */
    public function getRatingSummary()
    {
        if ($this->helperData->versionCompare('2.3.5')) {
            return $this->getProduct()->getRatingSummary();
        }

        return $this->getProduct()->getRatingSummary()->getRatingSummary();
    }

    /**
     * @return string
     */
    public function getReviewText()
    {
        return $this->helperData->getReviewText();
    }

    /**
     * Check enable module
     *
     * @param string $moduleName
     *
     * @return bool
     */
    public function checkModuleIsEnable($moduleName)
    {
        return $this->helperData->moduleIsEnable($moduleName);
    }

    /**
     * @return array|MpData
     */
    public function getHelperData()
    {
        return $this->helperData;
    }

    /**
     * @return mixed
     */
    public function getCfpHelperRule()
    {
        return $this->helperData->createObject(\Mageplaza\CallForPrice\Helper\Rule::class);
    }

    /**
     * @return mixed
     */
    public function getRfqHelper()
    {
        return $this->helperData->createObject(\Mageplaza\RequestForQuote\Helper\Data::class);
    }

    /**
     * @return bool
     */
    public function isLogin()
    {
        return $this->helperData->isLogin();
    }
}
