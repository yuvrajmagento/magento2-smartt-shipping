<?php

namespace Smarttshipping\Shipping\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Controller\Adminhtml\Order as AdminOrder;

class SaveRequote extends \Magento\Backend\App\Action
{
    
    protected $JsonFactory;
    protected $dispatchdetailFactory;
    protected $messageManager;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Smarttshipping\Shipping\Helper\Data $helperData,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Controller\Result\JsonFactory $JsonFactory,
        \Smarttshipping\Shipping\Model\DispatchdetailFactory $dispatchdetailFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->_helperData = $helperData;
        $this->_orderRepository = $orderRepository;
        $this->JsonFactory = $JsonFactory;
        $this->dispatchdetailFactory = $dispatchdetailFactory;
        $this->messageManager = $messageManager;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPost();

        parse_str($post['param'], $postData);
        $result = $this->JsonFactory->create();
        if ($postData && $postData['current_order_id']) {
            $order = $this->_orderRepository->get($postData['current_order_id']);
            //if (strpos($order->getShippingMethod(), "smartshipping_")!==false) {
                $res = explode('^^^^', $postData['requote_carrier']);
               
                if (!empty($res)) {
                    $dispatchcollection = $this->dispatchdetailFactory->create()->getCollection()->addFieldToFilter('order_id', $postData['current_order_id']);
                    $dispatchdetail = $this->dispatchdetailFactory->create();
                    if ($dispatchcollection->count() > 0) {
                        $dispatchid = $dispatchcollection->getFirstItem()->getId();
                        $dispatchdetail->load($dispatchid);
                    }

                    $dispatchdetail->setRequoteCarriername($res[0]);
                    $dispatchdetail->setRequoteTransitdays($res[1]);
                    $dispatchdetail->setRequotePrice($res[2]);
                    $dispatchdetail->setRequoteCarrierid($res[3]);
                    $dispatchdetail->setRequoteEstimate($postData['requote_estimate']);
                    $dispatchdetail->setIsrequote(1);
                    $dispatchdetail->setMagentoShipmentId(0);
                    $dispatchdetail->setCustomerId($order->getCustomerId());
                    $dispatchdetail->setOrderId($postData['current_order_id']);
                    $dispatchdetail->save();
                    $this->messageManager->addSuccessMessage(__('Smartt Shipping quote saved successfully'));
                    $result->setData(['status'  => "1"]);
                }
            //} else {
                //$result->setData(['status'  => "0"]);
            //}
        } else {
            $result->setData(['status'  => "0"]);
        }
        
        return $result;
    }
}
