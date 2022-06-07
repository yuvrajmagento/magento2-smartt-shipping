<?php
namespace Smarttshipping\Shipping\Controller\Adminhtml\Shipping;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Smarttshipping\Shipping\Helper\Data $helperData,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Magento\Framework\App\Response\RedirectInterface $redirect
    ) {
        parent::__construct($context);
        $this->_helperData = $helperData;
        $this->_orderRepository = $orderRepository;
        $this->_shipmentRepository = $shipmentRepository;
        $this->_redirect = $redirect;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        
        //return  $resultPage = $this->resultPageFactory->create();
        $customerid = '';
        try {
                $order_id = $this->getRequest()->getParam('order_id');
                $order = $this->_orderRepository->get($order_id);
                    $result = $this->_helperData->updateOrderShippingMethod($order);
                    /*if(isset($result['status']) && $result['status']=='success'){
                        $this->messageManager->addSuccess( __('Smart Shipping Dispatch has been created successfully.') );
                    } else if($result['status']=='error' && isset($result['message'])) {
                        $this->messageManager->addError( $result['message'] );
                    } else {
                        $this->messageManager->addError( __('Error appeared while create dispatch.') );
                    }*/
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Error appeared while save data').$e->getMessage());
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
    public function _isAllowed()
    {
        return true;
    }
}
