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

use Bss\Simpledetailconfigurable\Helper\ProductData;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;

class PreselectByConfig extends BaseResolver implements ResolverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductData
     */
    protected $productData;

    /**
     * @var ValueFactory
     */
    protected $valueFactory;

    /**
     * PreselectByConfig constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param ProductData $productData
     * @param ValueFactory $valueFactory
     * @param ReviewCollectionFactory $reviewCollectionFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        ProductData $productData,
        ValueFactory $valueFactory,
        ReviewCollectionFactory $reviewCollectionFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->productData = $productData;
        $this->valueFactory = $valueFactory;
        parent::__construct($reviewCollectionFactory);
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value
     * @throws GraphQlInputException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        $storeId = $args['store_id'] ?? null;
        $sku = $args['sku'] ?? '';

        if (!$storeId) {
            throw new GraphQlInputException(__('Store ID can not be empty.'));
        }

        if (!$sku) {
            throw new GraphQlInputException(__('Product SKU can not be empty.'));
        }

        $childProductData = $this->getPreselectConfig($sku, (int)$storeId);

        return $this->valueFactory->create(
            function () use ($childProductData) {
                return $childProductData;
            }
        );
    }

    /**
     * @param string $sku
     * @param int $storeId
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function getPreselectConfig(string $sku, int $storeId): array
    {
        $product = $this->productRepository->get($sku);
        $store = $this->storeManager->getStore($storeId);
        $preselectData = $this->productData->getSelectingData($product->getId());
        $isEnablePreselect = $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_advanced/preselect',
            ScopeInterface::SCOPE_STORE,
            $store->getId()
        );

        if ($product->getTypeId() !== 'configurable') {
            throw new InputException(__('We does not support this type product: %1', $product->getTypeId()));
        }

        if ((bool)$isEnablePreselect && !empty($preselectData)) {
            /** @var Configurable $productTypeInstance */
            $productTypeInstance = $product->getTypeInstance();
            $child = $productTypeInstance->getProductByAttributes($preselectData, $product);
            $childData = $this->productData->getChildDetail($child->getId());
            if (isset($childData['entity']) && $childData['entity']) {
                $entity = $childData['entity'];
                return $this->setChildData([$entity => $childData], (int)$store->getId())[0] ?? [];
            }
        }
        return [];
    }
}
