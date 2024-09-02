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
namespace Webkul\AttrBaseSplitcart\Controller\Cartover;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator;
use Webkul\AttrBaseSplitcart\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 *  Webkul AttrBaseSplitcart Cartover Proceedtocheckout controller
 */
class Proceedtocheckout extends Action
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;
    
    /**
     * @var \Webkul\AttrBaseSplitcart\Helper\Data
     */
    protected $helper;
    
    /**
     * @var ScopeConfigInterface $scopeConfig
     */
    public $scopeConfig;

     /**
      * @var \Magento\Checkout\Model\Session
      */
    protected $checkoutSession;

    /**
     * @var Magento\Customer\Model\Session
     */
      protected $customerSession;

    /**
     * @param Context $context
     * @param Validator $formKeyValidator
     * @param Data $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        Validator $formKeyValidator,
        Data $helper,
        ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
    }

    /**
     * To proceed to checkout a selected cart
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $isEnable = $this->helper->checkAttributesplitcartStatus();
        if ($this->getRequest()->isPost() && $isEnable) {
            try {
                if (!$this->formKeyValidator->validate($this->getRequest())) {
                    $this->messageManager->addError(__("Something Went Wrong !!!"));
                    return $this->resultRedirectFactory->create()->setPath(
                        'commanderapide',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                $isGuestCheckoutEnable = $this->scopeConfig->getValue(
                    'checkout/options/guest_checkout',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                if ($isGuestCheckoutEnable || $this->customerSession->isLoggedIn()) {
                    $fields = $this->getRequest()->getParams();
                    if (isset($fields['attrsplitcart_value'])
                        && $fields['attrsplitcart_value']!==""
                        && $fields['attrsplitcart_attribute']
                        && $fields['attrsplitcart_attribute']!==""
                    ) {
                        $this->checkoutSession->setTempFirstCheckoutCheck('1');
                        $verCart = $this->helper->getVirtualCart();
                        $this->checkoutSession->setTempVertualData($verCart);
                        $this->helper->getUpdatedQuote($fields);
                        return $this->resultRedirectFactory->create()->setPath(
                            'commanderapide',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                } else {
                    return $this->resultRedirectFactory->create()->setPath(
                        'customer/account/login',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } catch (\Exception $e) {
                $this->helper->logDataInLogger("Controller_Proceedtocheckout execute : ".$e->getMessage());
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath(
                    'commanderapide',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            $this->messageManager->addError(__("Something Went Wrong !!!"));
            return $this->resultRedirectFactory->create()->setPath(
                'onepagecheckout',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
