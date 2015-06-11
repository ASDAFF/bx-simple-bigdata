<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */
use Bitrix\Main\Loader;
use Bitrix\Iblock;

if(!Loader::includeModule('iblock'))
	return;

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock = array();
$iblockFilter = (
!empty($arCurrentValues['IBLOCK_TYPE'])
	? array('TYPE' => $arCurrentValues['IBLOCK_TYPE'], 'ACTIVE' => 'Y')
	: array('ACTIVE' => 'Y')
);
$rsIBlock = CIBlock::GetList(array('SORT' => 'ASC'), $iblockFilter);
while ($arr = $rsIBlock->Fetch())
	$arIBlock[$arr['ID']] = '['.$arr['ID'].'] '.$arr['NAME'];
unset($arr, $rsIBlock, $iblockFilter);

$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"RCM_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage('SH_RCM_TYPE_TITLE'),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => array(
				// general
				'bestsell' => GetMessage('SH_RCM_BESTSELLERS'),
				// personal
				'personal' => GetMessage('SH_RCM_PERSONAL'),
				// item2item
				'similar_sell' => GetMessage('SH_RCM_SOLD_WITH'),
				'similar_view' => GetMessage('SH_RCM_VIEWED_WITH'),
				'similar' => GetMessage('SH_RCM_SIMILAR'),
				// randomly distributed
				'any_similar' => GetMessage('SH_RCM_SIMILAR_ANY'),
				'any_personal' => GetMessage('SH_RCM_PERSONAL_WBEST'),
				'any' => GetMessage('SH_RCM_RAND')
			),
		),
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SH_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SH_IBLOCK"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
		),
		"ELEMENT_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SH_IBLOCK_ELEMENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["ELEMENT_ID"]}',
		),
		"ELEMENT_CODE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SH_IBLOCK_ELEMENT_CODE"),
			"TYPE" => "STRING",
			"DEFAULT" => '',
		),
		"ELEMENTS_LIMIT" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SH_ELEMENTS_LIMIT"),
			"TYPE" => "STRING",
			"DEFAULT" => '',
		),
		"CACHE_TIME" => array("DEFAULT"=>3600),
		"CACHE_GROUPS" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("SH_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
	),
);
?>