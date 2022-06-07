<?php

namespace Smarttshipping\Shipping\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Controller\Adminhtml\Order as AdminOrder;

class Order extends \Magento\Backend\App\Action
{
    
    protected $JsonFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Smarttshipping\Shipping\Helper\Data $helperData,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Controller\Result\JsonFactory $JsonFactory
    ) {
        parent::__construct($context);
        $this->_helperData = $helperData;
        $this->_orderRepository = $orderRepository;
        $this->JsonFactory = $JsonFactory;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPost();

        parse_str($post['param'], $postData);
        $result = $this->JsonFactory->create();
        if ($postData && $postData['current_order_id']) {
            $order = $this->_orderRepository->get($postData['current_order_id']);
            //if (strpos($order->getShippingMethod(), "smartshipping_")!==false) {
                $methods = $this->_helperData->getQuotes($order, $postData);
                if (isset($methods['Success']) && $methods['Success'] == 1 && isset($methods['Carriers'])) {
                    $methods = $methods['Carriers'];
                    if ($methods) {
                        $result->setData(['status'  => "1", 'Data' => $methods]);
                    }
                } else {
                    $result->setData(['status'  => "0", 'Data' => '']);
                }
            //} else {
                //$result->setData(['status'  => "0", 'Data' => '']);
            //}
        } else {
            $result->setData(['status'  => "0", 'Data' => '']);
        }
        
        return $result;
    }
}
