<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

\Bitrix\Main\Loader::includeModule('iblock');

/**
 * Tree
 */
class Tree extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $arParams['CACHE_TIME'] = $arParams['CACHE_TIME'] ?? 86400;
        return $arParams;
    }

    public function executeComponent()
    {
        $IBLOCK_ID = $this->arParams['IBLOCK_ID'];
        if ($this->startResultCache()) {
            $sections = [];

            $sectionResult = \Bitrix\Iblock\SectionTable::getList([
                'filter' => ['IBLOCK_ID' => $IBLOCK_ID, '=ACTIVE' => 1],
                'select' => ['ID', 'NAME', 'IBLOCK_SECTION_ID'],
                'order' => ['LEFT_MARGIN' => 'ASC']
            ]);

            while ($section = $sectionResult->fetch()) {
                $count = \CIBlockSection::GetSectionElementsCount($section['ID'], ["CNT_ACTIVE" => "Y"]);
                if ($count > 0) {
                    $sections[$section['ID']] = $section;
                    $sections[$section['ID']]['ELEMENTS'] = [];
                    $sections[$section['ID']]['CHILDREN'] = [];
                }
            }

            $elementResult = \Bitrix\Iblock\Elements\ElementTestTable::getList([
                'filter' => ['IBLOCK_ID' => $IBLOCK_ID, '=ACTIVE' => 1],
                'select' => ['ID', 'NAME', 'CODE', 'IBLOCK_SECTION_ID', 'ELEMENT_TAGS'],
                'order' => ['SORT' => 'ASC']
            ]);

            foreach ($elementResult->fetchCollection() as $element) {
                if (isset($sections[$element['IBLOCK_SECTION_ID']])) {
                    $tagsArray = [];
                    if ($element->get('ELEMENT_TAGS')) {
                        foreach ($element->get('ELEMENT_TAGS')->getAll() as $elem) {
                            $tagsArray[] = $elem->getValue();
                        }
                    }
                    $tags = implode(', ', $tagsArray);
                    $element = [
                        'ID' => $element->getId(),
                        'NAME' => $element->getName(),
                        'IBLOCK_SECTION_ID' => $element->get('IBLOCK_SECTION_ID'),
                        'TAGS' => $tags
                    ];
                    $sections[$element['IBLOCK_SECTION_ID']]['ELEMENTS'][] = $element;
                }
            }

            $tree = [];

            foreach ($sections as $section) {
                if ($section['IBLOCK_SECTION_ID']) {
                    $sections[$section['IBLOCK_SECTION_ID']]['CHILDREN'][] = &$sections[$section['ID']];
                } else {
                    $tree[] = &$sections[$section['ID']];
                }

            }
            $this->arResult = $tree;
            $this->includeComponentTemplate();
        }

    }
}
