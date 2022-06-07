<?php
namespace Smarttshipping\Shipping\Model\Api;

class Smartapi extends \Magento\Framework\Model\AbstractModel
{
    protected $importlogger;
    protected $orderManager;
    protected $messageManager;
    protected $warrantysale;
    protected $httpstatuscode;
    protected $resourceModel;
    protected $connection;
    protected $helper;
    protected $scopeConfig;
    protected $apiurl;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Smarttshipping\Shipping\Model\Logger $logger,
        \Magento\Framework\App\ResourceConnection $resource,
        \Smarttshipping\Shipping\Model\Api\Httpstatuscode $httpstatuscode,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->importlogger = $logger;
        $this->resourceModel = $resource;
        $this->httpstatuscode = $httpstatuscode;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $registry);
    }

    protected function getConnection()
    {
        if (!$this->connection) {
            $this->connection = $this->resourceModel
            ->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        }

        return $this->connection;
    }

    public function getConfigValue($field, $storeId = null)
    {
        if (!$storeId) {
            if (isset($this->configSettings[$field])) {
                return $this->configSettings[$field];
            }
        }
        $this->configSettings[$field] = $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $this->configSettings[$field];
    }

    public function getHttpErrorCode($errorCode)
    {
        return $this->httpstatuscode->getHttpDtatusCode($errorCode);
    }

    public function postApiRequest($requestArr, $gatewayUrl, $method = 'POST', $apiKey = null)
    {
        $requestString = $requestArr;
        if ($apiKey == null) {
            $apiKey = $this->getApikey();
        }

        $header = ['APIKEY:' .$apiKey,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $gatewayUrl);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestString);

        $data = curl_exec($ch);
        
        $responce = curl_getinfo($ch);
        if (!curl_errno($ch) && empty($data) !== false) {
            $errors['errors'][] = [
                                    'status' => $responce['http_code'],
                                    'code' => $responce['http_code']*100,
                                    'title'=>$this->getHttpErrorCode($responce['http_code'])
                                ];
            return json_encode($errors);
        } elseif (!($data)) {
            $this->importlogger->errorLog($responce['http_code']);
            $errors['errors'][] = [
                                    'status'=>$responce['http_code'],
                                    'code' => $responce['http_code']*100,
                                    'title'=>$this->getHttpErrorCode($responce['http_code'])
                                ];
            return json_encode($errors);
        }
        curl_close($ch);
        unset($ch);
        return $data;
    }

    public function getApiRequest($requestArr, $gatewayUrl, $method = 'GET', $apiKey = null)
    {
        $requestString = $requestArr;
        
        if ($apiKey == null) {
            $apiKey = $this->getApikey();
        }
        
        $header = ['APIKEY:' .$apiKey,
            'Content-Type: application/json'
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $gatewayUrl);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        $responce = curl_getinfo($ch);
        if (!curl_errno($ch) && empty($data) !== false) {
            $errors['errors'][] = [
                                    'status'=>$responce['http_code'],
                                    'code' => $responce['http_code']*100,
                                    'title'=>$this->getHttpErrorCode($responce['http_code'])
                                ];
            return json_encode($errors);
        } elseif (!($data)) {
            $this->importlogger->errorLog($responce['http_code']);
            $errors['errors'][] = [
                                    'status'=>$responce['http_code'],
                                    'code' => $responce['http_code']*100,
                                    'title'=>$this->getHttpErrorCode($responce['http_code'])
                                ];
            return json_encode($errors);
        }
        curl_close($ch);
        unset($ch);
        return $data;
    }

    public function getApiurl()
    {
        return $this->scopeConfig->getValue('carriers/smartshipping/api_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSandboxApiurl()
    {
        return $this->scopeConfig->getValue('carriers/smartshipping/sandbox_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getApikey()
    {
          return $this->scopeConfig->getValue('carriers/smartshipping/api_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isSandbox()
    {
        return $this->scopeConfig->getValue('carriers/smartshipping/is_sandbox', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSmartProductId()
    {
        return $this->scopeConfig->getValue('carriers/smartshipping/smart_product_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    public function getApiBaseUrl()
    {
        if ($this->apiurl) {
            return $this->apiurl;
        }
        if ($this->isSandbox()) {
            $this->apiurl = $this->getSandboxApiurl();
        } else {
            $this->apiurl = $this->getApiurl();
        }
        return $this->apiurl;
    }


    public function getAddressCitySearch($name)
    {
        $urlPram = 'GetCities?search='.rawurlencode($name);
        $url = $this->getApiBaseUrl().$urlPram;
        $result = $this->getApiRequest('', $url, 'GET');
        return $this->processResponce($result);
    }

    public function getAddressCountriesSearch($name)
    {
        $urlPram = 'GetCountries?search='.rawurlencode($name);
        $url = $this->getApiBaseUrl().$urlPram;
        $result = $this->getApiRequest('', $url, 'GET');
        return $this->processResponce($result);
    }

    public function getAddressStateSearch($name)
    {
        $urlPram = 'GetStates?search='.rawurlencode($name);
        $url = $this->getApiBaseUrl().$urlPram;
        $result = $this->getApiRequest('', $url, 'GET');
        return $this->processResponce($result);
    }

    public function getAddressCity()
    {
        $urlPram = 'GetCities';
        $url = $this->getApiBaseUrl().$urlPram;
        $result = $this->getApiRequest('', $url, 'GET');
        return $this->processResponce($result);
    }

    public function getAddressState()
    {
        $urlPram = 'GetStates';
        $url = $this->getApiBaseUrl().$urlPram;
        $result = $this->getApiRequest('', $url, 'GET');
        return $this->processResponce($result);
    }

    public function getAddressCountries()
    {
        $urlPram = 'GetCountries';
        $url = $this->getApiBaseUrl().$urlPram;
        $result = $this->getApiRequest('', $url, 'GET');
        return $this->processResponce($result);
    }

    public function getAllPackages()
    {
        $urlPram = 'GetAllPackages';
        $url = $this->getApiBaseUrl().$urlPram;
        $result = $this->getApiRequest('', $url, 'GET');
        return $this->processResponce($result);
    }

    public function getAllCarrierServiceOptions()
    {
        $urlPram = 'getAllCarrierServiceOptions';
        $url = $this->getApiBaseUrl().$urlPram;
        $result = $this->getApiRequest('', $url, 'GET');
        return $this->processResponce($result);
    }

    public function getSyncAddress()
    {
        $citis = $this->getAddressCity();
        $cityFlag = $this->insertCityData($citis);
        $Countries = $this->getAddressCountries();
        $CountriesFlag = $this->insertCountryData($Countries);
        $State = $this->getAddressState();
        $StateFlag = $this->insertStateData($State);
        if ($cityFlag && $CountriesFlag && $StateFlag) {
            return true;
        }
        return false;
    }

    protected function searchFiledValue($name, $field, $table)
    {
        $connection = $this->getConnection();
        $table = $connection->getTableName($table);
        $select = $connection->select()
            ->from(['main' =>$table], ['*'])
            ->where("main.$field LIKE ?", $name.'%');
        return $connection->fetchAll($select);
    }

    protected function getGetCityByName($name)
    {
        $connection = $this->getConnection();
        $table = $connection->getTableName('smart_city_data');
        $select = $connection->select()
            ->from(['main' =>$table], ['*'])
            ->where('UPPER(main.city_name) = ?', strtoupper($name));
        return $connection->fetchRow($select);
    }

    protected function getGetCountryByName($name)
    {
        $connection = $this->getConnection();
        $table = $connection->getTableName('smart_countries_data');
        $select = $connection->select()
            ->from(['main' =>$table], ['*'])
            ->where('UPPER(main.country_name) = ?', strtoupper($name));
        return $connection->fetchRow($select);
    }

    protected function getGetStateByName($name)
    {
        $connection = $this->getConnection();
        $table = $connection->getTableName('smart_state_data');
        $select = $connection->select()
            ->from(['main' =>$table], ['*'])
            ->where('UPPER(main.state_name) = ?', strtoupper($name));
        return $connection->fetchRow($select);
    }

    protected function trunkateTable($table)
    {
        if (empty($table) !== true) {
            $adapter = $this->getConnection();
            $tableName = $adapter->getTableName($table);
            $select = "TRUNCATE $tableName";
            return $adapter->query($select);
        }
    }

    public function getCity($name)
    {
        $temp = [];
        $city = $this->getGetCityByName($name);
        if (empty($city) !== true) {
            $temp['smart_city_id'] = $city['city_id'];
        } else {
            $city = $this->searchFiledValue($name, 'city_name', 'smart_city_data');
            if (count($city) > 0) {
                foreach ($city as $key => $cit) {
                    if (strtoupper($cit['city_name'])==strtoupper($name)) {
                        $temp['smart_city_id'] = $cit['city_id'];
                        break;
                    }
                }
            }
            if (empty($temp) === true) {
                $result = $this->getAddressCitySearch($name);
                if (isset($result['Success']) && $result['Success']==1 && $result['Success'] && isset($result['Cities'])) {
                    foreach ($result['Cities'] as $key => $citem) {
                        if (isset($citem['CityName']) && strtoupper($citem['CityName'])==strtoupper($name)) {
                            $temp['smart_city_id'] = $citem['CityId'];
                            break;
                        }
                    }
                }
            }
        }
        return $temp;
    }

    public function getCountry($name)
    {
        $temp = [];
        $country = $this->getGetCountryByName($name);
        if (empty($country) !== true) {
            $temp['smart_country_id'] = $country['country_id'];
        } else {
            $country = $this->searchFiledValue($name, 'country_name', 'smart_countries_data');
            if (count($country) > 0) {
                foreach ($country as $key => $con) {
                    if (strtoupper($con['country_name'])==strtoupper($name)) {
                        $temp['smart_country_id'] = $con['country_id'];
                        break;
                    }
                }
            }

            if (empty($temp) === true) {
                $result = $this->getAddressCountriesSearch($name);
                if (isset($result['Success']) && $result['Success']==1 && $result['Success'] && isset($result['Countries'])) {
                    foreach ($result['Countries'] as $key => $citem) {
                        if (isset($citem['CountryName']) && strtoupper($citem['CountryName']) == strtoupper($name)) {
                            $temp['smart_country_id'] = $citem['CountryId'];
                            break;
                        }
                    }
                }
            }
        }
        return $temp;
    }

    public function getState($name)
    {
        $temp = [];
        $state = $this->getGetStateByName($name);
        if (empty($state) !== true) {
            $temp['smart_region_id'] = $state['state_id'];
        } else {
            $state = $this->searchFiledValue($name, 'state_name', 'smart_state_data');
            if (count($state) > 0) {
                foreach ($state as $key => $sta) {
                    if (strtoupper(isset($sta['country_name']))==strtoupper($name)) {
                        $temp['smart_region_id'] = $sta['state_id'];
                        break;
                    }
                }
            }
            if (empty($temp) === true) {
                $result = $this->getAddressStateSearch($name);
                if (isset($result['Success']) && $result['Success']==1 && $result['Success'] && isset($result['States'])) {
                    foreach ($result['States'] as $key => $citem) {
                        if (isset($citem['StateName']) && strtoupper($citem['StateName'])==strtoupper($name)) {
                            $temp['smart_region_id'] = $citem['StateId'];
                            break;
                        }
                    }
                }
            }
        }
        return $temp;
    }

    public function validateAddress($countryName, $city, $region = null)
    {
        
        $temp = [];
        $city = $this->getCity($city);
        $country = $this->getCountry($countryName);
        $state = $this->getState($region);
        $temp = array_merge($temp, $city, $country, $state);
        return $temp;
    }

    protected function insertData($table, $data)
    {
        if (empty($data) !== true) {
            $this->trunkateTable($table);
            $adapter = $this->getConnection();
            $tableName = $adapter->getTableName($table);
            return $adapter->insertMultiple($tableName, $data);
        }
    }

    protected function insertCityData($insertData)
    {
        $data = [];
        
        if (isset($insertData['Success']) && isset($insertData['Cities'])) {
            foreach ($insertData['Cities'] as $key => $city) {
                if ($city['CityName'] != '') {
                    $data[] = ['city_id'=>$city['CityId'], 'city_name'=>$city['CityName']];
                }
            }
        }
        if (count($data)>0) {
            $this->insertData('smart_city_data', $data);
        }
        return true;
    }

    protected function insertCountryData($insertData)
    {
        $data = [];
        if (isset($insertData['Success']) && isset($insertData['Countries'])) {
            foreach ($insertData['Countries'] as $key => $city) {
                if ($city['CountryName'] != '') {
                    $data[] = ['country_id'=>$city['CountryId'], 'country_name'=>$city['CountryName']];
                }
            }
        }
   
        if (count($data)>0) {
            $this->insertData('smart_countries_data', $data);
        }
        return true;
    }

    protected function insertStateData($insertData)
    {
        $data = [];
        if (isset($insertData['Success']) && isset($insertData['States'])) {
            foreach ($insertData['States'] as $key => $city) {
                if ($city['StateName'] != '') {
                    $data[] = ['state_id'=>$city['StateId'], 'state_name'=>$city['StateName']];
                }
            }
        }
        if (count($data)>0) {
            $this->insertData('smart_state_data', $data);
        }
        return true;
    }

    public function processResponce($value, $error = false)
    {
        $errorCodeValidate = [/*'40100',*/'0','40101','40102'];
        $value = json_decode($value, true);
        if (isset($value['errors'])) {
            $data = $value['errors'];
            if (isset($data[0]['code']) && $data[0]['code'] != '20400' && (in_array($data[0]['code'], $errorCodeValidate) || $error === true)) {
                $this->importlogger->errorLog($data);
                $detail = isset($data[0]['detail'])?$data[0]['detail']:'';
                if (empty($this->messageManager) !== true) {
                    $this->messageManager->addError(__($data[0]['title'].', '.$detail));
                }
                throw new \Exception(__($data[0]['title'].', '.$detail));
            }
            $this->importlogger->errorLog($value);
            return $value;
        }
        return $value;
    }
}
