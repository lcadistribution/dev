<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Validator\ValidateException;

class AddAmastySkuAttribute implements DataPatchInterface
{
    public const ATTRIBUTE_NAME = 'mwishlist_sku';
    /**
     * @var EavSetup
     */
    private $eavSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetup = $eavSetupFactory->create(['setup' => $moduleDataSetup]);
    }

    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return AddAmastySkuAttribute
     * @throws LocalizedException|ValidateException
     */
    public function apply()
    {
        $this->eavSetup->addAttribute(
            Product::ENTITY,
            self::ATTRIBUTE_NAME,
            [
                'type' => 'text',
                'backend' => '',
                'attribute' => '',
                'frontend' => '',
                'label' => 'Amasty Searchable SKU for MWishlist',
                'input' => 'text',
                'class' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'default' => null,
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'used_for_sort_by' => false,
                'unique' => false,
                'apply_to' => 'simple,virtual,downloadable,bundle,configurable',
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );

        return $this;
    }
}
