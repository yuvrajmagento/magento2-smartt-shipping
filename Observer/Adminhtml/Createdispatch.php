<?php
namespace Smarttshipping\Shipping\Observer\Adminhtml;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Controller\ResultFactory;

class Createdispatch implements ObserverInterface
{
    
    protected $request;
    protected $redirect;
    protected $messageManager;
    protected $actionFlag;
    
    public function __construct(
        \Smarttshipping\Shipping\Helper\Data $helperData,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        \Magento\Framework\App\ActionFlag $actionFlag
    ) {
        $this->_helperData = $helperData;
        $this->_orderRepository = $orderRepository;
        $this->redirect = $redirect;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->actionFlag = $actionFlag;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $controller = $observer->getEvent()->getControllerAction();
        $order_id = $request->getParam('order_id');
        
        if ($order_id) {
            $order = $this->_orderRepository->get($order_id);
            if (strpos($order->getShippingMethod(), "smartshipping_")!==false && empty($request->getParam('pickdatetime'))) {
                $this->messageManager->addErrorMessage(__('Please select Pickup date and create shipment.'));
                $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->redirect->redirect($controller->getResponse(), 'sales/order/view', ['order_id' => $order_id]);
            }
            
            try {
                
                if (strpos($order->getShippingMethod(), "smartshipping_")!==false) {
                    $result = $this->_helperData->createDispatch($order);
                    if (isset($result['status']) && $result['status']=='success') {
                        //$this->messageManager->addSuccessMessage(__('Smart Shipping Dispatch has been created successfully.'));
                        return $this;
                    } elseif ($result['status']=='error' && isset($result['message'])) {
                        $this->messageManager->addErrorMessage($result['message']);
                        $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                        $this->redirect->redirect($controller->getResponse(), 'sales/order/view', ['order_id' => $order_id]);
                    } else {
                        $this->messageManager->addErrorMessage(__('Error appeared while create dispatch.'));
                        $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                        $this->redirect->redirect($controller->getResponse(), 'sales/order/view', ['order_id' => $order_id]);
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Error appeared while save data').$e->getMessage());
                $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->redirect->redirect($controller->getResponse(), 'sales/order/view', ['order_id' => $order_id]);
            }
        }
    }
}
