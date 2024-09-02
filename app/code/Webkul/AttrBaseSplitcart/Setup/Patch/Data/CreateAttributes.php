<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AttrBaseSplitcart
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AttrBaseSplitcart\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateAttributes implements DataPatchInterface
{
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * AccountPurposeCustomerAttribute constructor.
     * @param ModuleDataSetupInterface $setup
     * @param Config $eavConfig
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        Config $eavConfig,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->setup = $setup;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        $attributeSetId = $customerSetup->getDefaultAttributeSetId($customerEntity->getEntityTypeId());
        $attributeGroupId = $customerSetup->getDefaultAttributeGroupId(
            $customerEntity->getEntityTypeId(),
            $attributeSetId
        );
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'attr_virtual_cart',
            [
                'type'         => 'text',
                'label'        => 'Attribute Virtual Cart',
                'input'        => 'text',
                'required'     => false,
                'visible'      => false,
                'user_defined' => true,
                'sort_order'   => 1000,
                'position'     => 1000,
                'system'       => false,
                'global'       => true,
                'default'      => '0'
            ]
        );
        //add attribute to attribute set
        $newAttribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'attr_virtual_cart');
        $newAttribute->addData([
            'used_in_forms' => ['adminhtml_customer'],
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId
        ]);
        $newAttribute->save();
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
