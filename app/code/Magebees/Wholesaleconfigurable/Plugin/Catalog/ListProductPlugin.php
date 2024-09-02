<?php
namespace Magebees\Wholesaleconfigurable\Plugin\Catalog;
use Magento\Store\Model\ScopeInterface;
class ListProductPlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    protected $request;
    protected $urlInterface;
    protected $helper;


    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Framework\UrlInterface $urlInterface,
		\Magebees\Wholesaleconfigurable\Helper\Data $helper
	
	)
    {
        $this->_scopeConfig = $scopeConfig;
		$this->request = $request;
		$this->urlInterface = $urlInterface;
		$this->helper = $helper;
    }

    /**
     * Retrieve product details html
     *
     * @param \Magento\Catalog\Block\Product\ListProduct
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function aroundGetProductDetailsHtml(
        \Magento\Catalog\Block\Product\ListProduct $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product)
    {
        $result = $proceed($product);
		$enabled_product_listing = $this->_scopeConfig->getValue('wholesaleconfigurable/setting/enabled_product_listing',ScopeInterface::SCOPE_STORE);
		$enabled_show_options = $this->_scopeConfig->getValue('wholesaleconfigurable/setting/enabled_show_options',ScopeInterface::SCOPE_STORE);
		$enabledGroup = $this->_scopeConfig->getValue('wholesaleconfigurable/setting/enabled_group',ScopeInterface::SCOPE_STORE);
		$moduleEnabled = $this->helper->moduleEnabled();
		if($moduleEnabled){
			$mode =   $this->request->getParam('product_list_mode');
			$layout = $subject->getLayout();
			
			
			if($mode == "list")	{
				if($enabled_product_listing){
					$block = $layout->getBlock('category.product.type.details.renderers'); 
					if($block) {
						$layout->unsetElement('category.product.type.details.renderers');
					}

					if($layout->getBlock('category.product.list.wholesale')){
						$layout->unsetElement('category.product.list.wholesale');
					}
					if($product->getTypeId() == 'configurable' || $product->getTypeId() == 'grouped'){
						
						if($product->getTypeId() == 'grouped'){
							if(!$enabledGroup){
								return $result;
							}							
						}
						
						$result = '';
						if($layout->getBlock('category.product.list.wholesale')){
							$layout->unsetElement('category.product.list.wholesale');
						}

						$messageBlock = $layout->createBlock('Magento\Framework\View\Element\Template', 'category.product.list.wholesale');
						$messageBlock->setProduct($product);
						$messageBlock->setTemplate('Magebees_Wholesaleconfigurable::product/list/info_list.phtml');
						if ($parentBlock = $layout->getBlock('category.product.addto')) {
							$layout->getBlock('category.product.addto')->insert($messageBlock);
	}
						if ($parentBlock = $layout->getBlock('catalogsearch.product.addto')) {
							$layout->getBlock('catalogsearch.product.addto')->insert($messageBlock);
	}

						$pId = $product->getId();
						return $result . "<input id='btn-options-$pId' type='hidden' class='action tocart primary btn-options magebees_options'/>";
					}
				}	
			}else{
				if($enabled_show_options){
					$block = $layout->getBlock('category.product.type.details.renderers'); 
					if($block) {
						$layout->unsetElement('category.product.type.details.renderers');
					}

					if($product->getTypeId() == 'configurable' || $product->getTypeId() == 'grouped'){
						
						if($product->getTypeId() == 'grouped'){
							if(!$enabledGroup){
								return $result;
							}							
						}
						$result = '';
						$pId = $product->getId();
						$msg = 'Show Options';
						$pathbase = 'wholesaleconfigurable/index/index';
						$base_url = str_replace("index.php/", "", $this->urlInterface->getUrl());
						$baseUrl = $base_url.$pathbase;
						$btnUrl = $baseUrl."/productid/".$pId;
						return $result . "<button id='btn-options-$pId' type='button' href='$btnUrl' title='$msg' class='action tocart primary btn-options magebees_options'>$msg</button>";
					}
				}
			}	
		}
        return $result;
    }
}
