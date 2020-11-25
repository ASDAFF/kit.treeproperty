<?php
namespace Newsite\TreeProperty;

use \Bitrix\Main\Localization\Loc;

Loc::LoadMessages(__FILE__);

class Admin
{
    public function __construct()
    {
        \Bitrix\Main\Loader::includeModule("iblock");
        \Bitrix\Main\Loader::includeModule("catalog");
    }

    public function getIblockTypes()
    {
        $res = array();
        $dbl = \CIBlockType::GetList(array('SORT' => 'ASC', 'NAME' => 'ASC'), array('ACTIVE' => 'Y'));
        while ($arr = $dbl->Fetch()) {
            $res[$arr['ID']] = $arr;
        }
        return $res;
    }

    public function getIblocks()
    {
        $res = array(); 
        $dbl = \Bitrix\Iblock\IblockTable::getList(array('order'=>array('SORT' => 'ASC', 'NAME' => 'ASC'), 'filter'=>array('ACTIVE' => 'Y', 'SECTION_PROPERTY'=>'Y')));
//        $dbl = \CIBlock::GetList(array('SORT' => 'ASC', 'NAME' => 'ASC'), array('ACTIVE' => 'Y'));
        while ($arr = $dbl->Fetch()) {
            $res[$arr['ID']] = $arr;
        }
        return $res;
    }

    //возвращает св-ва раздела(привязанные и наследуемые - INHERITED = Y/N)
    public static function getSectionProperties($iblock, $section)
    {
        $res = array();

        $arrRes = \CIBlockSectionPropertyLink::GetArray($iblock, $section);
        foreach ($arrRes as $arr) {
            $prop = \CIBlockProperty::GetById($arr['PROPERTY_ID'])->Fetch();
            $res[$arr['PROPERTY_ID']] = array(
                'type' => 'property',
                'data' => array(
                    'text' => self::getPropertySectionsText($iblock, $arr['PROPERTY_ID']),
                    'edit_link' => '/bitrix/admin/iblock_edit_property.php?ID=' . $arr['PROPERTY_ID'] . '&lang=' . SITE_ID . '&IBLOCK_ID=' . $iblock . '&admin=N',
                ),
                'id' => $section . '_' . $arr['PROPERTY_ID'],
                'text' => $prop['NAME'] . ' <span class="color-gray font10">[' . $arr['PROPERTY_ID'] . ' '.$prop['CODE'].']</span>',
                'children' => false,
                'icon' => 'file ' . ($arr['INHERITED'] == 'N' ? 'not_inherited' : 'inherited'),
            );
        }

        return $res;
    }

    public static function getSectionChilds($iblock, $section)
    {
        $res = array();
        $arFilter = array('IBLOCK_ID' => $iblock, 'ACTIVE' => 'Y', 'SECTION_ID' => $section);
        $rsSect = \CIBlockSection::GetList(array('left_margin' => 'asc'), $arFilter);
        $iblockRes = \CIBlock::GetByID($iblock)->fetch();
        while ($arr = $rsSect->GetNext()) {
            $res[] = array(
                'data' => array(
                    'edit_link' => '/bitrix/admin/iblock_section_edit.php?IBLOCK_ID=' . $iblock . '&type=' . $iblockRes['IBLOCK_TYPE_ID'] . '&lang=' . SITE_ID . '&ID=' . $arr['ID'],
                ),
                'type' => 'section',
                'id' => $arr['ID'],
                'text' => $arr['NAME'] . ' [' . $arr['ID'] . ']',
                'children' => true,
            );
        }
        $properties = self::getSectionProperties($iblock, $section);
        $res = array_merge($res, $properties);
        if ($section == 0) {
            $res[] = self::getIblockFreeProperty($iblock);
        }
        return $res;
    }

    public static function getIblockFreeProperty($iblock)
    {
        global $DB;
        $dbIblockProps = $DB->Query('select ID, NAME, CODE from b_iblock_property where ID not in (select distinct PROPERTY_ID from b_iblock_section_property) and IBLOCK_ID = ' . $DB->ForSql($iblock).';');

        $childs = array();
        while ($arr = $dbIblockProps->Fetch()) {
            $childs[] = array(
                'type' => 'property',
                'id' => 'f_' . $arr['ID'],
                'data' => array(
                    'text' => self::getPropertySectionsText($iblock, $arr['ID']),
                    'edit_link' => '/bitrix/admin/iblock_edit_property.php?ID=' . $arr['ID'] . '&lang=' . SITE_ID . '&IBLOCK_ID=' . $iblock . '&admin=N'
                ),
                'text' => $arr['NAME'] . ' <span class="color-gray">[' . $arr['ID'] . ' '.$arr['CODE'].']</span>',
                'children' => false,
                'icon' => 'file inherited',

            );
        }

        $res = array(
            'type' => 'section',
            'id' => 'not_inherited_props',
            'data' => array(),
            'text' => GetMessage("NEWSITE_TREEPROPERTY_NOT_INHERITED", array('#COUNT#' => count($childs))),
            'children' => array(),
        );
        $res['children'] = $childs;
        return $res;
    }

