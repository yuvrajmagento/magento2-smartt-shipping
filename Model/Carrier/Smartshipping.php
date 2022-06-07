<?php
namespace Smarttshipping\Shipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use \Magento\Checkout\Model\Session as CheckoutSession;

class Smartshipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'smartshipping';
    
    protected $_logger;
    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;


    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;
    protected $_apirequest;
    protected $_shippingHelper;
    protected $_trackFactory;
    protected $_trackStatusFactory;
    //protected $shipmentReferenceRepository;
    protected $dispatchdetail;


    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\Product $product,
        \Smarttshipping\Shipping\Model\Logger $customLog,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Smarttshipping\Shipping\Model\ApiRequest $apirequest,
        \Smarttshipping\Shipping\Helper\Data $shippingHelper,
        CheckoutSession $checkoutSession,
        \Magento\Shipping\Model\Tracking\ResultFactory $resultFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        //\Temando\Shipping\Model\ResourceModel\Repository\ShipmentReferenceRepositoryInterface $shipmentReferenceRepository,
        \Smarttshipping\Shipping\Model\Dispatchdetail $dispatchdetail,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_logger = $logger;
        $this->_customLog = $customLog;
        $this->_product = $product;
        $this->_apirequest = $apirequest;
        $this->_shippingHelper = $shippingHelper;
        $this->_rateErrorFactory = $rateErrorFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_trackFactory = $resultFactory;
        $this->_trackStatusFactory = $trackStatusFactory;
        //$this->shipmentReferenceRepository = $shipmentReferenceRepository;
        $this->dispatchdetail = $dispatchdetail;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $result = $this->_rateResultFactory->create();
        if ($this->_shippingHelper->getIsFilter()) {
            $returnType = 'filter';
        } else {
            $returnType = 'all';
        }
        $methods = $this->_shippingHelper->getCarrierRates($request, false, false, $returnType);
        
        $shippingDropType = '';
        foreach ($request->getAllItems() as $item) {
            $shippingDropType = $item->getProduct()->getShippingTypeDrop();
        }
        
        if (isset($methods['Success']) && $methods['Success'] == 1 && isset($methods['Carriers'])) {
            $methods = $methods['Carriers'];
            if ($methods) {
                $quote = $this->_checkoutSession->getQuote();
                $quote->setData('smart_shipping_carrier_estimate', json_encode($methods, true));
                $quote->save();
                $carrierids = [];
                foreach ($methods as $shippingType) {
                    if (!in_array($shippingType['CarrierId'], $carrierids)) {
                        $rate = $this->_rateMethodFactory->create();
                        $rate->setCarrier($this->_code);
                        $rate->setCarrierTitle($this->getConfigData('title'));
                        $rate->setMethod($shippingType['CarrierId']);
                        $rate->setMethodTitle($shippingType['CarrierName']);
                        $rate->setCost($shippingType['Price']);
                        $rate->setPrice($shippingType['Price']);
                        $result->append($rate);
                        $carrierids[] = $shippingType['CarrierId'];
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        
        return [$this->_code=> $this->getConfigData('name')];
    }

    /**
     * Check if carrier has shipping tracking option available
     *
     * @return bool
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    public function getTrackingInfo($trackingNumber)
    {
        $result = $this->_trackFactory->create();
        $tracking = $this->_trackStatusFactory->create();
        $tracking->setCarrier($this->_code);
        $tracking->setTracking($trackingNumber);

        try {
            if ($trackingNumber) {
                $trackingInfo = $this->dispatchdetail->getCollection()->addFieldToFilter("tracking_number", $trackingNumber)->getFirstItem();
                if ($trackingInfo->getTrackingNumber() == $trackingNumber) {
                    $carrierTitle = $trackingInfo->getCarrierName();
                    $trackingUrl = $trackingInfo->getTrackingUrl();
                }
            } else {
                //$shipmentTrack = $this->shipmentReferenceRepository->getShipmentTrack($this->_code, $trackingNumber);
                //$carrierTitle = $shipmentTrack->getTitle() ? $shipmentTrack->getTitle() : $this->getConfigData('title');
                $carrierTitle = $this->getConfigData('title');
                $trackingUrl = '';
            }
            
            $tracking->setCarrierTitle($carrierTitle);
            $tracking->setUrl($trackingUrl);
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());

            $tracking->setCarrierTitle($this->getConfigData('title'));
            $tracking->setUrl('');
        }
        $result->append($tracking);

        return $tracking;
    }
}
