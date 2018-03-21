<?php

namespace Arrilot\BitrixHermitage;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use CBitrixComponent;
use CBitrixComponentTemplate;
use CIBlock;
use InvalidArgumentException;

Loc::loadMessages(__FILE__);

class Action
{
    protected static $panelButtons = [];

    protected static $iblockElementArray = [];

    protected static $iblockSectionArray = [];

    protected static $hlblockIdsByTableName= [];

    /**
     * Get edit area id for specific type
     *
     * @param CBitrixComponentTemplate|CBitrixComponent $componentOrTemplate
     * @param $type
     * @param $element
     * @return string
     */
    public static function getEditArea($componentOrTemplate, $type, $element)
    {
        $id = is_numeric($element) ? $element : $element['ID'];
        return $componentOrTemplate->GetEditAreaId("{$type}_{$id}");
    }

    /**
     * @param CBitrixComponentTemplate|CBitrixComponent $componentOrTemplate
     * @param $element
     * @return string
     */
    public static function editIBlockElement($componentOrTemplate, $element)
    {
        if (!$GLOBALS['APPLICATION']->GetShowIncludeAreas()) {
            return '';
        }

        if (is_numeric($element)) {
            $element = static::prepareIBlockElementArrayById($element);
        }
        if (!$element["IBLOCK_ID"] || !$element['ID']) {
            throw new InvalidArgumentException('Element must include ID and IBLOCK_ID');
        }

        $buttons = static::getIBlockElementPanelButtons($element);
        $link = $buttons["edit"]["edit_element"]["ACTION_URL"];

        $componentOrTemplate->AddEditAction('iblock_element_' . $element['ID'], $link, CIBlock::GetArrayByID($element["IBLOCK_ID"], "ELEMENT_EDIT"));

        return static::areaForIBlockElement($componentOrTemplate, $element);
    }

