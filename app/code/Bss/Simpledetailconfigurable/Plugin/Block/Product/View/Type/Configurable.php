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

namespace Bss\Simpledetailconfigurable\Plugin\Block\Product\View\Type;

use Bss\Simpledetailconfigurable\Helper\ModuleConfig;
use Bss\Simpledetailconfigurable\Helper\ProductData;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;

class Configurable
{
    /**
     * @var Json
     */
    protected $serialize;

    /**
     * @var ModuleConfig
     */
    protected $helper;

    /**
     * @var ProductData
     */
    protected $productData;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Configurable constructor.
     * @param Json $serialize
     * @param ModuleConfig $helper
     * @param ProductData $productData
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        Json $serialize,
        ModuleConfig $helper,
        ProductData $productData,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->serialize = $serialize;
        $this->helper = $helper;
        $this->productData = $productData;
        $this->_urlBuilder = $urlBuilder;
        $this->request = $request;
    }

    /**
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param string $result
     * @return string
     * @throws NoSuchEntityException
     */
    public function afterGetJsonConfig($subject, $result)
    {
        if ($this->helper->isModuleEnable()) {
            $product = $subject->getProduct();
            $config = $this->serialize->unserialize($result);
            $config["url_suffix"] = $this->helper->getSuffix();
            $config["ajax_option_url"] = $this->getCustomOptionAjaxUrl();
            $config["action_name"] = $this->request->getFullActionName();
            if ($this->helper->isShowName()) {
                $config["is_enable_swatch_name"] = $this->helper->isShowName();
                $config["bss_simple_detail"] = $this->productData->getAllProductName($product->getEntityId());
            }
            if ($this->helper->isShowAdditionalInfo()) {
                $config["is_enable_rating_reviews"] = $this->helper->isShowAdditionalInfo();
                if (!isset($config["bss_simple_detail"])) {
                    $config["bss_simple_detail"] = $this->productData->getAllProductName($product->getEntityId());
                }
            }

            if (!empty($config['canDisplayShowOutOfStockStatus'])) {
                $config = $this->setProductOutStock($config);
            }

            return $this->serialize->serialize($config);
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getCustomOptionAjaxUrl()
    {
        return $this->_urlBuilder->getUrl('bss_sdcp/ajax/option');
    }

    /**
     * Remove product out of stock in catalog page
     *
     * @param array $config
     * @return array
     */
    public function setProductOutStock($config)
    {
        $salableProductIds = $config['salable'] ?? [];

        foreach ($salableProductIds as $attributeId => $options) {
            if (!isset($config['attributes'][$attributeId]['options'])) {
                continue;
            }

            $optionsData = &$config['attributes'][$attributeId]['options'];
            foreach ($optionsData as &$option) {
                $option['products'] = $options[$option['id']] ?? [];
            }
        }

        return $config;
    }
}
