<?php
namespace Smarttshipping\Shipping\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Smarttshipping\Shipping\Model\ApiRequest $api
    ) {
        $this->_api = $api;
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $this->_api->getResponse();
        return $this->_pageFactory->create();
    }
}
