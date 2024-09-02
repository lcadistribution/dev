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
namespace Bss\Simpledetailconfigurable\Override\Model\ResourceModel\Product\Type;

use Bss\Simpledetailconfigurable\Helper\ModuleConfig;
use Magento\Catalog\Model\ResourceModel\Product\Relation as ProductRelation;
use Magento\ConfigurableProduct\Model\AttributeOptionProviderInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionProvider;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Model\ResourceModel\Db\Context as DbContext;

class Configurable extends \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
{
    /**
     * @var ModuleConfig
     */
    protected $configSDCP;

    /**
     * @var OptionProvider|mixed
     */
    protected $optionProvider;

    /**
     * @param DbContext $context
     * @param ProductRelation $catalogProductRelation
     * @param string $connectionName
     * @param ScopeResolverInterface|null $scopeResolver
     * @param AttributeOptionProviderInterface|null $attributeOptionProvider
     * @param OptionProvider|null $optionProvider
     * @param ModuleConfig|null $configSDCP
     */
    public function __construct(
        DbContext                        $context,
        ProductRelation                  $catalogProductRelation,
        $connectionName = null,
        ScopeResolverInterface           $scopeResolver = null,
        AttributeOptionProviderInterface $attributeOptionProvider = null,
        OptionProvider                   $optionProvider = null,
        ModuleConfig                     $configSDCP = null
    ) {
        parent::__construct(
            $context,
            $catalogProductRelation,
            $connectionName,
            $scopeResolver,
            $attributeOptionProvider,
            $optionProvider
        );
        $this->optionProvider = $optionProvider ?? \Magento\Framework\App\ObjectManager::getInstance()
            ->get(OptionProvider::class);
        $this->configSDCP = $configSDCP ?? \Magento\Framework\App\ObjectManager::getInstance()
            ->get(ModuleConfig::class);
    }

    /**
     * Get children id
     *
     * @param int $parentId
     * @param bool $required
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getChildrenIds($parentId, $required = true)
    {
        if (!$this->configSDCP->isModuleEnable()) {
            return parent::getChildrenIds($parentId, $required);
        }

        $select = $this->getConnection()->select()->from(
            ['l' => $this->getMainTable()],
            ['product_id', 'parent_id']
        )->join(
            ['p' => $this->getTable('catalog_product_entity')],
            'p.' . $this->optionProvider->getProductEntityLinkField() . ' = l.parent_id',
            []
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            'e.entity_id = l.product_id', // skip check required_options because enable SDCP
            []
        )->where(
            'p.entity_id IN (?)',
            $parentId,
            \Zend_Db::INT_TYPE
        );

        $childrenIds = [
            0 => array_column(
                $this->getConnection()->fetchAll($select),
                'product_id',
                'product_id'
            )
        ];

        return $childrenIds;
    }
}
