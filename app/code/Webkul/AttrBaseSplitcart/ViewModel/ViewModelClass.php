<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_AttrBaseSplitcart
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AttrBaseSplitcart\ViewModel;

use \Webkul\AttrBaseSplitcart\Block\Index;
use \Webkul\AttrBaseSplitcart\Helper\Data as AttrBaseSplitcartHelper;
use \Magento\Framework\View\Element\Block\ArgumentInterface;

class ViewModelClass implements ArgumentInterface
{

    /**
     * @var Index
     */
    protected $blockData;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var AttrBaseSplitcartHelper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     *
     * @param Index $blockData
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonData
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param AttrBaseSplitcartHelper $helper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        Index $blockData,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Serialize\Serializer\Json $jsonData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        AttrBaseSplitcartHelper $helper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->blockData = $blockData;
        $this->taxHelper = $taxHelper;
        $this->checkoutSession = $checkoutSession;
        $this->jsonData = $jsonData;
        $this->_storeManager = $storeManager;
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Get block data
     *
     * @return \Webkul\AttrBaseSplitcart\Block\Index
     */
    public function getBlock()
    {
        return $this->blockData;
    }
    
    /**
     * Display card price
     *
     * @return int
     */
    public function displayCartBothPrices()
    {
        return $this->taxHelper->displayCartBothPrices() ? 2 : 1;
    }

    /**
     * Checkout session
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function checkoutSession()
    {
        return $this->checkoutSession;
    }
    /**
     * Json data
     *
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function jsonData()
    {

        return $this->jsonData;
    }

    /**
     * GetContinueUrl function
     *
     * @return string
     */
    public function getContinueUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * GetHelper function
     *
     * @return AttrBaseSplitcartHelper
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * GetJsonHelper function
     *
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
}
