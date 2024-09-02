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
namespace Webkul\AttrBaseSplitcart\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Quote\Model\QuoteFactory;

class BeforeOrderPlaced implements ObserverInterface
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
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;
    /**
     * __construct
     *
     * @param \Webkul\AttrBaseSplitcart\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param QuoteFactory $quoteFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     */
    public function __construct(
        \Webkul\AttrBaseSplitcart\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        QuoteFactory $quoteFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
        $this->_request = $request;
        $this->cart = $cart;
        $this->productRepository = $productRepository;
    }
    /**
     * Update the minicart
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->helper->checkAttributesplitcartStatus()) {
                $this->helper->removeCustomQuote();
                if ($this->checkoutSession->getTempFirstCheckoutCheck() == 1) {
                    $this->helper->addVirtualCartToQuote();
                    $this->checkoutSession->unsTempFirstCheckoutCheck();
                    $this->checkoutSession->setTempFirstCheckoutCheckForView(1);
                } else {
                    $this->helper->addQuoteToVirtualCart();
                }
                $this->getUpdatedQuote();
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Observer_ShoppingCart_execute Exception : " . $e->getMessage()
            );
        }
    }

    /**
     * [getUpdatedQuote used to remove items of other attribute value from the quote].
     *
     * @param [array] $attrsplitcartData [current attribute data]
     *
     * @return void
     */
    public function getUpdatedQuote()
    {
        try {
            $oldQuote = $this->cart->getQuote();
            $newQuote = $this->quoteFactory->create();
            $newQuote->setStoreId($oldQuote->getStoreId());
            if ($oldQuote->getCustomerId()) {
                $newQuote->setCustomerId($oldQuote->getCustomerId());
                $newQuote->setCustomerGroupId($oldQuote->getCustomerGroupId());
                $newQuote->setCustomerEmail($oldQuote->getCustomerEmail());
                $newQuote->setCustomerFirstname($oldQuote->getCustomerFirstname());
                $newQuote->setCustomerLastname($oldQuote->getCustomerLastname());
                $newQuote->setCustomerDob($oldQuote->getCustomerDob());
                $newQuote->setCustomerGender($oldQuote->getCustomerGender());
            }
            $newQuote->merge($oldQuote);
            $newQuote->collectTotals()->save();
            $oldQuote->setIsActive(0)->save();
            $this->checkoutSession->replaceQuote($newQuote);
            $newQuote->setIsActive(1)->save();
            $this->checkoutSession->setCartWasUpdated(true);
            $attrCode = $this->helper->getSelectedAttribute();
            $count = 0;
            foreach ($newQuote->getAllItems() as $item) {
                $TypeId = $item->getProduct()->getTypeId();
                if (!$item->hasParentItemId()) {
                    if ($TypeId == 'configurable') {
                        $ItemId = $item->getItemId();
                        $quoteId = $item->getQuoteId();
                        $productId = $item->getProduct()->getEntityId();

                        $quoteItem = $this->quoteItemFactory->create()->getCollection()
                            ->addFieldToFilter('quote_id', ['eq' => $quoteId])
                            ->addFieldToFilter('parent_item_id', ['eq' => $ItemId])
                            ->getFirstItem();
                        $productSku = $this->product->create()->load($quoteItem->getProductId())->getSku();

                        $product = $this->productRepository->get($productSku);
                        $attributeOptionId = $product->getData($attrCode);
                    } else {
                        $productId = $item->getProduct()->getEntityId();

                        $productSku = $this->product->create()->load($productId)->getSku();
                        $product = $this->productRepository->get($productSku);
                        $attributeOptionId = $product->getData($attrCode);
                    }

                    if ($attributeOptionId === 0) {
                        $attributeOptionId = 0;
                    }
                    if ($attributeOptionId == "") {
                        $attributeOptionId = -1;
                    }

                    $count += $item->getQty();

                }
            }
            $newQuote->setItemsQty($count);
            $newQuote->save();

            $newQuote->collectTotals()->save();

            $this->checkoutSession->replaceQuote($newQuote);
            $this->checkoutSession->setCartWasUpdated(true);
        } catch (\Exception $e) {
             $e->getMessage();
        }
    }
}
