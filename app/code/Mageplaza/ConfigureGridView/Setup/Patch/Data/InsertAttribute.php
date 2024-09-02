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
 * @package     Mageplaza_ConfigureGridView
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
declare(strict_types=1);

namespace Mageplaza\ConfigureGridView\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
 * Class InsertAttribute
 * @package Mageplaza\ConfigureGridView\Setup\Patch\Data
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
        $eavSetup->removeAttribute(Product::ENTITY, 'mp_gridview');

        /**
         * Add active Table Mode Config attribute to the product eav/attribute
         */
        $eavSetup->addAttribute(Product::ENTITY, 'mp_gridview', [
            'group'                   => 'Product Details',
            'type'                    => 'text',
            'backend'                 => '',
            'frontend'                => '',
            'label'                   => 'Active Grid View',
            'input'                   => 'select',
            'class'                   => 'mp-grid-view',
            'source'                  => \Mageplaza\ConfigureGridView\Model\Config\Source\Product::class,
            'global'                  => ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible'                 => true,
            'user_defined'            => true,
            'searchable'              => false,
            'filterable'              => false,
            'comparable'              => false,
            'visible_on_front'        => false,
            'used_in_product_listing' => true,
            'apply_to'                => 'configurable',
            'unique'                  => false,
            'note'                    => 'Choose Active to allow configurable products to show as table'
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
