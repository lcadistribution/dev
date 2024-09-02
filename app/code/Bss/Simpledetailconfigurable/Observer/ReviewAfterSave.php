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
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Simpledetailconfigurable\Observer;

use Magento\Framework\App\Cache\Manager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ReviewAfterSave implements ObserverInterface
{
    /**
     * @var Manager
     */
    protected $cacheManager;

    /**
     * @param Manager $cacheManager
     */
    public function __construct(
        \Magento\Framework\App\Cache\Manager $cacheManager
    ) {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $this->cacheManager->flush([$this->cacheManager->getAvailableTypes()[11]]);
    }
}
