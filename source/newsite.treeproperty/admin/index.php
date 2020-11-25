<?php

use Newsite\TreeProperty as NTP;
use \Bitrix\Main\Localization\Loc;

define("ADMIN_MODULE_NAME", "newsite.treeproperty");
define("CSS_THEME", "default");

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
Loc::LoadMessages(__FILE__);
if (!$USER->IsAdmin()) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

if (!CModule::IncludeModule(ADMIN_MODULE_NAME)) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$APPLICATION->SetTitle(GetMessage("NEWSITE_TREEPROPERTY_FIELD_SETTINGS_LIST"));

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

// init scripts
CJSCore::RegisterExt(
    'ns_treeproperty',
    array(
        'js'   => array('/bitrix/js/' . ADMIN_MODULE_NAME . '/jstree.js', '/bitrix/js/' . ADMIN_MODULE_NAME . '/script.js'),
        'css' => array('/bitrix/panel/' . ADMIN_MODULE_NAME . '/themes/' . CSS_THEME . '/style.css', '/bitrix/panel/' . ADMIN_MODULE_NAME . '/style.css'),
        'rel'  => array('jquery')
    )
);
CJSCore::Init('ns_treeproperty');

$objAdmin = new NTP\Admin();
$iblockTypes = $objAdmin->getIblockTypes();
$iblocks = $objAdmin->getIblocks();
?>
    <div class="newsite_treeproperty">
        <form action="#" id="newsite_treeproperty_form" method="post">
            <input type="hidden" name="action" value="savesettings">
            <div>
                <div class="newsite_item_row">
                    <label><?= GetMessage("NEWSITE_TREEPROPERTY_TYPE_BLOCK") ?></label>
                    <select name="type">
                        <? $bFirst = true;
                        foreach ($iblockTypes as $id => $item):
                            if ($bFirst) {
                                $currType = $id;
                                $bFirst = false;
                            }
                            ?>
                            <option value="<?= $id ?>"><?= $item['NAME'] ?></option>
                        <? endforeach ?>
                    </select>
                </div>
                <div class="newsite_item_row">
                    <label><?= GetMessage("NEWSITE_TREEPROPERTY_IBLOCK_ID") ?></label>
                    <select name="block">
                        <? foreach ($iblocks as $id => $item): ?>
                            <option value="<?= $id ?>" data-type="<?= $item['IBLOCK_TYPE_ID'] ?>"
                                <? if ($item['IBLOCK_TYPE_ID'] != $currType): ?> style="display: none;" <? endif ?> ><?= '[' . $item['ID'] . '] ' . $item['NAME'] ?></option>
                        <? endforeach ?>
                    </select>
                </div>
            </div>
        </form>
    </div>
    <div id="newsite_treeproperty_js">
    </div>
<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>