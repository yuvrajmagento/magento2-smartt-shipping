<?php
namespace Smarttshipping\Shipping\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

class CreateDispatch extends \Magento\Framework\App\Action\Action
{
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Smarttshipping\Shipping\Model\AddresssettingFactory $addresssettingFactory,
        \Smarttshipping\Shipping\Helper\Data $helperData,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Registry $registry
    ) {
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_addresssettingFactory = $addresssettingFactory;
        $this->_helperData = $helperData;
        $this->_orderRepository = $orderRepository;
        $this->_shipmentRepository = $shipmentRepository;
        $this->_registry = $registry;
        $this->_redirect = $redirect;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get('Magento\Customer\Model\Url')->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }


    public function execute()
    {
        $customerid = '';
        try {
            $shipment_id = $this->getRequest()->getParam('shipment_id');
            if ($shipment_id) {
                $shipment = $this->_shipmentRepository->get($shipment_id);
                $order_id = $shipment->getOrderId();
                $order = $this->_orderRepository->get($order_id);
                if (strpos($order->getShippingMethod(), "smartshipping_")!==false) {
                    $result = $this->_helperData->createDispatch($order, $shipment);
                    if (isset($result['status']) && $result['status']=='success') {
                        //$this->messageManager->addSuccess(__('Smart Shipping Dispatch has been created successfully.'));
                    } elseif ($result['status']=='error' && isset($result['message'])) {
                        $this->messageManager->addError($result['message']);
                    } else {
                        $this->messageManager->addError(__('Error appeared while create dispatch.'));
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
}
