<?php
namespace Magebees\Wholesaleconfigurable\Controller\Index;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
    public function execute()
    {	
		
        $isAjax = $this->getRequest()->isAjax();
        if ($isAjax) {
			//echo "sd"; die;
			$this->_pageFactory = $this->_objectManager->get('\Magento\Framework\View\Result\PageFactory');
			$resultPage= $this->_pageFactory->create();
			$layoutblk = $resultPage->addHandle('wholesaleconfigurable_index_request')->getLayout();

			$res = $layoutblk->getBlock('wholesaleconfigurableoptions')->toHtml();

			$output=[];
			$output['sucess']=true;
			$output['product_detail']=$res;
			return $this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($output));
            
        } else {
            return $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
        }
    }
}
