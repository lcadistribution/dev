<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Ui\DataProvider\Product\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Ui\Component\Form;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\App\RequestInterface;

/**
 * Class Websites customizes websites panel.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Customerprice extends AbstractModifier
{
    const SORT_ORDER = 40;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * @var \Magento\Store\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var array
     */
    protected $websitesOptionsList;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $websitesList;

    /**
     * @var string
     */
    private $dataScopeName;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param LocatorInterface           $locator
     * @param StoreManagerInterface      $storeManager
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param GroupRepositoryInterface   $groupRepository
     * @param StoreRepositoryInterface   $storeRepository
     * @param type                       $dataScopeName\
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param RequestInterface $request
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        WebsiteRepositoryInterface $websiteRepository,
        GroupRepositoryInterface $groupRepository,
        StoreRepositoryInterface $storeRepository,
        $dataScopeName,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        RequestInterface $request
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->websiteRepository = $websiteRepository;
        $this->groupRepository = $groupRepository;
        $this->storeRepository = $storeRepository;
        $this->dataScopeName = $dataScopeName;
        $this->_scopeConfig = $scopeConfig;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $isEnabled = (string) $this->_scopeConfig->getValue('customerprice/general/enable', $storeScope);
        if (!$this->storeManager->isSingleStoreMode() && $isEnabled == 1 && $this->getProductType()) {
            $meta = array_replace_recursive(
                $meta,
                [
                    'customerprice' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'additionalClasses' => 'admin__fieldset-product-customerprice',
                                    'label' => __('Price Per Customer'),
                                    'collapsible' => true,
                                    'componentType' => Form\Fieldset::NAME,
                                    'sortOrder' => $this->getNextGroupSortOrder(
                                        $meta,
                                        'search-engine-optimization',
                                        self::SORT_ORDER
                                    ),
                                ],
                            ],
                        ],
                        'children' => $this->getPanelChildren(),
                    ],
                ]
            );
        }

        return $meta;
    }

    /**
     * Prepares panel children configuration.
     *
     * @return array
     */
    protected function getPanelChildren()
    {
        return [
            'customerprice_products_button_set' => $this->getButtonSet(),
            
        ];
    }

    /**
     * Returns Buttons Set configuration.
     *
     * @return array
     */
    protected function getButtonSet()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'Magedelight_Customerprice/js/components/container-customerprice-handler',
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'content1' => __(
                            'Apply discount for this product to customers.'
                        ),
                        'template' => 'ui/form/components/complex',
                        'createCustomerpriceButton' => 'ns = ${ $.ns }, index = create_customerprice_products_button',
                    ],
                ],
            ],
            'children' => [

                'create_customerprice_products_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' => $this->dataScopeName.'.customerpriceModal',
                                        'actionName' => 'trigger',
                                        'params' => ['active', true],
                                    ],
                                    [
                                        'targetName' => $this->dataScopeName.'.customerpriceModal',
                                        'actionName' => 'openModal',
                                    ],
                                ],
                                'title' => __('Price Per Customer Configuration'),
                                'sortOrder' => 20,

                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get product type
     *
     * @return null|string
     */
    private function getProductType()
    {
        $type = (string)$this->request->getParam('type', $this->locator->getProduct()->getTypeId());

        if ($type == 'simple' || $type == 'downloadable'
            || $type == 'virtual') {
            return true;
        } else {
            return false;
        }
    }
}
