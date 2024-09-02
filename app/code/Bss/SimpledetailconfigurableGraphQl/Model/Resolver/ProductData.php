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

use Bss\Simpledetailconfigurable\Helper\ProductData as ProductDataHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;

class ProductData extends BaseResolver implements ResolverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductDataHelper
     */
    protected $productDataHelper;

    /**
     * @var ValueFactory
     */
    protected $valueFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ProductData constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param ProductDataHelper $productData
     * @param ValueFactory $valueFactory
     * @param StoreManagerInterface $storeManager
     * @param ReviewCollectionFactory $reviewCollectionFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductDataHelper $productData,
        ValueFactory $valueFactory,
        StoreManagerInterface $storeManager,
        ReviewCollectionFactory $reviewCollectionFactory
    ) {
        $this->productRepository = $productRepository;
        $this->productDataHelper = $productData;
        $this->valueFactory = $valueFactory;
        $this->storeManager = $storeManager;
        parent::__construct($reviewCollectionFactory);
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value|mixed
     * @throws GraphQlInputException
     * @throws NoSuchEntityException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        $sku = $args['sku'] ?? '';
        if (!$sku) {
            throw new GraphQlInputException(__('Product SKU cannot be empty.'));
        }

        $productData = $this->getProductData($sku);
        return $this->valueFactory->create(
            function () use ($productData) {
                return $productData;
            }
        );
    }

    /**
     * @param string $sku
     * @param int $storeId
     * @return array
     * @throws GraphQlInputException
     * @throws NoSuchEntityException
     */
    public function getProductData(string $sku, $storeId = 0): array
    {
        $product = $this->productRepository->get($sku);
        $store = $this->storeManager->getStore($storeId);

        if ($product->getTypeId() !== 'configurable') {
            throw new GraphQlInputException(__('We does not support this product type: %1', $product->getTypeId()));
        }
        $rawProductData = $this->productDataHelper->getAllData($product->getId());

        $productData = [];
        $productData['sku'] = $product->getSku();
        $productData['name'] = $product->getName();
        $productData['url'] = $rawProductData['url'] ?? '';
        $productData['price'] = 0;
        $productData['desc'] = $rawProductData['desc'] ?? '';
        $productData['stock_data'] = [
            'is_in_stock' => (bool)$rawProductData['stock_status'] ?? false,
            'salable_qty' => (float)$rawProductData['stock_number'] ?? 0,
        ];

        $items = $rawProductData['child'] ?? [];
        $additionalInfo = $rawProductData['additional_info'] ?? [];
        $preselect = $rawProductData['preselect'] ?? [];
        $images = $rawProductData['image'] ?? [];
        $metaData = $rawProductData['meta_data'] ?? [];

        $productData['meta_data'] = [
            'meta_description' => $metaData['meta_description'] ?? '',
            'meta_keyword' => $metaData['meta_keyword'] ?? '',
            'meta_title' => $metaData['meta_title'] ?? '',
        ];

        $productData['preselect'] = $this->setPreselect($preselect);
        $productData['images'] = $this->setImages($images);
        $productData['items'] = $this->setChildData($items, (int)$store->getId());
        $productData['additional_info'] = $this->setAdditionalInfo($additionalInfo);

        $generalConfigData = $this->productDataHelper->getEnabledModuleOnProduct($product->getId())->getData();
        $productData['enable_module_on_product'] = $generalConfigData['enabled'] ?? true;
        $productData['enable_ajax_on_product'] = $generalConfigData['is_ajax_load'] ?? true;

        return $productData;
    }
}
