<?php
namespace Smarttshipping\Shipping\Controller\Adminhtml\Shipping;

use Magento\Framework\Controller\ResultFactory;

class Cancelshipment extends \Magento\Backend\App\Action
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
        try {
            $type = $this->getRequest()->getParam('type');
            $reason = $this->getRequest()->getParam('comment');
            $shipment_id = $this->getRequest()->getParam('shipment_id');
            if ($shipment_id) {
                $shipment = $this->_shipmentRepository->get($shipment_id);
                $order_id = $shipment->getOrderId();
                $order = $this->_orderRepository->get($order_id);
                if (strpos($order->getShippingMethod(), "smartshipping_")!==false) {
                    $result = $this->_helperData->cancelShipment($order, $shipment, $type, $reason);
                    if (isset($result['Message']) && isset($result['Success']) && $result['Success']) {
                        $this->messageManager->addSuccess(__('Smart Shipping Shipment has been cancelled successfully.'));
                    } elseif (isset($result['Message'])) {
                        $this->messageManager->addError($result['message']);
                    } else {
                        $this->messageManager->addError(__('Error appeared while cancel smart shipping shipment.'));
                    }
                }
            }
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
