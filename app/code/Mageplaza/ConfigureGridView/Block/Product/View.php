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

namespace Mageplaza\ConfigureGridView\Block\Product;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View as CatalogView;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\EncoderInterface as JsonEncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface;
use Mageplaza\ConfigureGridView\Helper\Data;

/**
 * Class Configurable
 * @package Mageplaza\ConfigureGridView\Block\Product\Renderer
 */
class View extends CatalogView
{
    /**
     * @var Data
     */
    protected $_moduleHelper;

    /**
     * View constructor.
     *
     * @param Data $_moduleHelper
     * @param Context $context
     * @param EncoderInterface $urlEncoder
     * @param JsonEncoderInterface $jsonEncoder
     * @param StringUtils $string
     * @param Product $productHelper
     * @param ConfigInterface $productTypeConfig
     * @param FormatInterface $localeFormat
     * @param Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Data $_moduleHelper,
        Context $context,
        EncoderInterface $urlEncoder,
        JsonEncoderInterface $jsonEncoder,
        StringUtils $string,
        Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->_moduleHelper = $_moduleHelper;

        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
    }

    /**
     * @return bool
     */
    public function isEnable()
    {
        try {
            $storeId = $this->_storeManager->getStore()->getId();
        } catch (Exception $e) {
            $storeId = null;
        }
        $product = $this->getProduct();

        return $this->_moduleHelper->checkEnableModule($product, $storeId);
    }
}
