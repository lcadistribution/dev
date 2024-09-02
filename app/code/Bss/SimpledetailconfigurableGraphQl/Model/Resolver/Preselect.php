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
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
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

class Preselect extends BaseResolver implements ResolverInterface
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
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ProductData
     */
    protected $productData;

    /**
     * @var ValueFactory
     */
    protected $valueFactory;

    /**
     * Preselect constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ProductRepositoryInterface $productRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductData $productData
     * @param ValueFactory $valueFactory
     * @param ReviewCollectionFactory $reviewCollectionFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AttributeRepositoryInterface $attributeRepository,
        ProductRepositoryInterface $productRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductData $productData,
        ValueFactory $valueFactory,
        ReviewCollectionFactory $reviewCollectionFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->attributeRepository = $attributeRepository;
        $this->productRepository = $productRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
        $preselect = $args['preselect'] ?? [];

        if (!$storeId) {
            throw new GraphQlInputException(__('Store ID can not be empty.'));
        }

        if (!$sku) {
            throw new GraphQlInputException(__('Product SKU can not be empty.'));
        }

        if (empty($preselect)) {
            throw new GraphQlInputException(__('Preselect Attributes can not be empty.'));
        }

        $childProductData = $this->getPreselect($preselect, $sku, (int)$storeId);

        return $this->valueFactory->create(
            function () use ($childProductData) {
                return $childProductData;
            }
        );
    }

    /**
     * @param array $attributeSelect
     * @param string $sku
     * @param int $storeId
     * @return array
     * @throws GraphQlInputException
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function getPreselect(array $attributeSelect, string $sku, int $storeId): array
    {
        $product = $this->productRepository->get($sku);

        if ($product->getTypeId() !== 'configurable') {
            throw new GraphQlInputException(__('We does not support this type product: %1', $product->getTypeId()));
        }

        $store = $this->storeManager->getStore($storeId);
        $isEnableCustomUrl = $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_advanced/url',
            ScopeInterface::SCOPE_STORE,
            $store->getId()
        );

        if ($isEnableCustomUrl) {
            $attrArr = [];
            foreach ($attributeSelect as $attr) {
                if (!isset($attr['code']) || !isset($attr['value'])) {
                    continue;
                }
                $attrArr[$attr['code']] = $attr['value'];
            }

            $filter = $this->filterBuilder->setField('attribute_code')
                ->setValue(array_keys($attrArr))
                ->setConditionType('in')
                ->create();
            $searchCriteria = $this->searchCriteriaBuilder->addFilters([$filter])->create();

            /** @var AttributeInterface[] $attributes */
            $attributes = $this->attributeRepository->getList(
                Product::ENTITY,
                $searchCriteria
            )->getItems();

            $supperAttributes = [];
            foreach ($attributes as $attribute) {
                if (isset($attrArr[$attribute->getAttributeCode()])) {
                    $options = $attribute->getOptions();

                    /** @var AttributeOptionInterface $option */
                    foreach ($options as $option) {
                        if ($option->getLabel() == $attrArr[$attribute->getAttributeCode()]) {
                            $supperAttributes[$attribute->getAttributeId()] = $option->getValue();
                        }
                    }
                }
            }

            if (!empty($supperAttributes)) {
                /** @var Configurable $productTypeInstance */
                $productTypeInstance = $product->getTypeInstance();
                $child = $productTypeInstance->getProductByAttributes($supperAttributes, $product);
                $childData = $this->productData->getChildDetail($child->getId());
                if (isset($childData['entity']) && $childData['entity']) {
                    $entity = $childData['entity'];
                    return $this->setChildData([$entity => $childData], (int)$store->getId())[0] ?? [];
                }
            }
        }
        return [];
    }
}
