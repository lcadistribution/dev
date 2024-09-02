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

class RemoveSummary implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    
    /**
     * @var \Webkul\AttrBaseSplitcart\Helper\Data
     */
    protected $helper;
    
    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Webkul\AttrBaseSplitcart\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\AttrBaseSplitcart\Helper\Data $helper
    ) {
        $this->request = $request;
        $this->helper = $helper;
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
        $page = $this->request->getFullActionName();
        if ($this->helper->checkAttributesplitcartStatus() && $page=="checkout_cart_index") {
            $layout = $observer->getLayout();
            $layout->unsetElement("cart.summary");
        }
    }
}
