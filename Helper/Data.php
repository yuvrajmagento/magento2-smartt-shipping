<?php

namespace Smarttshipping\Shipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    protected $storeManager;

    protected $_customLog;

    protected $_productFactory;

    protected $_cart;

    protected $_apiurl;

    protected $_apikey;

    protected $_defaultPackageId;

    protected $_defaultstackable;

    protected $_defaultDangerous;

    protected $_configSettings;

    protected $_availableCarrierMethods;

    protected $_smartapi;

    protected $_productRepository;
	

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        \Smarttshipping\Shipping\Model\Logger $customLog,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Smarttshipping\Shipping\Model\AddresssettingFactory $addresssettingFactory,
        \Smarttshipping\Shipping\Model\Config\Source\Options $sourceoptions,
        \Magento\Framework\App\RequestInterface $requestdata,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Smarttshipping\Shipping\Model\DispatchdetailFactory $dispatchdetailFactory,
        \Smarttshipping\Shipping\Model\Api\Smartapi $smartapi,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->storeManager  = $storeManager;
        $this->_customLog = $customLog;
        $this->_productFactory = $productFactory;
        $this->_cart = $cart;
        $this->_countryFactory = $countryFactory;
        $this->_regionFactory = $regionFactory;
        $this->_addresssettingFactory = $addresssettingFactory;
        $this->_sourceoptions = $sourceoptions;
        $this->_requestdata = $requestdata;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_actionFlag = $actionFlag;
        $this->_messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->_smartapi = $smartapi;
        $this->_responseFactory = $responseFactory;
        $this->_dispatchdetailFactory = $dispatchdetailFactory;
        $this->_orderRepository = $orderRepository;
        $this->_shipmentRepository = $shipmentRepository;
        $this->request = $request;
        $this->_productRepository = $productRepository;
        parent::__construct($context);
    }
    
    public function getConfigValue($field, $storeId = null)
    {
        if (!$storeId) {
            if (isset($this->_configSettings[$field])) {
                return $this->_configSettings[$field];
            }
        }
        $this->_configSettings[$field] = $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $this->_configSettings[$field];
    }
    public function getProductBySku($sku){
        try {
            return $this->_productRepository->get($sku);
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    public function getApiurl()
    {
        return $this->getConfigValue('carriers/smartshipping/api_url');
    }

    public function getApikey()
    {
        if ($this->_apikey) {
            return $this->_apikey;
        }
        return $this->getConfigValue('carriers/smartshipping/api_key');
    }

    public function getSandboxApiurl()
    {
        return $this->getConfigValue('carriers/smartshipping/sandbox_url');
    }

    public function getDefaultConfigPackageId()
    {
        if ($this->_defaultPackageId) {
            return $this->_defaultPackageId;
        }
        $this->_defaultPackageId =  $this->getConfigValue('smartship/adminconfig/package_id');
        return $this->_defaultPackageId;
    }


    public function getDefaultConfigStackable()
    {
        if ($this->_defaultstackable) {
            return $this->_defaultstackable;
        }
        $this->_defaultstackable =  $this->getConfigValue('smartship/adminconfig/is_stackable');
        return $this->_defaultstackable;
    }

    public function getDefaultConfigDangerous()
    {
        if ($this->_defaultDangerous) {
            return $this->_defaultDangerous;
        }
        $this->_defaultDangerous =  $this->getConfigValue('smartship/adminconfig/is_dangerous');
        return $this->_defaultDangerous;
    }

    public function isSandbox()
    {
        return $this->getConfigValue('carriers/smartshipping/is_sandbox');
    }

    public function getSmartProductId()
    {
        return $this->getConfigValue('carriers/smartshipping/smart_product_id');
    }

    public function getAllowedMethods()
    {
        return $this->getConfigValue('carriers/smartshipping/allowed_methods');
    }
    public function getIsFilter()
    {
        return $this->getConfigValue('smartship/adminconfig/is_filter');
    }

    public function getApiBaseUrl()
    {
        if ($this->_apiurl) {
            return $this->_apiurl;
        }
        if ($this->isSandbox()) {
            $this->_apiurl = $this->getSandboxApiurl();
        } else {
            $this->_apiurl = $this->getApiurl();
        }
        return $this->_apiurl;
    }

    public function getApiRequest($urlmethod, $method = 'POST', $requestString, $apiKey = null)
    {
        $url = '';
        $url = $this->getApiBaseUrl().$urlmethod;
        if (!$apiKey) {
            $apiKey = $this->getApikey();
        }
        $headers = [
          'APIKEY: '.$apiKey,
          'Content-Type: application/json',
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
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
            $this->_customLog->debugLog('Error smart shipping api request:');
        }
        if (curl_error($ch)) {
            $this->_customLog->debugLog('Error smart shipping api request:'.curl_error($ch));
        }
        curl_close($ch);
        unset($ch);

        return $data;
    }

    public function getCarrierRates($request, $addressdata = false, $params = false, $returnType = 'filter')
    {
        $url = "GetCarrierRates";
        $availbablemethods = [];
        $items = [];
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                $prod = $item->getProduct();
                
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        $prod = $this->_productFactory->create()->load($child->getProductId());
                        $temp = [];
                        $weight = $prod->getWeight();
                        $itemqty = $child->getQty();
                        if (isset($addressdata['city'])) {
                            $itemqty = $child->getQtyOrdered();
                        }
                        $weight = $weight*$itemqty;

                        if ($weight == 0) {
                            $weight = 1;
                        }
                        $packageid = $prod->getPackageId();
                        if ($packageid == '') {
                            $packageid = $this->getDefaultConfigPackageId();
                        }
                        $stackable = $prod->getIsStackable();
                        if ($stackable=='') {
                            $stackable = $this->getDefaultConfigStackable();
                        }
                        $dangerous = $prod->getIsDangerous();
                        if ($dangerous == '') {
                            $dangerous = $this->getDefaultConfigDangerous();
                        }
                        $producid = $this->getSmartProductId();
                        if (isset($params['shipping_height'][$child->getId()]) && !empty($params['shipping_height'][$child->getId()])) {
                            $height = $params['shipping_height'][$child->getId()];
                            $width = $params['shipping_width'][$child->getId()];
                            $length = $params['shipping_length'][$child->getId()];
                            $qty = $params['shipping_qty'][$child->getId()];
                            $weight = $params['shipping_weight'][$child->getId()];
                            $packageid = $params['package_id'][$child->getId()];

                            $items[] = [
                            'Quantity'=>round($qty),
                            'Width'=> (int) round($width),
                            'Length'=> (int) round($length),
                            'Height'=> (int) round($height),
                            'Weight'=> (int) round($weight),
                            'PackageId'=>$packageid,
                            'ProductId'=> $producid,
                            'IsStackable'=> (bool) $stackable,
                            'IsDangerous'=>(bool) $dangerous,
                            'ShipmentContainsDGItems'=>[]];
                        } else {
                            $items[] = ['Quantity'=>round($itemqty),
                            'Width'=> (int) round(($prod->getShippingWidth() != '') ? $prod->getShippingWidth() : '1'),
                            'Length'=> (int) round(($prod->getShippingLength() != '') ? $prod->getShippingLength() : '1'),
                            'Height'=> (int) round(($prod->getShippingHeight() != '') ? $prod->getShippingHeight() : '1'),
                            'Weight'=> (int) round($weight),
                            'PackageId'=> $packageid,
                            'ProductId'=>$producid,
                            'IsStackable'=>(bool) $stackable,
                            'IsDangerous'=>(bool) $dangerous,
                            'ShipmentContainsDGItems'=>[]];
                        }
                    }
                } else {
                    if (!$item->getHasChildren()) {
                        $weight = $prod->getWeight();
                        $itemqty = $item->getQty();
                        if (isset($addressdata['city'])) {
                            $itemqty = $item->getQtyOrdered();
                        }
                        $weight = $weight*$itemqty;
                        if ($weight==0) {
                            $weight = 1;
                        }
                        $producid =  $this->getSmartProductId();
                        $packageid = $prod->getPackageId();
                        if ($packageid == '') {
                            $packageid = $this->getDefaultConfigPackageId();
                        }
                       
                        $stackable = $prod->getIsStackable();
                        if ($stackable=='') {
                            $stackable = $this->getDefaultConfigStackable();
                        }
                        $dangerous = $prod->getIsDangerous();
                        if ($dangerous == '') {
                            $dangerous = $this->getDefaultConfigDangerous();
                        }
                        
                        if (isset($params['shipping_height'][$item->getId()]) && !empty($params['shipping_height'][$item->getId()])) {
                            $height = $params['shipping_height'][$item->getId()];
                            $width = $params['shipping_width'][$item->getId()];
                            $length = $params['shipping_length'][$item->getId()];
                            $qty = $params['shipping_qty'][$item->getId()];
                            $weight = $params['shipping_weight'][$item->getId()];
                            $packageid = $params['package_id'][$item->getId()];

                            $items[] = [
                            'Quantity'=>round($qty),
                            'Width'=> (int) round($width),
                            'Length'=> (int) round($length),
                            'Height'=> (int) round($height),
                            'Weight'=> (int) round($weight),
                            'PackageId'=>$packageid,
                            'ProductId'=> $producid,
                            'IsStackable'=> (bool) $stackable,
                            'IsDangerous'=>(bool) $dangerous,
                            'ShipmentContainsDGItems'=>[]];
                        } else {
                            $items[] = ['Quantity'=>round($itemqty),
                            'Width'=> (int) round(($prod->getShippingWidth() != '') ? $prod->getShippingWidth() : '1'),
                            'Length'=> (int) round(($prod->getShippingLength() != '') ? $prod->getShippingLength() : '1'),
                            'Height'=> (int) round(($prod->getShippingHeight() != '') ? $prod->getShippingHeight() : '1'),
                            'Weight'=> (int) round($weight),
                            'PackageId'=>$packageid,
                            'ProductId'=> $producid,
                            'IsStackable'=> (bool) $stackable,
                            'IsDangerous'=>(bool) $dangerous,
                            'ShipmentContainsDGItems'=>[]];
                        }
                    }
                }
            }
        }
       
        if (isset($addressdata['postcode']) && isset($addressdata['country_id']) && isset($addressdata['city']) && $addressdata) {
            $city = trim($addressdata['city']);
            $region = isset($addressdata['region'])? trim($addressdata['region']) :'';
            $country_id = $addressdata['country_id'];
            $name = $addressdata['name'];
            $postcode = trim($addressdata['postcode']);
            $region_id = isset($addressdata['region_id'])? $addressdata['region_id']:'';
            // $validateresult = $this->validateShipperAddress($country_id, $city, $region, $region_id);
            //  if(!isset($validateresult["smart_country_id"])) {
            //     return array('error'=>'Shipping address Country is not valid.');
            // }
            // if(!isset($validateresult["smart_city_id"])) {
            //     return array('error'=>'Shipping address City is not valid.');
            // }
            // if(!isset($validateresult["smart_region_id"])) {
            //     return array('error'=>'Shipping address State/region is not valid.');
            // }
        } else {
            $quote = $this->_cart->getQuote();
            $customerId = $this->_cart->getQuote()->getCustomer()->getId();
            $city = $quote->getShippingAddress()->getCity();
            $city = trim($city);
            $region = $quote->getShippingAddress()->getRegion();
            $country_id = $quote->getShippingAddress()->getCountryId();
            $name = $quote->getShippingAddress()->getFirstname();
            $postcode = $quote->getShippingAddress()->getPostcode();
            $postcode = trim($postcode);
            $region_id = $quote->getShippingAddress()->getRegionId();
            $validateresult = $this->validateShipperAddress($country_id, $city, $region, $region_id);
        }
        
        //$shipaddress = $this->getShipperAddress($sellerid);
        $cityid = isset($validateresult["smart_city_id"]) ? $validateresult["smart_city_id"] : 0;
        $customerAddress = ['CustomerId'=>0,
        "CityId"=> $cityid,
        "City" => $city,
        "StateId"=> isset($validateresult["smart_region_id"]) ? $validateresult["smart_region_id"] : 1,
        "countryId"=> isset($validateresult["smart_country_id"]) ? $validateresult["smart_country_id"] : 1,
        'Name'=> $name,
        "PostalCode"=> $postcode,
        'IsShipFrom'=>"false"
        ];
        
        $is_imperial = $this->getConfigValue('smartship/adminconfig/is_imperial') ? 'true' : 'false';
        //$is_imperial= false;
        $term_id = $this->getConfigValue('smartship/adminconfig/term_id');
        $shipment_type_id = $this->getConfigValue('smartship/adminconfig/shipment_type_id');
        $is_all_services = $this->getConfigValue('smartship/adminconfig/is_all_services') ? 'true' : 'false';
        $fragile = $this->getConfigValue('smartship/adminconfig/fragile') ? 'true' : 'false';
        $saturday_delivery = $this->getConfigValue('smartship/adminconfig/saturday_delivery') ? 'true' : 'false';
        $no_signature_required =  $this->getConfigValue('smartship/adminconfig/no_signature_required') ? 'true' : 'false';
        $residential_signature = $this->getConfigValue('smartship/adminconfig/residential_signature') ? 'true' : 'false';
        $special_handling = $this->getConfigValue('smartship/adminconfig/special_handling') ? 'true' : 'false';
        $service_id = $this->getConfigValue('smartship/adminconfig/service_id');
        $is_selected = $this->getConfigValue('smartship/adminconfig/is_selected') ? 'true' : 'false';
        $declared_value = $this->getConfigValue('smartship/adminconfig/declared_value');
        if (!$declared_value) {
            $declared_value = 0;
        }
        $drop_off = $this->getConfigValue('smartship/adminconfig/drop_off') ? 'true' : 'false';
        
        /*if($sellerid){
            $ShipmentCustomers = array( $customerAddress, $shipaddress );
        } else { */
            $ShipmentCustomers = [ $customerAddress ];
            //$term_id = 2;
            //$shipment_type_id = 1;
       /* } */
	    $requestedcurrency = $this->getCurrentCurreCode();
		
        $paramArray = ['IsImperial'=> (bool)$is_imperial,
                            'ShipmentItems'=>$items,
                            'TermId'=>$term_id,
							'RequestedCurrency'=>$requestedcurrency,
                            'ShipmentCustomers'=> $ShipmentCustomers,
                            'Services'=>[["ServiceId"=>$service_id, "IsSelected"=> $is_selected ]],
                            'ShipmentTypeId'=>$shipment_type_id,
                            'IsAllServices'=>$is_all_services,
                            'Fragile'=>$fragile,
                            'SaturdayDelivery'=>$saturday_delivery,
                            'NoSignatureRequired'=>$no_signature_required,
                            'ResidentialSignature'=>$residential_signature,
                            'SpecialHandling'=>$special_handling,
                            'IsReturnShipment'=>"false",
                            'DeclaredValue'=>$declared_value,
                            'DropOff'=>$drop_off];

        $param = json_encode($paramArray);
        $this->_customLog->debugLog(json_encode($paramArray));
        
        $data_get = $this->getApiRequest($url, 'POST', $param);
        $jsonresponse = json_decode($data_get, true);
        $this->_customLog->debugLog($jsonresponse);
        

        if ($this->getAllowedMethods()) {
            $enableShipmethods = explode(",", $this->getAllowedMethods());
            $availbablemethods = $enableShipmethods;
        }

        $res = json_decode($data_get, true);
        
        $shippingmethodarray = [];
        if (isset($res['Success']) && $res['Success'] == 1 && isset($res['Carriers'])) {
            foreach ($res['Carriers'] as $key => $methoditem) {
                if ($returnType == 'filter') {
                    if (!empty($availbablemethods)) {
                        if (in_array($methoditem['CarrierId'], $availbablemethods)) {
                            $shippingmethodarray['Carriers'][] = $methoditem;
                        }
                    }
                } else {
                    $shippingmethodarray['Carriers'][] = $methoditem;
                }
            }

            $shippingmethodarray['Success'] = 1;
        } else {
            $shippingmethodarray['Message'] = $res['Message'];
        }

        return $shippingmethodarray;
    }

    public function validateShipperAddress($country_id, $city, $region = null, $region_id = null)
    {
        $temp = [];
        $countryName = '';
        if ($country_id) {
            $country = $this->_countryFactory->create()->loadByCode($country_id);
            $countryName = $country->getName();
        }
        $regionvalue = '';
        if ($region || $region_id) {
            $regionvalue = $region;
            if ($region_id && $region=='') {
                $regionmodel = $this->_regionFactory->create();
                $regionmodel->load($region_id);
                $regionvalue = $regionmodel->getName();
            }
        }
        $temp = $this->_smartapi->validateAddress($countryName, $city, $regionvalue);
        return $temp;
    }

    public function getTelephoneNumber($telephone)
    {
        $telephoneNumber = preg_replace('/\D/', '', $telephone);
        if (strlen($telephoneNumber) == 10) {
            return $telephoneNumber;
        } else {
            return substr($telephoneNumber, -10);
        }
    }

    public function getQuotes($order, $dimentionData)
    {
       
        $items = [];
        $url = "GetCarrierRates";
        $availbablemethods = [];
        
        if ($dimentionData) {
            $producid =  $this->getSmartProductId();
            foreach ($dimentionData['package_id'] as $key => $ditem) {
                if ($ditem == '') {
                    $ditem = $this->getDefaultConfigPackageId();
                }
                if (!empty($dimentionData['is_stackable'][$key])) {
                    $IsStackable = $dimentionData['is_stackable'][$key];
                } else {
                    $IsStackable = 0;
                }
            
                $temp = [
                  "Quantity"=> round($dimentionData['qty'][$key]),
                  "Width"   => (int) round(($dimentionData['width'][$key] != '') ? $dimentionData['width'][$key] : '1'),
                  "Length" => (int) round(($dimentionData['length'][$key] != '') ? $dimentionData['length'][$key] : '1'),
                  "Height" => (int) round(($dimentionData['height'][$key] != '') ? $dimentionData['height'][$key] : '1'),
                  "Weight" => (int) round(($dimentionData['weight'][$key] != '') ? $dimentionData['weight'][$key] : '1'),
                  "PackageId"=> $ditem,
                  "ProductId"=> $producid,
                  "IsStackable"=> (bool) $IsStackable,
                  "IsDangerous"=> false,
                  "ShipmentContainsDGItems"=>[]
                  ];
                $items[] = $temp;
            }
        }

        $shippingaddress = $order->getShippingAddress();
        $country_id = $shippingaddress->getCountryId();
        $region = $shippingaddress->getRegion();
        if ($region) {
            $region = trim($region);
        }
        $city  = $shippingaddress->getCity();
        if ($city) {
            $city = trim($city);
        }
        $region_id = $shippingaddress->getRegionId();
        $validateresult = $this->validateShipperAddress($country_id, $city, $region, $region_id);
        
        
        $address = $shippingaddress->getStreet();
        $newaddress = '';
        if ($address) {
            foreach ($address as $key => $value) {
                $newaddress .= $value;
            }
        }
        $transitdays = 1;
        //$shipaddress = $this->getShipperAddress($sellerid, $inbound);
        $cityid = isset($validateresult["smart_city_id"]) ? $validateresult["smart_city_id"] : 0;
        $customerAddress = [
              "CustomerId"=>0,
              "CityId"=> $cityid,
              "City"=> $city,
              "StateId"=> isset($validateresult["smart_region_id"]) ? $validateresult["smart_region_id"] : 1,
              "countryId"=> isset($validateresult["smart_country_id"]) ? $validateresult["smart_country_id"] : 1,
              "Name"=> $shippingaddress->getFirstname()." ".$shippingaddress->getLastname(),
              "Address"=> $newaddress,
              "PostalCode"=>$shippingaddress->getPostcode(),
              "Email"=> $shippingaddress->getEmail(),
              "Phone"=>$this->getTelephoneNumber($shippingaddress->getTelephone()),
              "IsShipFrom"=> false
              ];
        
        $customerData = $this->checkCustomerAddress($customerAddress, $shippingaddress->getEmail());
        $customerAddress['CustomerId'] = $customerData;
        $getUrl = 'CreateDispatch';

        $is_imperial = $this->getConfigValue('smartship/adminconfig/is_imperial') ? 'true' : 'false';
        //$is_imperial = false;
        $term_id = $this->getConfigValue('smartship/adminconfig/term_id');
        $shipment_type_id = $this->getConfigValue('smartship/adminconfig/shipment_type_id');
        $is_all_services = $this->getConfigValue('smartship/adminconfig/is_all_services') ? 'true' : 'false';
        $fragile = $this->getConfigValue('smartship/adminconfig/fragile') ? 'true' : 'false';
        $saturday_delivery = $this->getConfigValue('smartship/adminconfig/saturday_delivery') ? 'true' : 'false';
        $no_signature_required =  $this->getConfigValue('smartship/adminconfig/no_signature_required') ? 'true' : 'false';
        $residential_signature = $this->getConfigValue('smartship/adminconfig/residential_signature') ? 'true' : 'false';
        $special_handling = $this->getConfigValue('smartship/adminconfig/special_handling') ? 'true' : 'false';
        $service_id = $this->getConfigValue('smartship/adminconfig/service_id');
        $is_selected = $this->getConfigValue('smartship/adminconfig/is_selected') ? 'true' : 'false';
        $declared_value = $this->getConfigValue('smartship/adminconfig/declared_value');

        $no_signature_required = (!empty($dimentionData['signature'])) ? true : $no_signature_required;
        $residential_signature = (!empty($dimentionData['residential_signature'])) ? true : $residential_signature;
        $saturday_delivery = (!empty($dimentionData['saturday_delivery'])) ? true : $saturday_delivery;

        if (!$declared_value) {
            $declared_value = 0;
        }
        $drop_off = $this->getConfigValue('smartship/adminconfig/drop_off') ? 'true' : 'false';

        $ShipmentCustomers = [ $customerAddress ];
        //$term_id = 2;
        //$shipment_type_id = 1;
       /* } */
		
		$requestedcurrency = $this->getCurrentCurreCode();
		
        $paramArray = ['IsImperial'=> (bool)$is_imperial,
                            'ShipmentItems'=>$items,
                            'TermId'=>$term_id,
							'RequestedCurrency'=> $requestedcurrency,
                            'ShipmentCustomers'=> $ShipmentCustomers,
                            'Services'=>[["ServiceId"=>$service_id, "IsSelected"=> $is_selected ]],
                            'ShipmentTypeId'=>$shipment_type_id,
                            'IsAllServices'=>$is_all_services,
                            'Fragile'=>$fragile,
                            'SaturdayDelivery'=>$saturday_delivery,
                            'NoSignatureRequired'=>$no_signature_required,
                            'ResidentialSignature'=>$residential_signature,
                            'SpecialHandling'=>$special_handling,
                            'IsReturnShipment'=>"false",
                            'DeclaredValue'=>$declared_value,
                            'DropOff'=>$drop_off];
        
        $param = json_encode($paramArray);

        //$this->_customLog->debugLog(json_encode($paramArray));
        
        $data_get = $this->getApiRequest($url, 'POST', $param);
        $jsonresponse = json_decode($data_get, true);
        $this->_customLog->debugLog($jsonresponse);
        

        if ($this->getAllowedMethods()) {
            $enableShipmethods = explode(",", $this->getAllowedMethods());
            $availbablemethods = $enableShipmethods;
        }

        $res = json_decode($data_get, true);

        $shippingmethodarray = [];
        if (isset($res['Success']) && $res['Success'] == 1 && isset($res['Carriers'])) {
            $dispatchcollection = $this->_dispatchdetailFactory->create()->getCollection()->addFieldToFilter('order_id', $order->getId());
            $dispatchdetail = $this->_dispatchdetailFactory->create();
            if ($dispatchcollection->count() > 0) {
                $dispatchid = $dispatchcollection->getFirstItem()->getId();
                $dispatchdetail->load($dispatchid);
            }
            $dispatchdetail->setCustomerId($order->getCustomerId());
            $dispatchdetail->setOrderId($order->getId());
            $dispatchdetail->setMagentoShipmentId(0);
            $dispatchdetail->setRequoteData($param);
            $dispatchdetail->setSignature($no_signature_required);
            $dispatchdetail->setResidentialSignature($residential_signature);
            $dispatchdetail->setSaturdayDelivery($saturday_delivery);
            $dispatchdetail->save();

            $Type = $this->getIsFilter();
            if ($Type) {
                 $returnType = 'filter';
            } else {
                $returnType = 'all';
            }
            foreach ($res['Carriers'] as $key => $methoditem) {
                if ($returnType == 'filter') {
                    if (!empty($availbablemethods)) {
                        if (in_array($methoditem['CarrierId'], $availbablemethods)) {
                            $shippingmethodarray['Carriers'][] = $methoditem;
                        }
                    }
                } else {
                    $shippingmethodarray['Carriers'][] = $methoditem;
                }
            }

            $shippingmethodarray['Success'] = 1;
        } else {
            $shippingmethodarray['Message'] = $res['Message'];
        }
        return $shippingmethodarray;
    }

    public function getDefautOriginCountryId(){
        return $this->getConfigValue('shipping/origin/country_id');
    }

    public function createDispatch($order, $shipment = null, $inbound = false)
    {
        
         
        $tracking = $this->_requestdata->getPost("tracking");

        $trackingnumber = '';
        if (isset($tracking[1]['number'])) {
            $trackingnumber = $tracking[1]['number'];
        }
        if ($trackingnumber == '') {
            $trackingnumber = $this->_requestdata->getPost("tracking_id");
        }
        
        
        if ($this->_requestdata->getPost("pickdatetime")){
            $dateTime = date("Y-m-d H:i:s", strtotime($this->_requestdata->getPost("pickdatetime")));
            $_dispatch_date = date("Y-m-d", strtotime($dateTime));
            $_dispatch_time = date("H:i:s", strtotime($dateTime));
        }else{
            $_dispatch_date = "2020-12-15 08:30:00";
            $_dispatch_time =  "2020-12-15 09:40:21";
        }
        
        $isinternational = $this->_requestdata->getPost("is_international");
        $shippingaddresscountryid = $this->_requestdata->getPost("shipping_address_country_id");
        $importexporttype = $this->_requestdata->getPost("importexporttype");
        $customsbrokername = $this->_requestdata->getPost("customsbrokername");
        $importerofrecordname = $this->_requestdata->getPost("importerofrecordname");
        $ordercurrencycode =  $order->getOrderCurrencyCode();
        $InternationalShipmentItems=array();
        if($isinternational == 'yes'){
            foreach($order->getAllVisibleItems() as $items){
                $PackageName = $this->getDefaultConfigPackageId();
                $_product = $this->getProductBySku($items->getSku());
                $manufacturer = $this->getDefautOriginCountryId();
                if($_product){
                   $PackageName = $_product->getResource()->getAttribute('package_id')->getFrontend()->getValue($_product);
                   if($PackageName == null){
                        $PackageName = $this->getDefaultConfigPackageId();
                   }
               }else{
                  $PackageName = $this->getDefaultConfigPackageId(); 
               }

                $InternationalShipmentItems[]=['PackageName'=>$PackageName,'ProductName'=>$items->getName(),'Quantity'=>(int)$items->getQtyOrdered(),'Price'=>$items->getPrice(),'TariffCode'=>$shippingaddresscountryid,'Currency'=>$ordercurrencycode,'CountryOfManufacture'=>$manufacturer];
            }
        }

        /* create Dispatch if requote created for this order start here */

        $dispatchcollection = $this->_dispatchdetailFactory->create()->getCollection()->addFieldToFilter('order_id', $order->getId())->addFieldToFilter('isrequote', 1);
            $dispatchdetail = $this->_dispatchdetailFactory->create();
            
        if ($dispatchcollection->count() > 0) {
            $dispatchid = $dispatchcollection->getFirstItem()->getId();
            $dispatchdetail->load($dispatchid);
            
            $getRequoteCarrierData = [];
            if ($dispatchdetail->getRequoteEstimate()) {
                $qtestimateShipping = json_decode($dispatchdetail->getRequoteEstimate(), true);
                if (is_array($qtestimateShipping)) {
                    foreach ($qtestimateShipping as $key => $sitem) {
                        if ($dispatchdetail->getRequoteCarrierid() == $sitem['CarrierId']) {
                            $getRequoteCarrierData = [
                                'carrierId' => $dispatchdetail->getRequoteCarrierid(),
                                'CarrierName' => $sitem['CarrierName'],
                                'ServiceName' => $sitem['ServiceName'],
                                'TransitDays' => $sitem['TransitDays'],
                                'Price' => round($sitem['Price'], 2),
                                'APIRatesEnabled' => $sitem['APIDocumentEnabled'],
                                'APIDocumentEnabled' => $sitem['APIDocumentEnabled'],
                                'APIDispatchEnabled' => $sitem['APIDispatchEnabled'],
                                'IsPriceInUsd' => $sitem['IsPriceInUsd']
                            ];
                            break;
                        }
                    }
                }
            }
            if(!empty($trackingnumber)){
                $trackingnumber = $trackingnumber;
            } else {
                $trackingnumber = $order->getIncrementId();
            }
            $requoteData = json_decode($dispatchdetail->getRequoteData());
            
            $requoteData->ReferenceNumber = $trackingnumber;
            $requoteData->PO = $order->getIncrementId();
            $requoteData->APICarrierAccountNumber = "";
            $requoteData->ShipmentDate = $_dispatch_date;
            $requoteData->ShipmentTime = $_dispatch_time;
            $requoteData->SelectedCarrier = $getRequoteCarrierData;
            

            // 'SaturdayDelivery'=>$saturday_delivery,
            // 'NoSignatureRequired'=>$no_signature_required,
            // 'ResidentialSignature'=>$residential_signature,

            $paramArray = $requoteData;
            
            $param = json_encode($paramArray, true);

        /* create Dispatch if requote created for this order end here */
        } else {
            $itemspost = $this->_requestdata->getPost("shipment");
            $items = [];
        
            if (isset($shipment) && !empty($shipment) && $shipment->getId()) {
                if (is_array($shipment->getAllTracks()) && !empty($shipment->getAllTracks())) {
                    foreach ($shipment->getAllTracks() as $tracknum) {
                        $trackingnumber =$tracknum->getNumber();
                        break;
                    }
                }

                foreach ($shipment->getAllItems() as $key => $sItem) {
                    $prodfactory = $this->_productFactory->create();
                    $prodfactory->load($sItem->getProductId());
                    $orderItem = $order->getItemById($sItem->getOrderItemId());
                    
                    $producid =  $this->getSmartProductId();
                    $packageid = $orderItem->getPackageId();
                    if ($packageid == '') {
                        $packageid = $this->getDefaultConfigPackageId();
                    }

                    $weight = $orderItem->getShippingWeight();
                    if ($weight == 0) {
                        $weight = 1;
                    }
                    
                    $stackable = $prodfactory->getIsStackable();
                    if ($stackable=='') {
                        $stackable = $this->getDefaultConfigStackable();
                    }
                    $dangerous = $prodfactory->getIsDangerous();
                    if ($dangerous == '') {
                        $dangerous = $this->getDefaultConfigDangerous();
                    }

                    $temp = [
                    "Quantity"=> round($orderItem->getRecalculateQty()),
                    "Width"   => (int) round(($orderItem->getShippingWidth() != '') ? $orderItem->getShippingWidth() : '1'),
                    "Length" => (int) round(($orderItem->getShippingLength() != '') ? $orderItem->getShippingLength() : '1'),
                    "Height" => (int) round(($orderItem->getShippingHeight() != '') ? $orderItem->getShippingHeight() : '1'),
                    "Weight" => (int) round($weight),
                    "PackageId" => $packageid,
                    "ProductId" => $producid,
                    "IsStackable" => (bool) $stackable,
                    "IsDangerous" => (bool)$dangerous,
                    "ShipmentContainsDGItems"=>[]
                      ];
                    $items[] = $temp;
                }
            } else {
                foreach ($order->getAllVisibleItems() as $key => $oitem) {
                    if (isset($itemspost["items"][$oitem->getId()]) && $itemspost["items"][$oitem->getId()]) {
                        $prodfactory = $this->_productFactory->create();
                        $prodfactory->load($oitem->getProductId());
                        
                        $weight = $prodfactory->getWeight();
                        $weight = $weight*$itemspost["items"][$oitem->getId()];
                        if ($weight == 0) {
                            $weight = 1;
                        }
                        $producid =  $this->getSmartProductId();
                        $packageid = $prodfactory->getPackageId();
                        if ($packageid == '') {
                            $packageid = $this->getDefaultConfigPackageId();
                        }
                        
                        $stackable = $prodfactory->getIsStackable();
                        if ($stackable=='') {
                            $stackable = $this->getDefaultConfigStackable();
                        }
                        $dangerous = $prodfactory->getIsDangerous();
                        if ($dangerous == '') {
                            $dangerous = $this->getDefaultConfigDangerous();
                        }

                        $temp = [
                          "Quantity"=> round($itemspost["items"][$oitem->getId()]),
                          "Width"   => (int) round(($prodfactory->getShippingWidth() != '') ? $prodfactory->getShippingWidth() : '1'),
                          "Length" => (int) round(($prodfactory->getShippingLength() != '') ? $prodfactory->getShippingLength() : '1'),
                          "Height" => (int) round(($prodfactory->getShippingHeight() != '') ? $prodfactory->getShippingHeight() : '1'),
                          "Weight" => (int) round($weight),
                          "PackageId"=>$packageid,
                          "ProductId"=>$producid,
                          "IsStackable"=>(bool)$stackable,
                          "IsDangerous"=>(bool)$dangerous,
                          "ShipmentContainsDGItems"=>[]
                          ];
                        $items[] = $temp;
                    } else {
                        $prodfactory = $this->_productFactory->create();
                        $prodfactory->load($oitem->getProductId());
                        
                        $weight = $prodfactory->getWeight();
                        $weight = $weight* $oitem->getQtyOrdered();
                            
                        $producid =  $this->getSmartProductId();
                        $packageid = $prodfactory->getPackageId();
                        if ($packageid == '') {
                            $packageid = $this->getDefaultConfigPackageId();
                        }
                       
                        $stackable = $prodfactory->getIsStackable();
                        if ($stackable=='') {
                            $stackable = $this->getDefaultConfigStackable();
                        }
                        $dangerous = $prodfactory->getIsDangerous();
                        if ($dangerous == '') {
                            $dangerous = $this->getDefaultConfigDangerous();
                        }

                        $temp = [
                          "Quantity"=> round($oitem->getQtyOrdered()),
                          "Width"   => (int) round(($prodfactory->getShippingWidth() != '') ? $prodfactory->getShippingWidth() : '1'),
                          "Length" => (int) round(($prodfactory->getShippingLength() != '') ? $prodfactory->getShippingLength() : '1'),
                          "Height" => (int) round(($prodfactory->getShippingHeight() != '') ? $prodfactory->getShippingHeight() : '1'),
                          "Weight" => (int) round($weight),
                          "PackageId" => $packageid,
                          "ProductId" => $producid,
                          "IsStackable" => (bool)$stackable,
                          "IsDangerous" => (bool)$dangerous,
                          "ShipmentContainsDGItems"=>[]
                          ];
                        $items[] = $temp;
                    }
                }
            }

            $shippingaddress = $order->getShippingAddress();
            $country_id = $shippingaddress->getCountryId();
            $region = $shippingaddress->getRegion();
            if ($region) {
                $region = trim($region);
            }
            $city  = $shippingaddress->getCity();
            if ($city) {
                $city = trim($city);
            }
            $region_id = $shippingaddress->getRegionId();
            $validateresult = $this->validateShipperAddress($country_id, $city, $region, $region_id);
            
			$requestedcurrency = $this->getCurrentCurreCode();
            
            $address = $shippingaddress->getStreet();
            $newaddress = '';
            if ($address) {
                foreach ($address as $key => $value) {
                    $newaddress .= $value;
                }
            }
            $transitdays = 1;
            //$shipaddress = $this->getShipperAddress($sellerid, $inbound);
            $cityid = isset($validateresult["smart_city_id"]) ? $validateresult["smart_city_id"] : 0;
            $customerAddress = [
                  "CustomerId"=>0,
                  "CityId"=> $cityid,
                  "City"=> $city,
                  "StateId"=> isset($validateresult["smart_region_id"]) ? $validateresult["smart_region_id"] : 1,
                  "countryId"=> isset($validateresult["smart_country_id"]) ? $validateresult["smart_country_id"] : 1,
                  "Name"=> $shippingaddress->getFirstname()." ".$shippingaddress->getLastname(),
                  "Address"=> $newaddress,
                  "PostalCode"=>$shippingaddress->getPostcode(),
                  "Email"=> $shippingaddress->getEmail(),
                  "Phone"=>$this->getTelephoneNumber($shippingaddress->getTelephone()),
                  "IsShipFrom"=> $inbound ? true : false
                  ];
            
            $customerData = $this->checkCustomerAddress($customerAddress, $shippingaddress->getEmail());
            $customerAddress['CustomerId'] = $customerData;
            

            $is_imperial = $this->getConfigValue('smartship/adminconfig/is_imperial') ? 'true' : 'false';
            //$is_imperial = false;
            $term_id = $this->getConfigValue('smartship/adminconfig/term_id');
            $shipment_type_id = $this->getConfigValue('smartship/adminconfig/shipment_type_id');
            $is_all_services = $this->getConfigValue('smartship/adminconfig/is_all_services') ? 'true' : 'false';
            $fragile = $this->getConfigValue('smartship/adminconfig/fragile') ? 'true' : 'false';
            $saturday_delivery = $this->getConfigValue('smartship/adminconfig/saturday_delivery') ? 'true' : 'false';
            $no_signature_required =  $this->getConfigValue('smartship/adminconfig/no_signature_required') ? 'true' : 'false';
            $residential_signature = $this->getConfigValue('smartship/adminconfig/residential_signature') ? 'true' : 'false';
            $special_handling = $this->getConfigValue('smartship/adminconfig/special_handling') ? 'true' : 'false';
            $service_id = $this->getConfigValue('smartship/adminconfig/service_id');
            $is_selected = $this->getConfigValue('smartship/adminconfig/is_selected') ? 'true' : 'false';
            $declared_value = $this->getConfigValue('smartship/adminconfig/declared_value');
            if (!$declared_value) {
                $declared_value = 0;
            }
            $drop_off = $this->getConfigValue('smartship/adminconfig/drop_off') ? 'true' : 'false';

            $shipdesc = $order->getShippingDescription();
            $pos = strpos($shipdesc, '-');
            if ($pos!==false) {
                $shipdesc = substr($shipdesc, $pos+1);
            }
            $shipdesc = trim($shipdesc);
            $shipamount = round($order->getShippingAmount(), 2);
            $shipmethod = $order->getShippingMethod();
            $shipmethod = str_replace("smartshipping_", "", $shipmethod);

            /*if($sellerid){
                $customers = array( $customerAddress, $shipaddress );
            } else {*/
                $customers = [ $customerAddress ];
                //$term_id = 2;
                //$shipment_type_id = 1;
            /*}*/
           
            $estimatemethods = $order->getData('smart_shipping_carrier_estimate');
            $estimateShipping = '';
            $CarrierName = '';
            $APIRatesEnabled = true;
            $APIDocumentEnabled = true;
            $APIDispatchEnabled = true;
            $IsPriceInUsd = false;
            if ($estimatemethods) {
                $estimateShipping = json_decode($estimatemethods, true);
                if (is_array($estimateShipping)) {
                    foreach ($estimateShipping as $key => $sitem) {
                        if ($shipmethod==$sitem['CarrierId']) {
                            $CarrierName = $sitem['CarrierName'];
                            $transitdays = $sitem['TransitDays'];
                            $shipdesc = $sitem['ServiceName'];
                            $APIRatesEnabled = $sitem['APIRatesEnabled'];
                            $APIDocumentEnabled = $sitem['APIDocumentEnabled'];
                            $APIDispatchEnabled = $sitem['APIDispatchEnabled'];
                            $IsPriceInUsd = $sitem['IsPriceInUsd'];
                            break;
                        }
                    }
                }
            }
            
            if(!empty($trackingnumber)){
                $trackingnumber = $trackingnumber;
            } else {
                $trackingnumber = $order->getIncrementId();
            }
            if($isinternational == 'yes'){
                $paramArray=[
                    'ImportExportType'=>$importexporttype,
                    'CustomsBrokerName'=>$customsbrokername,
                    'ImporterOfRecordName'=>$importerofrecordname,
                    'InternationalShipmentItems'=>$InternationalShipmentItems,
                    'IsImperial'=> (bool)$is_imperial,  
                    'ShipmentItems'=>$items,
                    'TermId'=>$term_id,
					'RequestedCurrency'=>$requestedcurrency,
                    'ShipmentCustomers'=> $customers,
                    'Services'=>[["ServiceId"=>$service_id, "IsSelected"=>$is_selected]],
                    'ShipmentTypeId'=>$shipment_type_id,
                    'IsAllServices'=>$is_all_services,
                    'Fragile'=>$fragile,
                    'SaturdayDelivery'=>$saturday_delivery,
                    'NoSignatureRequired'=>$no_signature_required,
                    'ResidentialSignature'=>$residential_signature,
                    'SpecialHandling'=>$special_handling,
                    'IsReturnShipment'=>"false",
                    'DeclaredValue'=>$declared_value,
                    'DropOff'=>$drop_off,
                    "ReferenceNumber"=> $trackingnumber,
                    "PONumber"=>$order->getIncrementId(),
                    /* "PONumber"=>$order->getIncrementId(), */
                    "APICarrierAccountNumber"=>"",
                    "ShipmentDate"=>$_dispatch_date,
                    "ShipmentTime"=>$_dispatch_time,
                    "SelectedCarrier"=> [
                        "carrierId"=>$shipmethod,
                        "CarrierName"=>$CarrierName,
                        "ServiceName"=>trim($shipdesc),
                        "TransitDays"=>$transitdays,
                        "Price"=>$shipamount,
                        "APIRatesEnabled"=>$APIRatesEnabled,
                        "APIDocumentEnabled"=>$APIDocumentEnabled,
                        "APIDispatchEnabled"=>$APIDispatchEnabled,
                        "IsPriceInUsd"=>$IsPriceInUsd
                    ]
                ];
            }else{
                $paramArray=[
                    'IsImperial'=> (bool)$is_imperial,  
                    'ShipmentItems'=>$items,
                    'TermId'=>$term_id,
					'RequestedCurrency'=>$requestedcurrency,
                    'ShipmentCustomers'=> $customers,
                    'Services'=>[["ServiceId"=>$service_id, "IsSelected"=>$is_selected]],
                    'ShipmentTypeId'=>$shipment_type_id,
                    'IsAllServices'=>$is_all_services,
                    'Fragile'=>$fragile,
                    'SaturdayDelivery'=>$saturday_delivery,
                    'NoSignatureRequired'=>$no_signature_required,
                    'ResidentialSignature'=>$residential_signature,
                    'SpecialHandling'=>$special_handling,
                    'IsReturnShipment'=>"false",
                    'DeclaredValue'=>$declared_value,
                    'DropOff'=>$drop_off,
                    "ReferenceNumber"=> $trackingnumber,
                    "PONumber"=>$order->getIncrementId(),
                    /* "PONumber"=>$order->getIncrementId(), */
                    "APICarrierAccountNumber"=>"",
                    "ShipmentDate"=>$_dispatch_date,
                    "ShipmentTime"=>$_dispatch_time,
                    "SelectedCarrier"=> [
                        "carrierId"=>$shipmethod,
                        "CarrierName"=>$CarrierName,
                        "ServiceName"=>trim($shipdesc),
                        "TransitDays"=>$transitdays,
                        "Price"=>$shipamount,
                        "APIRatesEnabled"=>$APIRatesEnabled,
                        "APIDocumentEnabled"=>$APIDocumentEnabled,
                        "APIDispatchEnabled"=>$APIDispatchEnabled,
                        "IsPriceInUsd"=>$IsPriceInUsd
                    ]
                ];
            }
            if ($order->getSmartShipmentId() != '') {
                $paramArray['ShipmentId'] = $order->getSmartShipmentId();
            }
        }

        $param = json_encode($paramArray, true);
        $getUrl = 'CreateDispatch';
        $data_get = $this->getApiRequest($getUrl, 'POST', $param);
        $result = json_decode($data_get, true);
       
        if (isset($result['Success']) && $result['Success']==1 && isset($result['ShipmentResponse']['ShipmentId'])){
            try {
                $dispatchdetail = $this->_dispatchdetailFactory->create();
               
                if (isset($shipment) && $shipment->getId() && !$inbound) {
                    $dispatchdetail->load($shipment->getId(), "magento_shipment_id");
                }
                $dispatchcollection = $this->_dispatchdetailFactory->create()->getCollection()->addFieldToFilter('order_id', $order->getId())->addFieldToFilter('isrequote', 1)->addFieldToFilter('magento_shipment_id', 0);
                if ($dispatchcollection->count() > 0) {
                    $dispatchid = $dispatchcollection->getFirstItem()->getId();
                    $dispatchdetail->load($dispatchid);
                }
                if ($inbound) {
                    $dispatchcollection = $this->_dispatchdetailFactory->create()->getCollection()->addFieldToFilter('magento_shipment_id', $shipment->getId())->addFieldToFilter('shiptype', "return");
                    $dispatchdetail = $this->_dispatchdetailFactory->create();
                    if ($dispatchcollection->count() > 0) {
                        $dispatchid = $dispatchcollection->getFirstItem()->getId();
                        $dispatchdetail->load($dispatchid);
                    }
                    $dispatchdetail->setShiptype("return");
                } else {
                    $dispatchdetail->setShiptype("shipment");
                }
                if (isset($shipment) && $shipment->getId()) {
                    $dispatchdetail->setMagentoShipmentId($shipment->getId());
                } else {
                    $dispatchdetail->setMagentoShipmentId(0);
                }
                  
                $dispatchdetail->setCustomerId($order->getCustomerId());
                $dispatchdetail->setOrderId($order->getId());
                $dispatchdetail->setStatus("pending");
                $dispatchdetail->setIsCancelled(0);
                $dispatchdetail->setAccountId($result['ShipmentResponse']['AccountId']);
                $dispatchdetail->setShipmentId($result['ShipmentResponse']['ShipmentId']);
                $dispatchdetail->setShipperName($result['ShipmentResponse']['ShipperName']);
                $dispatchdetail->setCarrierName($result['ShipmentResponse']['CarrierName']);
                $dispatchdetail->setTrackingNumber($result['ShipmentResponse']['TrackingNumber']);
                $dispatchdetail->setTrackingUrl($result['ShipmentResponse']['TrackingUrl']);
                $dispatchdetail->setTransitDays($result['ShipmentResponse']['TransitDays']);
                $dispatchdetail->setQuantity($result['ShipmentResponse']['Quantity']);
                $dispatchdetail->setShipmentWeight($result['ShipmentResponse']['ShipmentWeight']);
                $dispatchdetail->setSmarttBlNumber($result['ShipmentResponse']['SmarttBLNumber']);
                $dispatchdetail->setReferenceNumber($result['ShipmentResponse']['ReferenceNumber']);
                /*$dispatchdetail->setPo($result['ShipmentResponse']['PO']); */
                $dispatchdetail->setPONumber($result['ShipmentResponse']['PO']);
                $dispatchdetail->setShipmentDate($result['ShipmentResponse']['ShipmentDate']);
                $dispatchdetail->setBolPath($result['ShipmentResponse']['BolPath']);
                $dispatchdetail->setShipmentGuid($result['ShipmentResponse']['ShipmentGuid']);
                $dispatchdetail->setPickUpNumber($result['ShipmentResponse']['PickUpNumber']);
                $dispatchdetail->setShipmentCustomerId($result['ShipmentResponse']['ShipmentCustomerId']);
                $dispatchdetail->setShipmentShipperId($result['ShipmentResponse']['ShipmentShipperId']);
                $dispatchdetail->setDispatchDate($_dispatch_date);
                $dispatchdetail->setDispatchTime($_dispatch_time);
                $dispatchdetail->save();
                
                return ["status"=>"success","message"=>__("Smart shippping dispatch created successfully.")];
            } catch (\Exception $e) {
                $this->_customLog->debugLog("Error appeared while create dispatch. ".$e->getMessage());
                return ["status"=>"error","message"=>"Error appeared while create dispatch. ".$e->getMessage()];
            }
        } else {
            $error = '';
            if (isset($result['Message'])) {
                $error = $result['Message'];
            }
            $this->_customLog->debugLog($result);
            return ["status"=>"error","message"=>"Error appeared while create dispatch. ".$error];
        }
    }

    public function checkCustomerAddress($customerDetail, $email)
    {
        $getUrl = "GetAllCustomers?search=".strtolower($email);
        $data_get = $this->getApiRequest($getUrl, 'GET', null);
        $result = json_decode($data_get, true);
        $finale = $this->checkAddessValue($result, $customerDetail);
        return $finale;
    }

    public function checkAddessValue($result, $customerDetail)
    {
        if (isset($result['Success']) && count($result['Customers']) > 0) {
            foreach ($result['Customers'] as $key => $value) {
                $value = array_map('strtolower', $value);

                $addressValue = ['CustomerName'=>$customerDetail['Name'],
                                    'CityID'=>$customerDetail['CityId'],
                                    'StateID'=>$customerDetail['StateId'],
                                    'CountryID'=>$customerDetail['countryId'],
                                    'EmailID'=>$customerDetail['Email'],
                                    'Address'=>$customerDetail['Address'],
                                    'ZipCode'=>$customerDetail['PostalCode'],
                                    ];
                $addressValue = array_map('strtolower', $addressValue);
                $result = array_diff($addressValue, $value);
                if (isset($result['CustomerName']) === false && isset($result['CityID']) === false && isset($result['StateID']) === false && isset($result['EmailID']) === false) {
                    return $value['CustomerID'];
                }
            }
        }
        return 0;
    }

   
    public function getPdfUrl($id, $key = 'order_id')
    {
        $detailCollection = $this->_dispatchdetailFactory->create()->getCollection()->addFieldToFilter($key, $id)->addFieldToFilter("shiptype", "shipment");
        $detailCollection->getSelect()->order("id asc");
        if ($detailCollection->count() > 0) {
            return $detailCollection->getFirstItem()->getBolPath();
        }
    }

    public function getPdfReturnUrl($id, $key = 'order_id')
    {
        $detailCollection = $this->_dispatchdetailFactory->create()->getCollection()->addFieldToFilter($key, $id)->addFieldToFilter("shiptype", "return");
        $detailCollection->getSelect()->order("id desc");
        if ($detailCollection->count() > 0) {
            return $detailCollection->getFirstItem()->getBolPath();
        }
    }

    public function cancelShipment($order, $shipment = null, $type = null, $reason = null)
    {
        if (isset($shipment) && !empty($shipment) && $shipment->getId()) {
            $detailCollection = $this->_dispatchdetailFactory->create()->getCollection()->addFieldToFilter("magento_shipment_id", $shipment->getId());
            if ($type=='return') {
                $detailCollection->addFieldToFilter("shiptype", "return");
                $detailCollection->getSelect()->order("id desc");
            } else {
                $detailCollection->getSelect()->order("id asc");
            }
            $shipid = '';
            if ($detailCollection->count() > 0) {
                $firstItem = $detailCollection->getFirstItem();
                $shipid = $firstItem->getShipmentId();
                if ($shipid) {
                    $url = "CancelShipment";
                    $paramArray = ['ShipmentId'=>$shipid, 'CancellationReasonNote'=> $reason];
                    $param = json_encode($paramArray);
                    $data_get = $this->getApiRequest($url, 'POST', $param);
                    $resultresponse =  json_decode($data_get, true);
                    if (isset($resultresponse['Message']) && isset($resultresponse['Success']) && $resultresponse['Success']) {
                        $firstItem->setIsCancelled(1);
                        $firstItem->save();
                        return $resultresponse;
                    } else {
                        return $resultresponse;
                    }
                }
            }
        }
        return false;
    }

    public function getIsCancelled($shipid)
    {
        $detailCollection = $this->_dispatchdetailFactory->create()->getCollection()->addFieldToFilter("magento_shipment_id", $shipid)->addFieldToFilter("shiptype", "shipment");
        $detailCollection->getSelect()->order("id asc");
        if ($detailCollection->count() > 0) {
            if ($detailCollection->getFirstItem()->getIsCancelled()==0) {
                return 1;
            } elseif ($detailCollection->getFirstItem()->getIsCancelled()==1) {
                return 2;
            }
        }
        return false;
    }

    public function getIsReturnCancelled($shipid)
    {
        $detailCollection = $this->_dispatchdetailFactory->create()->getCollection()->addFieldToFilter("magento_shipment_id", $shipid)->addFieldToFilter("shiptype", "return");
        $detailCollection->getSelect()->order("id desc");
        if ($detailCollection->count() > 0) {
            if ($detailCollection->getFirstItem()->getIsCancelled()==0) {
                return 1;
            } elseif ($detailCollection->getFirstItem()->getIsCancelled()==1) {
                return 2;
            }
        }
        return false;
    }

    public function getAvailableCarrierMethods()
    {
        if ($this->_availableCarrierMethods) {
            return $this->_availableCarrierMethods;
        }
        $url = 'GetAllSupportedCarriers';
        $param = '';
        $data_get = $this->getApiRequest($url, 'GET', $param);
        $res = json_decode($data_get, true);
        $temp = [];
        if (isset($res['Carriers'][0]['CarrierName']) && !empty($res['Carriers'])) {
            foreach ($res['Carriers'] as $key => $val) {
                $temp[$val['CarrierID']] = $val['CarrierName'];
            }
        } else {
            $temp =
                        [
                            '3488' => "ABF / ARCBEST",
                            '3431' => "APEX",
                            '2423' => "APPS CARGO",
                            '3248' => "B&R ECKELS",
                            '3147' => "BANDSTRA TRANSPORT",
                            '2266' => "BARR WEST EXPRESS",
                            '3478' => "BARRWEST EXPRESS - NCT",
                            '3360' => "BIG FREIGHT",
                            '3324' => "CANADA POST",
                            '3349' => "CDS TRANSPORT",
                            '3241' => "COLD SHOT COURIER (SHIP BY BUS)",
                            '2728' => "COMOX PACIFIC EXPRESS",
                            '3026' => "CONSOLIDATED FASTFRATE",
                            '3092' => "DAY & ROSS",
                            '3166' => "DHL",
                            '2716' => "DIAMOND DELIVERY",
                            '3115' => "DICOM EXPRESS",
                            '3123' => "DICOM FREIGHT",
                            '3508' => "DIRECT RIGHT",
                            '3430' => "DUKES FREIGHT SERVICE",
                            '3479' => "DUKES FREIGHT SERVICE - NCT",
                            '3358' => "EAST WEST EXPRESS",
                            '3507' => "FRONTIER",
                            '3546' => "GIGG EXPRESS - BRANCH TRANSFER",
                            '2988' => "GOLDEN TRANSFER (HI-WAY 9)",
                            '2569' => "GRIMSHAW TRUCKING",
                            '3260' => "GRIMSHAW TRUCKING - NCT",
                            '3061' => "HIFAB TRANSPORT",
                            '3053' => "HI-WAY 9",
                            '3538' => "HY-LINE EXPRESS",
                            '3506' => "INTOWN COURIER - REQUEST SPECIAL ASSISTANCE",
                            '2249' => "J-6 FREIGHTWAYS",
                            '2918' => "JAYS TRANSPORTATION",
                            '3323' => "KINDERSLEY EXPEDITED",
                            '2890' => "KINDERSLEY TRANSPORT",
                            '2497' => "LA CRETE TRANSPORT",
                            '2544' => "LAC LA BICHE TRANSPORT",
                            '2801' => "LOOMIS",
                            '2788' => "MAIKO'S TRUCKING",
                            '3060' => "MANITOULIN TRANSPORT",
                            '2771' => "MAXXIMUM EXPRESS",
                            '3372' => "MIDLAND TRANSPORT",
                            '3064' => "NATURAL RESOURCE RECOVERY (GREEN TRANSPORT)",
                            '2616' => "OIL CITY EXPRESS",
                            '2760' => "OVERLAND WEST",
                            '3359' => "OVERLAND WEST - #6104891",
                            '3477' => "OVERLAND WEST - NCT",
                            '3163' => "OVERLAND WEST (LANDTRAN)",
                            '3348' => "P2P EXPRESS",
                            '2400' => "PACIFIC NORTHWEST FREIGHT",
                            '2789' => "PRIORITY ONE TRANSPORT",
                            '2484' => "PROVOST FREIGHT LINES",
                            '2599' => "PUROLATOR",
                            '2986' => "QUIKX TRANSPORTATION",
                            '3471' => "ROBERT TRANSPORT",
                            '3350' => "ROBERT TRANSPORT - CROSSROADS C&I",
                            '2272' => "ROCKET EXPRESS",
                            '2787' => "ROCKY VIEW TRANSPORT",
                            '3156' => "ROSEDALE TRANSPORTATION",
                            '3068' => "ROSENAU TRANSPORT",
                            '3168' => "SAMEDAY",
                            '3466' => "SPEEDEE TRANSPORT",
                            '3122' => "SPEEDY TRANSPORT",
                            '2566' => "TST-CF EXPRESS (CANADIAN FREIGHTWAYS)",
                            '2836' => "UPS",
                            '3539' => "URSUS TRANSPORT",
                            '2893' => "VANKAM/MUSTANG FREIGHTWAYS",
                            '2770' => "VITRAN",
                            '3537' => "VITRAN - LUCKY SUPERMARKET RATES",
                            '3353' => "WESTERN CANADA EXPRESS",
                            '2581' => "WHITECOURT TRANSPORT",
                            '2987' => "WILLY'S TRUCKING",
                            '3504' => "WILLY'S TRUCKING - NCT",
                            '3091' => "YRC FREIGHT",
                        ];
        }
        $this->_availableCarrierMethods = $temp;
        return $this->_availableCarrierMethods;
    }

    public function setItemData($order, $params)
    {
        if (isset($params['package_id']) && $params['package_id']) {
            foreach ($params['package_id'] as $key => $value) {
                $itemId = $key;
                $item = $order->getItemById($itemId);
                $item->setShippingHeight($params['shipping_height'][$itemId]);
                $item->setShippingWidth($params['shipping_width'][$itemId]);
                $item->setShippingLength($params['shipping_length'][$itemId]);
                $item->setPackageId($params['package_id'][$itemId]);
                $item->setRecalculateQty($params['shipping_qty'][$itemId]);
                $item->setShippingWeight($params['shipping_weight'][$itemId]);
            }
        }
        
        return $order;
    }

    public function updateOrderShippingMethod($order, $params)
    {
        if ($order->getId()) {
            $shippingmethod = $order->getShippingMethod();
            $this->setItemData($order, $params);
            $carrier = '';
             
            
            $carrier = explode("|", $params['shipnewmethod']);
            if (strpos($shippingmethod, "smartshipping_")!==false) {
                 //if($order->getData('smart_shipping_carrier_estimate')) {
                    /* $estimateshipping = json_decode($order->getData('smart_shipping_carrier_estimate'),true);
                     foreach ($estimateshipping as $key => $shippingitem) {
                         if($carrierId==$shippingitem['CarrierId']) {
                             $estimateshipping[$key]['Price'] = $shipamount;
                         }
                     }*/
                     $estimateshipping = [[
                         'CarrierId'=>$carrier[0],
                         'CarrierName'=>$carrier[1],
                         'TransitDays'=>$carrier[2],
                         'ServiceName'=>$carrier[3],
                         'Price'=>$carrier[4],
                         "APIRatesEnabled"=>$carrier[5],
                         "APIDocumentEnabled"=>$carrier[6],
                         "APIDispatchEnabled"=>$carrier[7]
                     ]];
                     $estimateshippingNew = json_encode($estimateshipping, true);
                     $order->setData('smart_shipping_carrier_estimate', $estimateshippingNew);
                    //}
            }
            if ($shippingmethod != "smartshipping_".$carrier[0]) {
                $order->setShippingDescription("Smart Shipping - ".$carrier[1]);
                $order->setShippingMethod("smartshipping_".$carrier[0]);
            }
                $shipamount = $carrier[4];
                $taxamount = $order->getTaxAmount();
                $percent = ($taxamount/($order->getShippingAmount() + $order->getSubTotal())) * 100;
                $order->setShippingAmount($shipamount);
                $order->setBaseShippingAmount($shipamount);
                //$newtax = (($order->getSubTotal() + $shipamount) * $percent)/100;
                $order->setGrandTotal($order->getSubTotal() + $shipamount + $order->getTaxAmount());
                $order->setBaseGrandTotal($order->getSubTotal() + $shipamount + $order->getTaxAmount());
                $order->setState('new');
                $order->setStatus('pendingpayment');
                $this->savedShipping($order);
                $order->save();
        }
    }

    public function isSmartShippingOrder($shipid)
    {
        if ($shipid) {
            $shipment = $this->_shipmentRepository->get($shipid);
            $order_id = $shipment->getOrderId();
            if ($order_id) {
                $order = $this->_orderRepository->get($order_id);
                if (strpos($order->getShippingMethod(), "smartshipping_")!==false) {
                    return true;
                }
            }
        }
        return false;
    }
	
	/**
     * Get current store currency code
     *
     * @return string
     */
    public function getCurrentCurreCode()
    {
        return $this->storeManager->getStore()->getBaseCurrencyCode();
    }    
    
}
