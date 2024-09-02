<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
namespace Magedelight\Customerprice\Block\Adminhtml\Group\Edit;

use Magento\Customer\Api\GroupExcludedWebsiteRepositoryInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Adminhtml customer groups edit form
 */
class Form extends \Magento\Customer\Block\Adminhtml\Group\Edit\Form
{
    /**
     * @var \Magento\Tax\Model\TaxClass\Source\Customer
     */
    protected $_taxCustomer;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxHelper;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $_groupRepository;

    /**
     * @var \Magento\Customer\Api\Data\GroupInterfaceFactory
     */
    protected $groupDataFactory;

    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @var GroupExcludedWebsiteRepositoryInterface
     */
    private $groupExcludedWebsiteRepository;

    /**
     * @var \Magedelight\Customerprice\Helper\Data
     */
    private $customerPriceHelper;

     /**
     * @var Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterface
     */
    private $customerGroupPrice;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Tax\Model\TaxClass\Source\Customer $taxCustomer
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Customer\Api\Data\GroupInterfaceFactory $groupDataFactory
     * @param \Magedelight\Customerprice\Helper\Data $customerPriceHelper
     * @param \Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterface $customerGroupPrice
     * @param array $data
     * @param SystemStore|null $systemStore
     * @param GroupExcludedWebsiteRepositoryInterface|null $groupExcludedWebsiteRepository
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Tax\Model\TaxClass\Source\Customer $taxCustomer,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Api\Data\GroupInterfaceFactory $groupDataFactory,
        \Magedelight\Customerprice\Helper\Data $customerPriceHelper,
        \Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterface $customerGroupPrice,
        array $data = [],
        SystemStore $systemStore = null,
        GroupExcludedWebsiteRepositoryInterface $groupExcludedWebsiteRepository = null
    ) {
        $this->customerPriceHelper = $customerPriceHelper;
        $this->customerGroupPrice = $customerGroupPrice;
        $this->systemStore = $systemStore ?: ObjectManager::getInstance()->get(SystemStore::class);
        $this->groupExcludedWebsiteRepository = $groupExcludedWebsiteRepository
            ?: ObjectManager::getInstance()->get(GroupExcludedWebsiteRepositoryInterface::class);
        parent::__construct($context, $registry, $formFactory,$taxCustomer,$taxHelper,$groupRepository,$groupDataFactory, $data,$systemStore,$groupExcludedWebsiteRepository);
    }

    /**
     * Prepare form for render
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $groupId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_GROUP_ID);
        /** @var \Magento\Customer\Api\Data\GroupInterface $customerGroup */
        $customerGroupExcludedWebsites = [];
        if ($groupId === null) {
            $customerGroup = $this->groupDataFactory->create();
            $defaultCustomerTaxClass = $this->_taxHelper->getDefaultCustomerTaxClass();
        } else {
            $customerGroup = $this->_groupRepository->getById($groupId);
            $defaultCustomerTaxClass = $customerGroup->getTaxClassId();
            $customerGroupExcludedWebsites = $this->groupExcludedWebsiteRepository->getCustomerGroupExcludedWebsites(
                $groupId
            );
        }

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Group Information')]);

        $validateClass = sprintf(
            'required-entry validate-length maximum-length-%d',
            \Magento\Customer\Model\GroupManagement::GROUP_CODE_MAX_LENGTH
        );
        $name = $fieldset->addField(
            'customer_group_code',
            'text',
            [
                'name' => 'code',
                'label' => __('Group Name'),
                'title' => __('Group Name'),
                'note' => __(
                    'Maximum length must be less then %1 characters.',
                    \Magento\Customer\Model\GroupManagement::GROUP_CODE_MAX_LENGTH
                ),
                'class' => $validateClass,
                'required' => true
            ]
        );

        if ($customerGroup->getId() == 0 && $customerGroup->getCode()) {
            $name->setDisabled(true);
        }

        $fieldset->addField(
            'tax_class_id',
            'select',
            [
                'name' => 'tax_class',
                'label' => __('Tax Class'),
                'title' => __('Tax Class'),
                'class' => 'required-entry',
                'required' => true,
                'values' => $this->_taxCustomer->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'customer_group_excluded_website_ids',
            'multiselect',
            [
                'name' => 'customer_group_excluded_websites',
                'label' => __('Excluded Website(s)'),
                'title' => __('Excluded Website(s)'),
                'required' => false,
                'can_be_empty' => true,
                'values' => $this->systemStore->getWebsiteValuesForForm(),
                'note' => __('Select websites you want to exclude from this customer group.')
            ]
        );

        if($this->customerPriceHelper->isEnabled() && $this->customerPriceHelper->getConfig('customerprice/general/enable_customer_groupprice')){

            $fieldset->addField(
                'value',
                'text',
                [
                    'name' => 'value',
                    'label' => __('Group Price'),
                    'title' => __('Group Price'),
                    'required' => false,
                    'can_be_empty' => true,
                    'note'=>"Add + or - sign before the price then only it will work +5, -5",
                ]
            );

            $fieldset->addField(
                'price_type',
                'select',
                [
                    'name' => 'price_type',
                    'label' => __('Price Type'),
                    'title' => __('Price Type'),
                    'required' => false,
                    'can_be_empty' => true,
                    'values' => [0=>"Fixed",1=>'Percent'],
                ]
            );
            if ($groupId) {
                $model = $this->customerGroupPrice->load($groupId,'group_id');
                $form->addValues(['value'=>$model->getValue()]);
                $form->addValues(['price_type'=>$model->getPriceType()]);
            }
        }



        if ($customerGroup->getId() !== null) {
            // If edit add id
            $form->addField('id', 'hidden', ['name' => 'id', 'value' => $customerGroup->getId()]);
        }

        if ($this->_backendSession->getCustomerGroupData()) {
            $form->addValues($this->_backendSession->getCustomerGroupData());
            $this->_backendSession->setCustomerGroupData(null);
        } else {
            // TODO: need to figure out how the DATA can work with forms
            $form->addValues(
                [
                    'id' => $customerGroup->getId(),
                    'customer_group_code' => $customerGroup->getCode(),
                    'tax_class_id' => $defaultCustomerTaxClass,
                    'customer_group_excluded_website_ids' => $customerGroupExcludedWebsites
                ]
            );
        }
        
        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('customer/*/save'));
        $form->setMethod('post');
        $this->setForm($form);
    }
}
