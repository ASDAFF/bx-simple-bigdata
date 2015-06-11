<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="bigdata-recommended">
	<div class="bigdata-wrap"></div>
</div>

<script>
	var bigData = {
		'cookie_prefix': '<?=CUtil::JSEscape(COption::GetOptionString("main", "cookie_name", "BITRIX_SM"))?>',
		'cookie_domain': '<?=$APPLICATION->GetCookieDomain()?>',
		'recommendationId': '<?=$arResult['RID']?>',
		'ajaxElementsPage': '<?=$componentPath?>/ajax.php',
		'data': <?=CUtil::PhpToJSObject($arResult['BIG_DATA'])?>,
		'url': '<?=$arResult['REQUEST_URL']?>',
		'detailPageUrlRecommendedClass': 'bx_rcm_view_link',
		'addToCartActionClass': 'add2cart'
	}
</script>