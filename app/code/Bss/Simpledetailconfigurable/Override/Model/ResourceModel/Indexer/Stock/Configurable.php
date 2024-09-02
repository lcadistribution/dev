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
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Override\Model\ResourceModel\Indexer\Stock;

use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\CatalogInventory\Model\Indexer\Stock\Action\Full;
use Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\DefaultStock;
use Magento\Framework\App\ObjectManager;

class Configurable extends \Magento\ConfigurableProduct\Model\ResourceModel\Indexer\Stock\Configurable
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher|mixed
     */
    protected $activeTableSwitcher;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    protected $configSDCP;

    /**
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $configSDCP
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param $connectionName
     * @param \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher|null $activeTableSwitcher
     */
    public function __construct(
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $configSDCP,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $connectionName = null,
        \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher $activeTableSwitcher = null
    ) {
        parent::__construct(
            $context,
            $tableStrategy,
            $eavConfig,
            $scopeConfig,
            $connectionName,
            $activeTableSwitcher
        );
        $this->activeTableSwitcher = $activeTableSwitcher ?: ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher::class
        );
        $this->configSDCP = $configSDCP;
    }

    /**
     * Get the select object for get stock status by configurable product ids
     *
     * @param int|array $entityIds
     * @param bool $usePrimaryTable use primary or temporary index table
     * @return \Magento\Framework\DB\Select
     */
    protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
    {
        $metadata = $this->getMetadataPool()->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $connection = $this->getConnection();
        $table = $this->getActionType() === Full::ACTION_TYPE
            ? $this->activeTableSwitcher->getAdditionalTableName($this->getMainTable())
            : $this->getMainTable();
        $idxTable = $usePrimaryTable ? $table : $this->getIdxTable();
        $select = DefaultStock::_getStockStatusSelect($entityIds, $usePrimaryTable);
        $linkField = $metadata->getLinkField();
        $select->reset(
            \Magento\Framework\DB\Select::COLUMNS
        )->columns(
            ['e.entity_id', 'cis.website_id', 'cis.stock_id']
        )->joinLeft(
            ['l' => $this->getTable('catalog_product_super_link')],
            'l.parent_id = e.' . $linkField,
            []
        )->join(
            ['le' => $this->getTable('catalog_product_entity')],
            'le.entity_id = l.product_id',
            []
        )->joinInner(
            ['cpei' => $this->getTable('catalog_product_entity_int')],
            'le.' . $linkField . ' = cpei.' . $linkField
            . ' AND cpei.attribute_id = ' . $this->_getAttribute('status')->getId()
            . ' AND cpei.value = ' . ProductStatus::STATUS_ENABLED,
            []
        )->joinLeft(
            ['i' => $idxTable],
            'i.product_id = l.product_id AND cis.website_id = i.website_id AND cis.stock_id = i.stock_id',
            []
        )->columns(
            ['qty' => new \Zend_Db_Expr('0')]
        );
        $statusExpr = $this->getStatusExpression($connection);

        // Nếu module SDCP enable thì sản phẩm cha dù có required_options vẫn có thể in_stock men.
        $stock = $this->configSDCP->isModuleEnable() ? 'i.stock_status' : '0';

        $optExpr = $connection->getCheckSql("le.required_options = 0", 'i.stock_status', $stock);
        $stockStatusExpr = $connection->getLeastSql(["MAX({$optExpr})", "MIN({$statusExpr})"]);

        $select->columns(['status' => $stockStatusExpr]);

        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds, \Zend_Db::INT_TYPE);
        }

        return $select;
    }
}
