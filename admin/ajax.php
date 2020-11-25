<?php
/**
 * Copyright (c) 25/11/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

use Kit\TreeProperty as NTP;
use \Bitrix\Main\Localization\Loc;

define('ADMIN_MODULE_NAME', 'kit.treeproperty');
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');
Loc::LoadMessages(__FILE__);

if (!$USER->IsAdmin()) {
    $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

if (!CModule::IncludeModule(ADMIN_MODULE_NAME)) {
    $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}
if (!CModule::IncludeModule('iblock')) {
    $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$iblock = intval($_REQUEST['iblock']);
if ($iblock <= 0) {
    $res = \CIBlock::GetList(array('SORT'=>'ASC'),array())->fetch();
    $iblock = $res['ID'];
}
$section = intval($_REQUEST['id']);
if ($section <= 0) {
    $section = 0;
}

//$arr[0] - id ������ � ������� ����������� ��-�� $arr[1] - id ��-��
$arr = explode('_',$_REQUEST['property_id']);

switch($_REQUEST['action']){
    case 'get':
        echo json_encode(NTP\Admin::getSectionChilds($iblock, $section));
        break;
    case 'copy':
        $new_parent = $_REQUEST['new_parent'];
        NTP\Admin::addSectionLink($new_parent, $arr[1]);
        echo 'copy';
        break;
    case 'move':
        $old_parent = $_REQUEST['old_parent'];
        $new_parent = $_REQUEST['new_parent'];
        NTP\Admin::deleteSectionLink($old_parent, $arr[1]);
        NTP\Admin::addSectionLink($new_parent, $arr[1]);
        echo 'move';
        break;
    case 'delete':
        $parent = $_REQUEST['parent'];
        NTP\Admin::deleteSectionLink($parent, $arr[1]);
        echo 'delete';
        break;
    case 'get_sections':
        echo NTP\Admin::getPropertySectionsText($iblock, $arr[1]);
        break;
    case 'get_products':
        echo NTP\Admin::getCountProductsText($arr[0], $arr[1]);
        break;
    case 'go_to_products':
        echo NTP\Admin::getRedirectToProductUrl($iblock, $arr[0], $arr[1]);
        break;
    case 'go_to_products_section':
        echo NTP\Admin::getRedirectToProductUrl($iblock, $arr[0], $arr[1], true);
        break;
}
die();