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
use Webkul\AttrBaseSplitcart\Helper\Data;

/**
 *  Webkul AttrBaseSplitcart Cartover Proceedtocheckout controller
 */
class Checkoutwithmultipleaddress extends Action
{
    /**
     * @var \Webkul\AttrBaseSplitcart\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;
    
    /**
     * @param Context $context
     * @param Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     */
    public function __construct(
        Context $context,
        Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->formKeyValidator = $formKeyValidator;
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
                        'checkout/cart',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                $fields = $this->getRequest()->getParams();
               
                if (isset($fields['attrsplitcart_value'])
                    && $fields['attrsplitcart_value']!==""
                    && $fields['attrsplitcart_attribute']
                    && $fields['attrsplitcart_attribute']!==""
                ) {
                    $this->checkoutSession->setTempFirstCheckoutCheck('1');
                    $this->helper->getUpdatedQuote($fields);
                    return $this->resultRedirectFactory->create()->setPath(
                        'multishipping/checkout/addresses',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } catch (\Exception $e) {
                $this->helper->logDataInLogger("Controller_Checkoutwithmultipleaddress execute : ".$e->getMessage());
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath(
                    'checkout/cart',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } elseif ($isEnable) {
            $this->checkoutSession->setTempFirstCheckoutCheck('1');
            $fields = $this->getRequest()->getParams();
            $this->helper->getUpdatedQuote($fields);
            return $this->resultRedirectFactory->create()->setPath(
                'multishipping/checkout/addresses',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'multishipping/checkout/addresses',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
