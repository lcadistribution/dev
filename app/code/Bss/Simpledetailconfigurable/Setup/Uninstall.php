<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at thisURL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Eav\Setup\EavSetupFactory;

class Uninstall implements UninstallInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    protected $setupData;

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * Construct.
     *
     * @param ModuleDataSetupInterface $setupData
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setupData,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->setupData = $setupData;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Remove table in db
     *
     * @param SchemaSetupInterface $installer
     * @param string $table
     */
    private function removeTable(SchemaSetupInterface $installer, $table)
    {
        if ($installer->tableExists($table)) {
            $installer->getConnection()->dropTable($table);
        }
    }

    /**
     * Uninstall module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $installer = $setup;
        /**
         * Uninstall will remove tables of module.
         */
        $this->removeTable($installer, $installer->getTable('sdcp_preselect'));
        $this->removeTable($installer, $installer->getTable('sdcp_product_enabled'));
        $this->removeTable($installer, $installer->getTable('sdcp_custom_url'));
        /**
         * Remove eav attributes
         */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setupData]);
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'only_display_product_page');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'redirect_to_configurable_product');

        $setup->endSetup();
    }
}
