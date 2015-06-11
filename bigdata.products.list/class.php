<?
use \Bitrix\Main;
use \Bitrix\Iblock;
use \Bitrix\Main\Analytics\Counter;
use \Bitrix\Main\Context;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\SystemException as SystemException;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class BigDataSimpleComponent extends CBitrixComponent{

	protected $possibleTypes = array('similar_sell', 'similar_view', 'similar', 'bestsell', 'personal');
	protected $dataToRequest;
	protected $productId;
	protected $url  = 'https://analytics.bitrix.info/crecoms/v1_0/recoms.php';

	public function onIncludeComponentLang()
	{
		$this->includeComponentLang(basename(__FILE__));
		Loc::loadMessages(__FILE__);
	}

	protected function checkModules()
	{
		if ($this->arParams['IBLOCK_ID'] && !Main\Loader::includeModule('iblock'))
			throw new SystemException(Loc::getMessage('SH_IBLOCK_MODULE_NOT_INSTALLED'));
	}

	public function onPrepareComponentParams($params)
	{

		if(!isset($params["CACHE_TIME"]))
			$params["CACHE_TIME"] = 36000000;

		$params['ELEMENT_ID'] = (isset($params['ELEMENT_ID']) ? (int)$params['ELEMENT_ID'] : 0);
		$params['ELEMENTS_LIMIT'] = (isset($params['ELEMENTS_LIMIT']) ? (int)$params['ELEMENTS_LIMIT'] : 10);
		$params['ELEMENT_CODE'] = (isset($params['ELEMENT_CODE']) ? trim($params['ELEMENT_CODE']) : '');
		$params['IBLOCK_ID'] = (isset($params['IBLOCK_ID']) ? (int)$params['IBLOCK_ID'] : 0);

		if (!$params["RCM_TYPE"])
			$params["RCM_TYPE"] = 'any';

		return $params;
	}

	protected function prepareData()
	{
		$this->setBigData($this->arParams['RCM_TYPE']);
		if ($this->arParams['ELEMENT_ID'])
			$this->setProductId($this->arParams['ELEMENT_ID']);

		$this->arResult['BIG_DATA'] = $this->getData();
		$this->arResult['RID'] = $this->arResult['BIG_DATA']['op'];
		$this->arResult['REQUEST_URL'] = $this->getUrl();

		if (!$this->arParams['ELEMENT_ID'] && $this->arParams['ELEMENT_CODE']){

			\CModule::IncludeModule('iblock');
			$arSelect = Array("ID", "NAME", "DATE_ACTIVE_FROM");
			$arFilter = Array("CODE"=>$this->arParams['ELEMENT_CODE']);
			$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>1), $arSelect);
			if($el = $res->fetch())
			{
				$this->arParams['ELEMENT_ID'] = $el['ID'];
			}
		}

	}

	protected function extractDataFromCache()
	{
		if ($this->arParams['CACHE_TYPE'] == 'N')
			return false;
		return !($this->StartResultCache());
	}

	protected function putDataToCache()
	{
		//$this->SetResultCacheKeys();
	}

	protected function abortDataCache()
	{
		$this->AbortResultCache();
	}

	public function executeComponent()
	{
		try
		{
			$this->checkModules();
			if (!$this->extractDataFromCache())
			{
				$this->prepareData();
				$this->setResultCacheKeys(array());
				$this->includeComponentTemplate();
				$this->putDataToCache();
			}
		}
		catch (SystemException $e)
		{
			$this->abortDataCache();
			ShowError($e->getMessage());
		}
	}

	public function setBigData($type = null, $productId = null)
	{
		$this->dataToRequest = array(
			'uid' => $_COOKIE['BX_USER_ID'],
			'aid' => Counter::getAccountId(),
			'count' => $this->arParams['ELEMENTS_LIMIT'],
		);

		$productId = intval($productId);
		if ($productId)
			$this->productId = $productId;

		if ($type)
			$this->setType($type);
	}

	public function getUrl(){
		return $this->url;
	}

	public function getData(){
		return $this->dataToRequest;
	}

	protected function getProductId(){
		return intval($this->productId);
	}

	public function setType($type){
		if ($type == 'any_similar' && $this->productId)
		{
			$this->possibleTypes = array('similar_sell', 'similar_view', 'similar');
			$type = $this->possibleTypes[array_rand($this->possibleTypes)];
		}
		elseif ($type == 'any_personal')
		{
			$this->possibleTypes = array('bestsell', 'personal');
			$type = $this->possibleTypes[array_rand($this->possibleTypes)];
		}
		elseif ($type == 'any')
		{
			$this->possibleTypes = array('similar_sell', 'similar_view', 'similar', 'bestsell', 'personal');

			if (!$this->productId){
				unset($this->possibleTypes[array_search('similar_sell', $this->possibleTypes)]);
				unset($this->possibleTypes[array_search('similar_view', $this->possibleTypes)]);
				unset($this->possibleTypes[array_search('similar', $this->possibleTypes)]);
			}

			$type = $this->possibleTypes[array_rand($this->possibleTypes)];
		}

		if ($type == 'bestsell')
		{
			$data['op'] = 'sim_domain_items';
			$data['type'] = 'order';
			$data['domain'] = Context::getCurrent()->getServer()->getHttpHost();
		}
		elseif ($type == 'personal')
		{
			$data['op'] = 'recommend';
		}
		elseif ($type == 'similar_sell')
		{
			$data['op'] = 'simitems';
			$data['eid'] = $this->productId;
			$data['type'] = 'order';
		}
		elseif ($type == 'similar_view')
		{
			$data['op'] = 'simitems';
			$data['eid'] = $this->productId;
			$data['type'] = 'view';
		}
		elseif ($type == 'similar')
		{
			$data['op'] = 'simitems';
			$data['eid'] = $this->productId;
		}

		if ($this->arParams['IBLOCK_ID'])
			$data['ib'] = $this->arParams['IBLOCK_ID'];

		foreach($data as $key => $val){
			$this->dataToRequest[$key] = $val;
		}
	}

	public function setProductId($id){
		$id = intval($id);
		if (!$id) return;

		$this->productId = $id;
	}

	public function makeCurlRequest(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->getUrl());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->getData()));
		$data = curl_exec($ch);
		curl_close($ch);

		return json_decode($data, true);
	}
}