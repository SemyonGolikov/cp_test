<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;

$this->SetViewTarget('inside_pagetitle');

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'pagetitle-toolbar-field-view');

\Bitrix\Main\UI\Extension::load("ui.buttons.icons");

$popupName = $this->randString(6);
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.group_create.popup",
	".default",
	array(
		"NAME" => $popupName,
		"PATH_TO_GROUP_EDIT" => (strlen($arParams["PATH_TO_GROUP_CREATE"]) > 0 
			? htmlspecialcharsback($arParams["PATH_TO_GROUP_CREATE"])
			: ""
		)
	),
	null,
	array("HIDE_ICONS" => "Y")
);

$filterID = (!empty($arParams["FILTER_ID"]) ? $arParams["FILTER_ID"] : 'SONET_GROUP_LIST');

?><div class="pagetitle-container pagetitle-flexible-space" style="overflow: hidden;" id="<?=htmlspecialcharsbx($filterID)?>_filter_container"><?
$APPLICATION->IncludeComponent(
	'bitrix:main.ui.filter',
	'',
	array(
		'GRID_ID' => $filterID,
		'FILTER_ID' => $filterID,
		'FILTER' => $arResult["Filter"],
		'FILTER_FIELDS' => array(),
		'FILTER_PRESETS' => $arResult['FilterPresets'],
		'ENABLE_LIVE_SEARCH' => true,
		'ENABLE_LABEL' => true,
		'CONFIG' => array(
			'AUTOFOCUS' => false
		)
	),
	$this->getComponent()
);

foreach($arResult["Filter"] as $filterField)
{
	if (
		$filterField['type'] == 'custom_entity'
		&& $filterField['selector']['TYPE'] == 'user'
	)
	{
		$userSelector = $filterField['selector']['DATA'];

		$selectorID = $userSelector['ID'];
		$fieldID = $userSelector['FIELD_ID'];

		$APPLICATION->IncludeComponent(
			"bitrix:main.ui.selector",
			".default",
			array(
				'ID' => $selectorID,
				'ITEMS_SELECTED' => array(),
				'CALLBACK' => array(
					'select' => 'BitrixSGFilterDestinationSelectorManager.onSelect',
					'unSelect' => '',
					'openDialog' => '',
					'closeDialog' => '',
					'openSearch' => ''
				),
				'OPTIONS' => array(
					'eventInit' => 'BX.SonetGroupList.Filter:openInit',
					'eventOpen' => 'BX.SonetGroupList.Filter:open',
					'context' => 'SONET_GROUP_LIST_FILTER_'.$fieldID,
					'contextCode' => 'U',
					'useSearch' => 'N',
					'userNameTemplate' => CUtil::JSEscape(CSite::GetNameFormat()),
					'useClientDatabase' => 'Y',
					'allowEmailInvitation' => 'N',
					'enableDepartments' => 'Y',
					'enableSonetgroups' => 'N',
					'departmentSelectDisable' => 'Y',
					'allowAddUser' => 'N',
					'allowAddCrmContact' => 'N',
					'allowAddSocNetGroup' => 'N',
					'allowSearchEmailUsers' => 'N',
					'allowSearchCrmEmailUsers' => 'N',
					'allowSearchNetworkUsers' => 'N',
					'allowSonetGroupsAjaxSearchFeatures' => 'N',
					'enableAll' => 'N'
				)
			),
			false,
			array("HIDE_ICONS" => "Y")
		);
		?>
		<script>
			BX.ready(
				function()
				{
					BitrixSGFilterDestinationSelector.create(
						"<?=CUtil::JSEscape($selectorID)?>",
						{
							filterId: "<?=CUtil::JSEscape($filterID)?>",
							fieldId: "<?=CUtil::JSEscape($fieldID)?>"
						}
					);
				}
			);
		</script>
		<?
	}
}

?></div><?

if ($arParams["ALLOW_CREATE_GROUP"] == "Y")
{
	?><a class="ui-btn ui-btn-primary ui-btn-icon-add" href="<?=htmlspecialcharsbx($arParams["HREF"])?>"><?
		?><?=Loc::getMessage("SONET_C36_T_CREATE2")?></a><?

	?><script>
		BX.ready(function()
		{
			<?
			if (isset($_GET["new"]))
			{
				?>
				BX.SGCP.ShowForm("create", "<?=$popupName?>", {});
				<?
			}
			?>
		});<?
	?></script><?
}

?><script>
	BX.ready(function()
	{
		var sonetGroupFilter = new BX.Bitrix24.SonetGroupFilter({
			filterId: '<?=CUtil::JSEscape($arParams["FILTER_ID"])?>'
		});
		sonetGroupFilter.init({
			minSearchStringLength: <?=intval($arResult["ftMinTokenSize"])?>
		});
	});<?
?></script><?

$this->EndViewTarget();