<?php
namespace Magebees\Wholesaleconfigurable\Controller\Index;
use \Magento\Framework\App\Action\Action;
class Price extends Action
{
    
    protected $resultPageFactory;
    protected $mappings = [];
    protected $storeManager;
    protected $currencyFactory;
    protected $price;
   
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Catalog\Model\Product\Type\Price $price,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory

    ) {
        parent::__construct($context);
		 $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->price = $price;

    }    

    public function execute()  {
		$qty =  $this->getRequest()->getParam('qty');
		$productid =  $this->getRequest()->getParam('productid');
		$productInfo = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productid);
		$cntprice = $this->price->getFinalPrice($qty, $productInfo);
		$cntprice = $this->convertToBaseCurrency($cntprice);
		
		$this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($cntprice));
		return;
	}
	
	public function convertToBaseCurrency($price)
    {
        $currentCurrency = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $baseCurrency = $this->storeManager->getStore()->getBaseCurrency()->getCode();
        $rate = $this->currencyFactory->create()->load($baseCurrency)->getAnyRate($currentCurrency);
        $returnValue = $price * $rate;
        return $returnValue;
    }
	

}
