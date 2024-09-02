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
 * @package    Bss_SimpledetailconfigurableGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\SimpledetailconfigurableGraphQl\Model\Resolver;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Configuration implements ResolverInterface
{
    /**
     * Const
     */
    const ENABLED = 'is_enabled';
    const DISPLAY_SKU = 'display_sku';
    const DISPLAY_NAME = 'display_name';
    const DISPLAY_DESC = 'display_desc';
    const DISPLAY_TIER = 'display_tier_price';
    const DISPLAY_STOCK = 'display_stock';
    const DISPLAY_IMAGES = 'display_images';
    const DISPLAY_CHILD_IMAGE = 'display_child_image';
    const DISPLAY_ADDITIONAL_INFO = 'display_additional_info';
    const DISPLAY_META_DATA = 'display_meta_data';
    const DISPLAY_CHILD_OPTIONS = 'display_child_options';
    const ENABLE_CUSTOM_URL = 'enable_custom_url';
    const ENABLE_PRESELECT = 'enable_preselect';
    const ENABLE_SITEMAP = 'enable_url_sitemap';

    const MAP = [
        'is_enabled' => 'Bss_Commerce/Simpledetailconfigurable/Enable',
        'display_sku' => 'Bss_Commerce/SDCP_details/sku',
        'display_name' => 'Bss_Commerce/SDCP_details/name',
        'display_desc' => 'Bss_Commerce/SDCP_details/desc',
        'display_tier_price' => 'Bss_Commerce/SDCP_details/tier_price',
        'display_stock' => 'Bss_Commerce/SDCP_details/stock',
        'display_images' => 'Bss_Commerce/SDCP_details/image',
        'display_child_image' => 'Bss_Commerce/SDCP_details/child_image',
        'display_additional_info' => 'Bss_Commerce/SDCP_details/additional_info',
        'display_meta_data' => 'Bss_Commerce/SDCP_details/meta_data',
        'display_child_options' => 'Bss_Commerce/SDCP_details/child_options',
        'enable_custom_url' => 'Bss_Commerce/SDCP_advanced/url',
        'enable_preselect' => 'Bss_Commerce/SDCP_advanced/preselect',
        'enable_url_sitemap' => 'Bss_Commerce/SDCP_advanced/url_sitemap',
    ];

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ValueFactory
     */
    protected $valueFactory;

    /**
     * Configuration constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ValueFactory $valueFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->valueFactory = $valueFactory;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value|mixed
     * @throws GraphQlInputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        $mapConfig = self::MAP;
        $storeId = $args['store_id'] ?? null;
        if ($storeId === null) {
            throw new GraphQlInputException(__('Store ID cannot be empty.'));
        }
        $store = $this->storeManager->getStore($storeId);
        $returnData = [];
        foreach ($mapConfig as $idxKey => $path) {
            $returnData[$idxKey] = $this->scopeConfig->getValue(
                $path,
                ScopeInterface::SCOPE_STORE,
                $store->getId()
            );
        }
        return $this->valueFactory->create(
            function () use ($returnData) {
                return $returnData;
            }
        );
    }
}
