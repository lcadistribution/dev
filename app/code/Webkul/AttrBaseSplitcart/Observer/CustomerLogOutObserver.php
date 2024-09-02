<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AttrBaseSplitcart
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AttrBaseSplitcart\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerLogOutObserver implements ObserverInterface
{
    /**
     * @var \Webkul\AttrBaseSplitcart\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Webkul\AttrBaseSplitcart\Cookie\Guestcart
     */
    protected $guestCart;

    /**
     * @param \Webkul\AttrBaseSplitcart\Helper\Data $helper
     * @param \Webkul\AttrBaseSplitcart\Cookie\Guestcart $guestCart
     */
    public function __construct(
        \Webkul\AttrBaseSplitcart\Helper\Data $helper,
        \Webkul\AttrBaseSplitcart\Cookie\Guestcart $guestCart
    ) {
        $this->helper = $helper;
        $this->guestCart = $guestCart;
    }

    /**
     * Function execute
     *
     * Executes when layout_generate_blocks_after event hit,
     * and used to remove default summary block
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $this->guestCart->delete();
            $this->guestCart->set(0, 3600);
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("CustomerLogoutObserver execute : ".$e->getMessage());
        }
    }
}
