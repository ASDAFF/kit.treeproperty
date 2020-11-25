<?
/**
 * Copyright (c) 25/11/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

use \Bitrix\Main\Localization\Loc;
Loc::LoadMessages(__FILE__);

if(class_exists("kit.treeproperty")) return;
Class kit_treeproperty extends CModule
{
    var $MODULE_ID = "kit.treeproperty";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $MODULE_GROUP_RIGHTS = "Y";

    function kit_treeproperty()
    {
        $arModuleVersion = array();
 
        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path . "/version.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        } else {
            $this->MODULE_VERSION = "1.0.0";
            $this->MODULE_VERSION_DATE = "2018.01.01";
        }

        $this->MODULE_NAME = Loc::getMessage("KIT_TREEPROPERTY_INSTALL_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("KIT_TREEPROPERTY_INSTALL_DESCRIPTION");
        $this->PARTNER_NAME = Loc::getMessage("KIT_TREEPROPERTY_INSTALL_COPMPANY_NAME");
        $this->PARTNER_URI = "https://asdaff.github.io/";
    }

    // Install functions
    function InstallDB()
    {
        RegisterModule($this->MODULE_ID);
        return TRUE;
    }

    function InstallEvents()
    {
        return TRUE;
    }

    function InstallOptions()
    {
        return TRUE;
    }

    function InstallFiles()
    {
        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath() . "/install")) {
            CopyDirFiles($this->GetPath()."/install/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", true, true);
            CopyDirFiles($this->GetPath()."/install/js", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js", true, true);
            CopyDirFiles($this->GetPath()."/install/panel", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/panel", true, true);
            CopyDirFiles($this->GetPath()."/install/ajax", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/ajax", true, true);
            return TRUE;
        } else {
            throw new InvalidPathException($path);
            return true;
        }
    }

    function InstallPublic()
    {
        return TRUE;
    }

    // UnInstal functions
    function UnInstallDB()
    {
        UnRegisterModule($this->MODULE_ID);
        return TRUE;
    }

    function UnInstallEvents()
    {
        return TRUE;
    }

    function UnInstallOptions()
    {
        return TRUE;
    }

    function UnInstallFiles()
    {
        DeleteDirFiles($this->GetPath()."/install/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");
        DeleteDirFiles($this->GetPath(). "/install/ajax", $_SERVER["DOCUMENT_ROOT"] . "/ajax");
        DeleteDirFiles($this->GetPath()."/install/js", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/");
        DeleteDirFiles($this->GetPath()."/install/panel", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/panel/");
        return TRUE;
    }

    function UnInstallPublic()
    {
        return TRUE;
    }

    function DoInstall()
    {
        global $APPLICATION, $step;
        $keyGoodDB = $this->InstallDB();
        $keyGoodEvents = $this->InstallEvents();
        $keyGoodOptions = $this->InstallOptions();
        $keyGoodFiles = $this->InstallFiles();
        $keyGoodPublic = $this->InstallPublic();
    }

    function DoUninstall()
    {
        global $APPLICATION, $step;
        $keyGoodFiles = $this->UnInstallFiles();
        $keyGoodEvents = $this->UnInstallEvents();
        $keyGoodOptions = $this->UnInstallOptions();
        $keyGoodDB = $this->UnInstallDB();
        $keyGoodPublic = $this->UnInstallPublic();
    }

    public function GetPath($notDocumentRoot=false)
    {
        if($notDocumentRoot)
            return str_ireplase(Application::getDocumentRoot(),'',dirname(__DIR__));
        else
            return dirname(__DIR__);

    }
}