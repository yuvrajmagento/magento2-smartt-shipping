<?php
namespace Smarttshipping\Shipping\Observer\Adminhtml;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Api\Data\ShipmentTrackInterfaceFactory;

class CreateShipmentAfter implements ObserverInterface
{
    
    protected $dispatchdetailFactory;
    protected $trackFactory;

    public function __construct(
        \Smarttshipping\Shipping\Model\DispatchdetailFactory $dispatchdetailFactory,
        ShipmentTrackInterfaceFactory $trackFactory
    ) {
        $this->dispatchdetailFactory = $dispatchdetailFactory;
        $this->trackFactory = $trackFactory;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        $shipment = $observer->getEvent()->getShipment();
        $order    = $shipment->getOrder();
        
        if ($order->getId() && $shipment->getId()) {
            $dispatchcollection = $this->dispatchdetailFactory->create()->getCollection()->addFieldToFilter('order_id', $order->getId())->addFieldToFilter('magento_shipment_id', 0);
            
            if ($dispatchcollection->count() > 0) {
                $dispatchdetail = $this->dispatchdetailFactory->create();
                $dispatchid = $dispatchcollection->getFirstItem()->getId();
                $TrackingNumber = $dispatchcollection->getFirstItem()->getTrackingNumber();
				
                $dispatchdetail->load($dispatchid);
                $dispatchdetail->setMagentoShipmentId($shipment->getId());
                $dispatchdetail->save();
				
				$carrierName = substr($order->getShippingDescription(), strpos($order->getShippingDescription(), "-") + 1);    
                if (!empty($TrackingNumber)) {
                     $data = [
                        'carrier_code' => 'smartshipping',
                        'title' => $carrierName,
                        'number' => $TrackingNumber, // Replace with your tracking number
                     ];

                     $track = $this->trackFactory->create()->addData($data);
                     $shipment->addTrack($track)->save();
                }
            }
        }
    }
}
