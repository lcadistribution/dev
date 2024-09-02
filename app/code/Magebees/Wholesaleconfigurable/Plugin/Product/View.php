<?php 
namespace Magebees\Wholesaleconfigurable\Plugin\Product;
use Magento\Framework\View\Result\Page as ResultPage;
use Magento\Catalog\Model\Product;
class View
{
    protected $scopeConfig;
	protected $_helper;
	public function __construct(
		\Magebees\Wholesaleconfigurable\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
		$this->_helper = $helper;
    }
	
	public function beforeInitProductLayout(
        \Magento\Catalog\Helper\Product\View $subject,
        ResultPage $resultPage, Product $product, $params = null
    ) {
		$moduleEnabled = $this->_helper->moduleEnabled();

		if($moduleEnabled)
		{
			if($product->getTypeId() == 'configurable'){
				$layout = $resultPage->getLayout();
				$layout->getUpdate()->addHandle('catalog_product_view_type_configurable');
				$block = $layout->getBlock('product.info.options.configurable');
				if($block){
					$layout->unsetElement('product.info.options.configurable');
				}

				$blocks = $layout->getBlock('product.info.options.swatches');
				if($blocks){
					$layout->unsetElement('product.info.options.swatches');
				} 
				
				$blocksadd = $layout->getBlock('product.info.options.wrapper.bottom');
				if($blocksadd){
					$layout->unsetElement('product.info.options.wrapper.bottom');
				} 
			}

			if($product->getTypeId() == 'grouped'){
				$moduleEnabled = $this->_helper->enabledGropedPrd();
				if($moduleEnabled){
				
					$layout = $resultPage->getLayout();
					$layout->getUpdate()->addHandle('catalog_product_view_type_grouped');


					$blocks = $layout->getBlock('product.info.grouped');
					if($blocks){
						$layout->unsetElement('product.info.grouped');
					} 

					$blocksadd = $layout->getBlock('product.info.addtocart');
					if($blocksadd){
						$layout->unsetElement('product.info.addtocart');
					}
				}	
			}			
		}	
		
		return [$resultPage, $product, $params];
    }
}
