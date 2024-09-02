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
 * @package     Mageplaza_TableCategoryView
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
declare(strict_types=1);

namespace Mageplaza\TableCategoryView\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
 * Class InsertAttribute
 * @package Mageplaza\TableCategoryView\Setup\Patch\Data
 */
class InsertAttribute implements
    DataPatchInterface,
    PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;

    /**
     * InsertAttribute constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup  = $moduleDataSetup;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(Category::ENTITY, 'mp_table_view');
        $eavSetup->removeAttribute(Category::ENTITY, 'mp_table_view_default');

        /**
         * Add active Table Mode Config attribute to the product eav/attribute
         */
        $eavSetup->addAttribute(Category::ENTITY, 'mp_table_view', [
            'type'         => 'int',
            'label'        => 'Mageplaza Attribute Config',
            'input'        => 'select',
            'sort_order'   => 100,
            'source'       => Boolean::class,
            'global'       => ScopedAttributeInterface::SCOPE_STORE,
            'visible'      => true,
            'required'     => false,
            'user_defined' => false,
            'default'      => '1',
            'group'        => 'Custom Design',
        ]);
        $eavSetup->addAttribute(Category::ENTITY, 'mp_table_view_default', [
            'type'         => 'int',
            'label'        => 'Mageplaza Attribute Default',
            'input'        => 'select',
            'sort_order'   => 101,
            'source'       => Boolean::class,
            'global'       => ScopedAttributeInterface::SCOPE_STORE,
            'visible'      => true,
            'required'     => false,
            'user_defined' => false,
            'default'      => '1',
            'group'        => 'Custom Design',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
