<?php
IncludeModuleLangFile(__FILE__);
$MODULE_ID="newsite.treeproperty";
if($GLOBALS['APPLICATION']->GetGroupRight("main") > "R")
{
    $MODULE_ID = str_replace('.', '_', $MODULE_ID);

    $aMenu = array(
        "parent_menu" => "global_menu_content",
        "section" => $MODULE_ID,
        "sort" => 360,
        "text" => GetMessage("NEWSITE_TREEPROPERTY_MODULE_NAME"),
        "title" => GetMessage("NEWSITE_TREEPROPERTY_MODULE_DESC"),
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