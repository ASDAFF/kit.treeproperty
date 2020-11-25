<?php
/**
 * Copyright (c) 25/11/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

IncludeModuleLangFile(__FILE__);
$MODULE_ID="kit.treeproperty";
if($GLOBALS['APPLICATION']->GetGroupRight("main") > "R")
{
    $MODULE_ID = str_replace('.', '_', $MODULE_ID);

    $aMenu = array(
        "parent_menu" => "global_menu_content",
        "section" => $MODULE_ID,
        "sort" => 360,
        "text" => GetMessage("KIT_TREEPROPERTY_MODULE_NAME"),
        "title" => GetMessage("KIT_TREEPROPERTY_MODULE_DESC"),
        "url" => "/bitrix/admin/".$MODULE_ID."_admin.php",
        "icon" => "",
        "page_icon" => "",
        "items_id" => $MODULE_ID."_items",
        "more_url" => array(),
        "items" => array()
    );


    return $aMenu;
}

?>