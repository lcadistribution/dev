<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magedelight\Customerprice\Helper\Data;
use Magento\Framework\App\Cache\Manager;

class RefreshCache implements ObserverInterface
{
    
    /**
     * @var Manager
     */
    private $cacheManager;

    /**
     * @var Data
     */
    private $helper;
    
    /**
     * @param AbstractRestrictHelper $restrictHelper
     * @param Manager $cacheManager
     */
    public function __construct(
        Manager $cacheManager,
        Data $helper
    ) {
        $this->cacheManager = $cacheManager;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */

    public function execute(Observer $observer)
    {
        if ($this->helper->isEnabled()) {
            $types = $this->cacheManager->getAvailableTypes();
            $cac = [];
            foreach ($types as $t) {
                if ($t == 'block_html') {
                    $cac[] = $t;
                }
            }
            $this->cacheManager->clean($cac);
        }
    }
}
