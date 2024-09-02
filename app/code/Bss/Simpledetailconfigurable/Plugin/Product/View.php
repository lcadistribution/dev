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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Simpledetailconfigurable\Plugin\Product;

use Bss\Simpledetailconfigurable\Block\Product\FinalPrice;
use Bss\Simpledetailconfigurable\CustomerData\ConfigurableItem;
use Bss\Simpledetailconfigurable\Helper\ModuleConfig;
use Magento\Framework\Serialize\Serializer\Json;

class View
{
    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var FinalPrice
     */
    protected $configurableItem;

    /**
     * @param Json $json
     * @param ModuleConfig $moduleConfig
     * @param ConfigurableItem $configurableItem
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json                $json,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig           $moduleConfig,
        \Bss\Simpledetailconfigurable\CustomerData\ConfigurableItem $configurableItem
    ) {
        $this->json = $json;
        $this->moduleConfig = $moduleConfig;
        $this->configurableItem = $configurableItem;
    }

    public function afterGetJsonConfig(
        \Magento\Catalog\Block\Product\View $subject,
        $result
    ) {
        if ($this->moduleConfig->isModuleEnable()) {
            $childProduct = $this->configurableItem->getChildByPreselect();
            if (!$childProduct) {
                return $result;
            }
            $priceInfo = $childProduct->getPriceInfo();
            $config = $this->json->unserialize($result);
            $config['prices']['baseOldPrice']['amount'] = $priceInfo->getPrice('regular_price')->getAmount()->getBaseAmount() * 1;
            $config['prices']['oldPrice']['amount'] = $priceInfo->getPrice('regular_price')->getAmount()->getValue() * 1;
            $config['prices']['basePrice']['amount'] = $priceInfo->getPrice('final_price')->getAmount()->getBaseAmount() * 1;
            $config['prices']['finalPrice']['amount'] = $priceInfo->getPrice('final_price')->getAmount()->getValue() * 1;
            return $this->json->serialize($config);
        }
        return $result;
    }
}
