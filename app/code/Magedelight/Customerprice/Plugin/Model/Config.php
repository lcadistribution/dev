<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
namespace Magedelight\Customerprice\Plugin\Model;

use Magento\Store\Model\StoreManagerInterface;
use Magedelight\Customerprice\Helper\Data;

class Config
{

    /**
     * @var use StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var use Magedelight\Customerprice\Helper\Data
     */
    protected $helper;

    /**
     * @param Data $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $helper,
        StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    public function afterGetAttributeUsedForSortByArray(\Magento\Catalog\Model\Config $catalogConfig, $options)
    {
        if ($this->helper->getConfig('customerprice/general/hide_price') && !$this->helper->getConfig('customerprice/general/show_price')) {
            unset($options['price']);
        }
        return $options;
    }
}
