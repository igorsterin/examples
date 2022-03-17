<?php

class tzEditPrj
{
    public static function test()
    {
        return \Bitrix\Crm\DealTable::getList()->fetch();
    }

    public static function modifyFields(&$newFields, $oldFields)
    {
        if ($oldFields['UF_OBJ_PRJ_APPROVED'] == 1 && $newFields['UF_EDIT_NO_DELETE_MODE'] == 'YES') {
            self::modifyFieldsInAddMode($newFields, $oldFields);
        }
        $newFields['UF_EDIT_NO_DELETE_MODE'] = '';
    }

    public static function modifyFieldsInAddMode(&$newFields, $oldFields)
    {
        foreach (array_intersect_key($oldFields, $newFields) as $key => $oldField) {
            $newFields[$key] = self::modifyFieldInAddMode($newFields[$key], $oldField);
        }
    }

    public static function modifyFieldInAddMode($newField, $oldField)
    {
        switch(gettype($oldField))
        {
            case 'array':
                return array_values(array_unique(array_merge($newField, $oldField)));
            case 'string':
                return $newField == '' ? $oldField : $newField;
            case 'NULL':
                return $newField;
        }

    }
}