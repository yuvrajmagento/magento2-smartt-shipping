<?php
namespace Smarttshipping\Shipping\Model;

class ApiRequest extends \Magento\Framework\Model\AbstractModel
{
    protected $_customLog;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Smarttshipping\Shipping\Model\Logger $customLog,
        \Smarttshipping\Shipping\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Checkout\Model\Cart $cart
    ) {
          $this->_helper = $helper;
        $this->_customLog = $customLog;
        $this->_productFactory = $productFactory;
        $this->_cart = $cart;
        parent::__construct($context, $registry);
    }
     
    public function getResponse($request)
    {
        if ($this->_helper->isSandbox() == 1) {
            $url = $this->_helper->getSandboxApiurl();
        } else {
            $url = $this->_helper->getApiurl();
        }

        $items = [];
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                $prod = $this->_productFactory->create()->load($item->getProductId());
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        $prod = $this->_productFactory->create()->load($child->getProductId());
                        $temp = [];
                        $weight = $prod->getWeight();
                        $weight = $weight*$item->getQty();
                        if ($weight==0) {
                            $weight = 1;
                        }
                        $producid = $prod->getId();
                           $items[] = ['Quantity'=>round($child->getQty()),
                        'Width'=>round($prod->getShippingWidth()),
                        'Length'=> round($prod->getShippingLength()),
                        'Height'=> round($prod->getShippingHeight()),
                        'Weight'=> $weight,
                        'PackageId'=>6179,
                        'ProductId'=>$producid,
                        'IsStackable'=>true,
                        'IsDangerous'=>false,
                        'ShipmentContainsDGItems'=>[]];

                        /*$temp['Quantity'] = $child->getQty();
                        $temp['Width'] = $prod->getShippingWidth();
                        $temp['Height'] = $prod->getShippingHeight();
                        $temp['Length'] = $prod->getShippingLength();
                        $temp['Weight'] = $child->getWeight();
                        $temp['PackageId'] = 6179;
                        $temp['ProductId'] = $child->getProductId();
                        $temp['IsStackable'] = true;
                        $temp['IsDangerous'] = false;
                        $temp['ShipmentContainsDGItems'] = array();
                        $items[] = $temp;*/
                    }
                } else {
                        $weight = $prod->getWeight();
                        $weight = $weight*$item->getQty();
                    if ($weight==0) {
                        $weight = 1;
                    }
                        $producid =  $prod->getId();
                        $items[] = ['Quantity'=>round($item->getQty()),
                        'Width'=>round($prod->getShippingWidth()),
                        'Length'=> round($prod->getShippingLength()),
                        'Height'=> round($prod->getShippingHeight()),
                        'Weight'=> round($weight),
                        'PackageId'=>6179,
                        'ProductId'=> $producid,
                        'IsStackable'=>true,
                        'IsDangerous'=>false,
                        'ShipmentContainsDGItems'=>[]];
                }
            }
        }
            

        /*    $item = array('Quantity'=>1,
            'Width'=>12,
            'Length'=>12,
            'Height'=>12,
            'Weight'=>12,
            'PackageId'=>6179,
            'ProductId'=>3075,
            'IsStackable'=>true,
            'IsDangerous'=>false,
            'ShipmentContainsDGItems'=>array());   */
            

        $quote = $this->_cart->getQuote();
        $customerId = $this->_cart->getQuote()->getCustomer()->getId();
        $city = $quote->getShippingAddress()->getCity();
        $state = $quote->getShippingAddress()->getRegion();
        $countryId = $quote->getShippingAddress()->getCountryId();
        $name = $quote->getShippingAddress()->getFirstname();
        $postcode = $quote->getShippingAddress()->getPostcode();

        $ShipmentCustomers[] = ['CustomerId'=>$customerId,
                                    'CityId'=>1,
                                    'StateId'=>1,
                                    'countryId'=>1,
                                    'Name'=> $name,
                                    "PostalCode"=> "T5a0c5",
                                    'IsShipFrom'=>false
                                    ];


        /*$ShipmentCustomers[] = array('CustomerId'=>0,
                            'CityId'=>1,
                            'StateId'=>1,
                            'countryId'=>1,
                            'Name'=>'Baljeet',
                            'PostalCode'=>'T5a0c5',
                            'IsShipFrom'=>false
                            );*/
        $paramArray = ['IsImperial'=>false,
                            'ShipmentItems'=>$items,
                            'TermId'=>2,
                            'ShipmentCustomers'=> $ShipmentCustomers,
                            'Services'=>[["ServiceId"=>15, "IsSelected"=>false]],
                            'ShipmentTypeId'=>1,
                            'IsAllServices'=>false,
                            'Fragile'=>false,
                            'SaturdayDelivery'=>false,
                            'NoSignatureRequired'=>false,
                            'ResidentialSignature'=>false,
                            'SpecialHandling'=>false,
                            'IsReturnShipment'=>false,
                            'DeclaredValue'=>0,
                            'DropOff'=>false];


        $this->_customLog->debugLog($paramArray);
        $param = json_encode($paramArray);
        
        $data_get = $this->getApiRequest($url, 'POST', $param);
        
        return json_decode($data_get, true);
        // $this->_customLog->debugLog($response);
    }
    public function getApiRequest($gatewayUrl, $method = 'POST', $requestString)
    {
        
        $apiKey = $this->_helper->getApikey();

        $headers = [
          'APIKEY: '.$apiKey,
          'Content-Type: application/json',
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $gatewayUrl);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestString);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        $data = curl_exec($ch);
        if (!($data)) {
            print_r('Payment Gateway Request Timeout.');
            $this->_customLog->debugLog('Payment Gateway Request Timeout.');
        }
        curl_close($ch);
        unset($ch);

        return $data;
    }
}
