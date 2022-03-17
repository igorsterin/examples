<?php

use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\FileTable;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::includeModule('iblock');

header('Content-type: application/json; charset=utf-8');
$inputJson = file_get_contents('php://input');
$inputArray = json_decode($inputJson, true);

$function = $_GET['function'] ?? 'testScript';

$result = $function($inputArray);

echo json_encode($result, 256);

function testScript($query = [])
{
    return
//        getSections()
        getElements();
}


function getSections($query)
{
    $sections = SectionTable::getList([
        'filter' => ['IBLOCK_SECTION_ID' => $query],
        'select' => ['ID', 'IBLOCK_SECTION_ID', 'NAME']
    ])->fetchCollection()->getIdList();
    return $sections;
}


function getElements()
{
    $elements = ElementTable::getList([
        'filter' => ['IBLOCK_ID' => 3],
        'select' => [
            'ID',
            'IBLOCK_SECTION_ID',
            'SECTION_NAME' => 'SECTION.NAME',
            'PROPERTY_VALUE' => 'ELEMENT_PROPERTY.VALUE',
            'FILE_NAME' => 'FILE.FILE_NAME',
            'SUBDIR' => 'FILE.SUBDIR',
            'FILE_SIZE' => 'FILE.FILE_SIZE',
            'PARENT_SECTION_ID' => 'SECTION.IBLOCK_SECTION_ID',
        ],
        'runtime' => [
            'SECTION' => [
                'data_type' => SectionTable::class,
                'reference' => ['this.IBLOCK_SECTION_ID' => 'ref.ID']
            ],
            'ELEMENT_PROPERTY' => [
                'data_type' => ElementPropertyTable::class,
                'reference' => ['this.ID' => 'ref.IBLOCK_ELEMENT_ID']
            ],
            'FILE' => [
                'data_type' => FileTable::class,
                'reference' => ['this.PROPERTY_VALUE' => 'ref.ID']
            ]
        ]
    ])->fetchAll();



    return $elements;
}

function prepareSectionsArray($sectionsArray)
{
    $result = [];
    foreach ($sectionsArray as $item) {
        $result[$item['IBLOCK_SECTION_ID']][$item['ID']] = [];
    }
    return $result;
}

function getTreeFromPreparedArray($array)
{
    $result = $array[''];
    getTreeRecursively( $result[''], $array);

    return $result;
}

function getTreeRecursively(&$parent, $array)
{
    foreach ($parent as $childID => &$child) {
        $child['CHILD_SECTIONS'] = $array[$childID] ?: [];

        if (count($child['CHILD_SECTIONS']) > 0) {
            getTreeRecursively($child['CHILD_SECTIONS'], $array);
        }
    }
}


