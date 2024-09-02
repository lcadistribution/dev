<?php

namespace LCA\ProductRulesPrice\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\Store\Model\StoreManager;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\CatalogRule\Model\ResourceModel\Rule as CatalogRule;
use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroupCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;

class Prices extends \Magento\Backend\Block\Template
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var PricingHelper
     */
    protected $princingHelper;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var CatalogRule
     */
    protected $catalogRule;

    /**
     * @var CatalogRuleRepositoryInterface
     */
    protected $catalogRuleRepository;

    /**
     * @var CustomerGroupCollection
     */
    protected $customerGroup;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;

    /**
     * @var string
     */
    protected $_template = 'prices.phtml';

    /**
     * @inheritDoc
     *
     * @param Context $context
     * @param PricingHelper $pricingHelper
     * @param StoreManager $storeManager
     * @param CatalogRule $catalogRule
     * @param CatalogRuleRepositoryInterface $catalogRuleRepository
     * @param CustomerGroupCollection $customerGroup
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Json $serializer,
        Context $context,
        PricingHelper $pricingHelper,
        StoreManager $storeManager,
        CatalogRule $catalogRule,
        CatalogRuleRepositoryInterface $catalogRuleRepository,
        CustomerGroupCollection $customerGroup,
        Registry $registry,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->serializer = $serializer;
        $this->pricingHelper = $pricingHelper;
        $this->storeManager = $storeManager;
        $this->catalogRule = $catalogRule;
        $this->catalogRuleRepository = $catalogRuleRepository;
        $this->customerGroup = $customerGroup;
        $this->coreRegistry = $registry;
        $this->resource = $resource;
        $this->_eavAttribute = $eavAttribute;
    }

    /**
     * Retrieve product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->coreRegistry->registry('product'));
        }
        $product = $this->getData('product');

        return $product;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getPriceData()
    {
        $data = [];
        $today = new \DateTime();
        $now = \Safe\strtotime(date("Y-m-d H:i:s"));
        $productId = $this->getProduct()->getId();
        $storeId = $this->getRequest()->getParam(
            'store',
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );
        $store = $this->storeManager->getStore($storeId);
        $websiteId = $store->getWebsiteId();
        $customerGroups = $this->customerGroup->toOptionArray();

        $prices = $this->getPricesFromProduct(0, 1, 0, $productId);

        if (count($prices)) {
            foreach ($prices as $price) {
                $rule = $this->getRulesFromProduct($price['rule_id']);

                $priceruleurl = '';

                $href = $this->getUrl('custom_pricing/priceRules/edit', [
                    'id' => $price['rule_id']
                ]);

                foreach ($rule as $rule) {
                    $priceruleurl = "<a target='_blank' href=\"{$href}\">{$rule['name']}</a>";

                    $price_type = '';
                    switch ($rule['default_price_type']) {
                        case '1':
                            $price_type = __("Absolute Price");
                            $symbol = '';
                            break;
                        case '2':
                            $price_type = __("Increase Fixed");
                            $symbol = '€';
                            break;
                        case '3':
                            $price_type = __("Decrease Fixed");
                            $symbol = '€';
                            break;
                        case '4':
                            $price_type = __("Increase Percentage");
                            $symbol = '%';
                            break;
                        case '5':
                            $price_type = __("Decrease Percentage");
                            $symbol = '%';
                            break;
                    }
                }

                $customerconditions = '';

                if (isset($rule['customer_serialized'])) {
                    $baseconditions = $this->serializer->unserialize(
                        $rule['customer_serialized']
                    );
                }

                if (isset($baseconditions)) {
                    $type = $baseconditions['aggregator'];
                    if ($type == 'any') {
                        $type = "<strong>OU</strong>";
                    } elseif ($type == 'all') {
                        $type = "<strong>ET</strong>";
                    }

                    if (isset($baseconditions['conditions'])) {
                        $count = count($baseconditions['conditions']);

                        $i = 0;

                        foreach (
                            $baseconditions['conditions']
                            as $basecondition
                        ) {
                            $i++;

                            $operator = '';

                            switch ($basecondition['operator']) {
                                case '==':
                                    $operator = '<u>est</u>';
                                    break;
                                case '2':
                                    $price_type = __("Increase Fixed");
                                    $symbol = '€';
                                    break;
                                case '{}':
                                    $operator = '<u>contient</u>';
                                    break;
                                case '4':
                                    $price_type = __("Increase Percentage");
                                    $symbol = '%';
                                    break;
                                case '5':
                                    $price_type = __("Decrease Percentage");
                                    $symbol = '%';
                                    break;
                            }

                            if ($basecondition['attribute'] != 'specified') {
                                $optionid = $basecondition['value'][0];

                                $attributeId = $this->_eavAttribute->getIdByCode(
                                    \Magento\Customer\Model\Customer::ENTITY,
                                    $basecondition['attribute']
                                );

                                $label = $this->getAtributeLabel($attributeId);
                                $label = __($label[0]['frontend_label']);

                                $value = $this->getAtributeOption($optionid);
                                $value = __($value[0]['value']);
                            } else {
                                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                                $customerObj = $objectManager
                                    ->create('Magento\Customer\Model\Customer')
                                    ->load($basecondition['value'][0]);
                                $customerName =
                                    $customerObj->getFirstname() .
                                    ' ' .
                                    $customerObj->getLasstname();

                                $label = __('Customer');
                                $value =
                                    $customerName .
                                    ' ' .
                                    $basecondition['value'][0];
                            }

                            $customerconditions .=
                                $label . ' ' . $operator . ' ' . $value;

                            if ($i < $count) {
                                $customerconditions .= ' ' . $type . ' ';
                            }
                        }
                    } else {
                        $customerconditions = 'Tous';
                    }
                }

                $data[] = [
                    'customer_group' => 1,
                    'price' => number_format($price['custom_price'], 2),
                    'catalog_rule' => $priceruleurl,
                    'type' => $price_type,
                    'reduction' =>
                        round($rule['default_price_value'], 2) . $symbol,
                    'customers' => $customerconditions
                ];
            }

            return $data;
        }
        $data = [];

        return $data;
    }

    /**
     * Get active rule data based on few filters
     *
     * @param int|string $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param int $productId
     * @return array
     */
    public function getPricesFromProduct(
        $date,
        $websiteId,
        $customerGroupId,
        $productId
    ) {
        $connection = $this->getConnection();
        if (is_string($date)) {
            $date = strtotime($date);
        }
        $select = $connection
            ->select()
            ->from('bss_product_price')
            ->where('product_id = ?', $productId);

        return $connection->fetchAll($select);
    }

    /**
     * Get active rule data based on few filters
     *
     * @param int|string $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param int $productId
     * @return array
     */
    public function getRulesFromProduct($ruleId)
    {
        $connection = $this->getConnection();

        $select = $connection
            ->select()
            ->from('bss_price_rules')
            ->where('id = ?', $ruleId);

        return $connection->fetchAll($select);
    }

    /**
     * Get active rule data based on few filters
     *
     * @param int $optionid
     * @return array
     */
    public function getAtributeOption($optionid)
    {
        $connection = $this->getConnection();

        $select = $connection
            ->select()
            ->from('eav_attribute_option_value')
            ->where('option_id = ?', $optionid);

        return $connection->fetchAll($select);
    }

    /**
     * Get active rule data based on few filters
     *
     * @param int $attributeid
     * @return array
     */
    public function getAtributeLabel($attributeid)
    {
        $connection = $this->getConnection();

        $select = $connection
            ->select()
            ->from('eav_attribute')
            ->where('attribute_id = ?', $attributeid);

        return $connection->fetchAll($select);
    }

    /**
     * Get active rule data based on few filters
     *
     * @param int|string $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param int $productId
     * @return array
     */
    public function getPriceFromProduct(
        $date,
        $websiteId,
        $customerGroupId,
        $productId
    ) {
        $connection = $this->getConnection();
        if (is_string($date)) {
            $date = strtotime($date);
        }
        $select = $connection
            ->select()
            ->from('bss_product_price')
            ->where('product_id = ?', $productId);

        return $connection->fetchAll($select);
    }

    /**
     * Get default connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getConnection()
    {
        if ($this->connection == null) {
            $this->connection = $this->resource->getConnection();
        }
        return $this->connection;
    }
}