    /**
     * @param CBitrixComponentTemplate|CBitrixComponent $componentOrTemplate
     * @param $element
     * @param string $confirm
     * @return string
     */
    public static function deleteIBlockElement($componentOrTemplate, $element, $confirm = null)
    {
        $confirm = $confirm ?: Loc::getMessage('ARRILOT_BITRIX_HERMITAGE_DELETE_IBLOCK_ELEMENT_CONFIRM');

        if (!$GLOBALS['APPLICATION']->GetShowIncludeAreas()) {
            return '';
        }

        if (is_numeric($element)) {
            $element = static::prepareIBlockElementArrayById($element);
        }

        if (!$element["IBLOCK_ID"] || !$element['ID']) {
            throw new InvalidArgumentException('Element must include ID and IBLOCK_ID');
        }
    
        $buttons = static::getIBlockElementPanelButtons($element);
        $link = $buttons["edit"]["delete_element"]["ACTION_URL"];

        $componentOrTemplate->AddDeleteAction('iblock_element_' . $element['ID'], $link, CIBlock::GetArrayByID($element["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => $confirm));

        return static::areaForIBlockElement($componentOrTemplate, $element);
    }

    /**
     * @param CBitrixComponentTemplate|CBitrixComponent $componentOrTemplate
     * @param $element
     * @return string
     */
    public static function editAndDeleteIBlockElement($componentOrTemplate, $element)
    {
        static::editIBlockElement($componentOrTemplate, $element);

        return static::deleteIBlockElement($componentOrTemplate, $element);
    }

    /**
     * @param CBitrixComponentTemplate|CBitrixComponent $componentOrTemplate
     * @param $element
     * @return string
     */
    public static function areaForIBlockElement($componentOrTemplate, $element)
    {
        return static::getEditArea($componentOrTemplate, 'iblock_element', $element);
    }

    /**
     * @param CBitrixComponentTemplate|CBitrixComponent $componentOrTemplate
     * @param $section
     * @return string
     */
    public static function editIBlockSection($componentOrTemplate, $section)
    {
        if (!$GLOBALS['APPLICATION']->GetShowIncludeAreas()) {
            return '';
        }

        if (is_numeric($section)) {
            $section = static::prepareIBlockSectionArrayById($section);
        }

        if (!$section["IBLOCK_ID"] || !$section['ID']) {
            throw new InvalidArgumentException('Section must include ID and IBLOCK_ID');
        }
    
        $buttons = static::getIBlockSectionPanelButtons($section);
        $link = $buttons["edit"]["edit_section"]["ACTION_URL"];

        $componentOrTemplate->AddEditAction('iblock_section_' . $section['ID'], $link, CIBlock::GetArrayByID($section["IBLOCK_ID"], "SECTION_EDIT"));

        return static::areaForIBlockSection($componentOrTemplate, $section);
    }

    /**
     * @param CBitrixComponentTemplate|CBitrixComponent $componentOrTemplate
     * @param $section
     * @param string $confirm
     * @return string
     */
    public static function deleteIBlockSection($componentOrTemplate, $section, $confirm = null)
    {
        $confirm = $confirm ?: Loc::getMessage("ARRILOT_BITRIX_HERMITAGE_DELETE_IBLOCK_SECTION_CONFIRM");

        if (!$GLOBALS['APPLICATION']->GetShowIncludeAreas()) {
            return '';
        }

        if (is_numeric($section)) {
            $section = static::prepareIBlockSectionArrayById($section);
        }

        if (!$section["IBLOCK_ID"] || !$section['ID']) {
            throw new InvalidArgumentException('Section must include ID and IBLOCK_ID');
        }
    
        $buttons = static::getIBlockSectionPanelButtons($section);
        $link = $buttons["edit"]["delete_section"]["ACTION_URL"];

        $componentOrTemplate->AddDeleteAction('iblock_section_' . $section['ID'], $link, CIBlock::GetArrayByID($section["IBLOCK_ID"], "SECTION_DELETE"), array("CONFIRM" => $confirm));

        return static::areaForIBlockSection($componentOrTemplate, $section);
    }

    /**
     * @param CBitrixComponentTemplate|CBitrixComponent $componentOrTemplate
     * @param $section
     * @return string
     */
    public static function editAndDeleteIBlockSection($componentOrTemplate, $section)
    {
        static::editIBlockSection($componentOrTemplate, $section);

        return static::deleteIBlockSection($componentOrTemplate, $section);
    }

    /**
     * @param CBitrixComponentTemplate|CBitrixComponent $componentOrTemplate
     * @param $section
     * @return string
     */
    public static function areaForIBlockSection($componentOrTemplate, $section)
    {
        return static::getEditArea($componentOrTemplate, 'iblock_section', $section);
    }
    
    /**
     * @param CBitrixComponentTemplate|CBitrixComponent $componentOrTemplate
     * @param $element
     * @param string $label
     * @return string
     */
    public static function editHLBlockElement($componentOrTemplate, $element, $label = null)
    {
        $label = $label ?: Loc::getMessage("ARRILOT_BITRIX_HERMITAGE_EDIT_HLBLOCK_ELEMENT_LABEL");

        if (!$GLOBALS['APPLICATION']->GetShowIncludeAreas()) {
            return '';
        }

        if (!$element["HLBLOCK_ID"] && $element["HLBLOCK_TABLE_NAME"]) {
            $element["HLBLOCK_ID"] = static::prepareHLBlockIdByTableName($element["HLBLOCK_TABLE_NAME"]);
        }

        if (!$element["HLBLOCK_ID"] || !$element['ID']) {
            throw new InvalidArgumentException('Element must include ID and HLBLOCK_ID/HLBLOCK_TABLE_NAME');
        }

        $linkTemplate = '/bitrix/admin/highloadblock_row_edit.php?ENTITY_ID=%s&ID=%s&lang=ru&bxpublic=Y';
        $link = sprintf($linkTemplate, (int) $element["HLBLOCK_ID"], (int) $element["ID"]);

        $componentOrTemplate->AddEditAction('hlblock_element_' . $element['ID'], $link, $label);

        return static::areaForHLBlockElement($componentOrTemplate, $element);
    }
    
    /**
     * @param CBitrixComponentTemplate|CBitrixComponent $componentOrTemplate
     * @param $element
     * @param string $label
     * @param string $confirm
     * @return string
     */
    public static function deleteHLBlockElement($componentOrTemplate, $element, $label = null, $confirm = null)
    {
        $label = $label ?: Loc::getMessage('ARRILOT_BITRIX_HERMITAGE_DELETE_HLBLOCK_ELEMENT_LABEL');
        $confirm = $confirm ?: Loc::getMessage('ARRILOT_BITRIX_HERMITAGE_DELETE_HLBLOCK_ELEMENT_CONFIRM');

        if (!$GLOBALS['APPLICATION']->GetShowIncludeAreas()) {
            return '';
        }

        if (!$element["HLBLOCK_ID"] && $element["HLBLOCK_TABLE_NAME"]) {
            $element["HLBLOCK_ID"] = static::prepareHLBlockIdByTableName($element["HLBLOCK_TABLE_NAME"]);
        }

        if (!$element["HLBLOCK_ID"] || !$element['ID']) {
            throw new InvalidArgumentException('Element must include ID and HLBLOCK_ID/HLBLOCK_TABLE_NAME');
        }

        $linkTemplate = '/bitrix/admin/highloadblock_row_edit.php?action=delete&ENTITY_ID=%s&ID=%s&lang=ru';
        $link = sprintf($linkTemplate, (int) $element["HLBLOCK_ID"], (int) $element["ID"]);

        $componentOrTemplate->AddDeleteAction('hlblock_element_' . $element['ID'], $link, $label, array("CONFIRM" => $confirm));

        return static::areaForHLBlockElement($componentOrTemplate, $element);
    }

    /**
     * @param CBitrixComponentTemplate|CBitrixComponent $componentOrTemplate
     * @param $element
     *
     * @return string
     */
    public static function editAndDeleteHLBlockElement($componentOrTemplate, $element)
    {
        static::editHLBlockElement($componentOrTemplate, $element);
        static::deleteHLBlockElement($componentOrTemplate, $element);

        return static::deleteHLBlockElement($componentOrTemplate, $element);
    }

    /**
     * @param CBitrixComponentTemplate|CBitrixComponent $componentOrTemplate
     * @param $element
     * @return string
     */
    public static function areaForHLBlockElement($componentOrTemplate, $element)
    {
        return static::getEditArea($componentOrTemplate, 'hlblock_element', $element);
    }

    /**
     * @param CBitrixComponent|CBitrixComponentTemplate $componentOrTemplate
     * @param $iblockId
     * @param array $options
     */
    public static function addForIBlock($componentOrTemplate, $iblockId, $options = [])
    {
        if (!$GLOBALS['APPLICATION']->GetShowIncludeAreas()) {
            return;
        }

        if ($componentOrTemplate instanceof CBitrixComponentTemplate) {
            $componentOrTemplate = $componentOrTemplate->__component;
        }

        $buttons = CIBlock::GetPanelButtons($iblockId, 0, 0, $options);
        $menu = CIBlock::GetComponentMenu($GLOBALS['APPLICATION']->GetPublicShowMode(), $buttons);

        $componentOrTemplate->addIncludeAreaIcons($menu);
    }

    /**
     * @param $element
     * @return array
     */
    protected static function getIBlockElementPanelButtons($element)
    {
        if (!isset(static::$panelButtons['iblock_element'][$element['ID']])) {
            static::$panelButtons['iblock_element'][$element['ID']] = CIBlock::GetPanelButtons(
                $element["IBLOCK_ID"],
                $element['ID'],
                0,
                ['SECTION_BUTTONS' => false, 'SESSID' => false]
            );
        }

        return static::$panelButtons['iblock_element'][$element['ID']];
    }

    /**
     * @param $section
     * @return array
     */
    protected static function getIBlockSectionPanelButtons($section)
    {
        if (!isset(static::$panelButtons['iblock_section'][$section['ID']])) {
            static::$panelButtons['iblock_section'][$section['ID']] = CIBlock::GetPanelButtons(
                $section["IBLOCK_ID"],
                0,
                $section['ID'],
                ['SESSID' => false]
            );
        }

        return static::$panelButtons['iblock_section'][$section['ID']];
    }
    
    /**
     * @param int $id
     * @return array
     */
    protected static function prepareIBlockElementArrayById($id)
    {
        $id = (int) $id;
        if (!$id) {
            return [];
        }

        if (empty(static::$iblockElementArray[$id])) {
            $connection = Application::getConnection();
            $el = $connection->query("SELECT ID, IBLOCK_ID FROM b_iblock_element WHERE ID = {$id}")->fetch();
            static::$iblockElementArray[$id] = $el ? $el : [];
        }

        return static::$iblockElementArray[$id];
    }

    /**
     * @param int $id
     * @return array
     */
    protected static function prepareIBlockSectionArrayById($id)
    {
        $id = (int) $id;
        if (!$id) {
            return [];
        }

        if (empty(static::$iblockSectionArray[$id])) {
            $connection = Application::getConnection();
            $el = $connection->query("SELECT ID, IBLOCK_ID FROM b_iblock_section WHERE ID = {$id}")->fetch();
            static::$iblockSectionArray[$id] = $el ? $el : [];
        }

        return static::$iblockSectionArray[$id];
    }

    /**
     * @param string $tableName
     * @return int
     */
    protected static function prepareHLBlockIdByTableName($tableName)
    {
        if (empty(static::$hlblockIdsByTableName[$tableName])) {
            $row = HighloadBlockTable::getList([
                'select' => ['ID'],
                'filter' => ['=TABLE_NAME' => $tableName]
            ])->fetch();

            if (!empty($row['ID'])) {
                static::$hlblockIdsByTableName[$tableName] = (int) $row['ID'];
            }
        }

        return static::$hlblockIdsByTableName[$tableName];
    }
}