    /*
     * возвращает список разделов к которым прикреплено св-во в виде строки текста
     */
    public static function getPropertySectionsText($iblock, $property_id)
    {
        $sections = '';
        global $DB;
        $res = $DB->Query('select b_iblock_section.NAME,b_iblock_section.ID from b_iblock_section_property left join b_iblock_section on b_iblock_section_property.SECTION_ID = b_iblock_section.ID where b_iblock_section_property.IBLOCK_ID = ' . $DB->ForSql($iblock) . ' and b_iblock_section_property.PROPERTY_ID = ' . $DB->ForSql($property_id));
        while ($sec = $res->fetch()) {
            if ($sec['ID']) {
                $sections .= $sec['NAME'] . ' [' . $sec['ID'] . ']<br>';
            } else {
                $sections .= GetMessage("NEWSITE_TREEPROPERTY_IBLOCK") . ' [0]<br>';
            }
        }
        return $sections;
    }

    /*
     * возвращает количество товаров с заполненным св-вом в разделе и в иблоке в целом в виде строки текста
     */
    public static function getCountProductsText($section_id, $property_id)
    {
        global $DB;
        $countAll = $DB->Query('select count(distinct IBLOCK_ELEMENT_ID) as count from b_iblock_element_property where IBLOCK_PROPERTY_ID = ' . $DB->ForSql($property_id).'  group by IBLOCK_PROPERTY_ID')->fetch();

        if(empty($section_id)){
            $section = ' is NULL';
        } else {
            $section = ' = '.$DB->ForSql($section_id);
        }
        $countSection = $DB->Query('select count(distinct b_iblock_element_property.IBLOCK_ELEMENT_ID) as count from b_iblock_element_property left join b_iblock_element on b_iblock_element_property.IBLOCK_ELEMENT_ID = b_iblock_element.ID where b_iblock_element_property.IBLOCK_PROPERTY_ID = '.$DB->ForSql($property_id).' and b_iblock_element.IBLOCK_SECTION_ID '.$section.' group by b_iblock_element_property.IBLOCK_PROPERTY_ID')->fetch();
        $counts = array($countAll['count']?:0, $countSection['count']?:0);
        return json_encode($counts);
    }

    public static function addSectionLink($parent, $property_id)
    {
        if ($parent !== 'not_inherited_props') {
            $parent = intval($parent);
            \CIBlockSectionPropertyLink::Add($parent, $property_id);
            return true;
        }
        return false;
    }

    public static function deleteSectionLink($parent, $property_id)
    {
        if ($parent !== 'not_inherited_props') {
            $parent = intval($parent);
            \CIBlockSectionPropertyLink::Delete($parent, $property_id);
            return true;
        }
        return false;
    }

    public static function getRedirectToProductUrl($iblock, $section_id, $property_id, $in_section = false){
        global $DB;

        $isCorectType = false;

        $iblockRes = \CIBlock::getByID($iblock)->fetch();
        $pType = $DB->Query('select PROPERTY_TYPE from b_iblock_property where ID = '.$DB->ForSql($property_id))->fetch();

        $DB->Query('update b_iblock_property set FILTRABLE = "Y" where ID = ' . $DB->ForSql($property_id) . ' and FILTRABLE = "N"');

        if($in_section){
            $sect = 'find_section_section='.$section_id;
        } else {
            $sect = 'find_section_section=-1&find_el_subsections=Y';
        }

        if(!in_array($pType['PROPERTY_TYPE'], array('L', 'F'))) {
            $isCorectType = true;
            $url =  '/bitrix/admin/iblock_element_admin.php?PAGEN_1=1&SIZEN_1=20&type=' . $iblockRes['IBLOCK_TYPE_ID'] . '&IBLOCK_ID=' . $iblockRes['ID'] . '&lang=' . LANG . '&set_filter=Y&adm_filter_applied=0&'.$sect.'&find_el_property_' . $property_id . '=~NULL';
        } else {
            $url = '/bitrix/admin/iblock_element_admin.php?PAGEN_1=1&SIZEN_1=20&type=' . $iblockRes['IBLOCK_TYPE_ID'] . '&IBLOCK_ID=' . $iblockRes['ID'] . '&lang=' . LANG . '&'.$sect;
        }
        return json_encode(array('type_ok' =>$isCorectType, 'url' => $url));
    }
}
