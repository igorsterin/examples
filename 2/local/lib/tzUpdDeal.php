<?php

use Bitrix\Main\UserTable;

class tzUpdDeal
{
    public static function checkNewAssigned($assignedID, $userID)
    {
        $userComRoutes = UserTable::getByPrimary($userID, ['select' => ['UF_COM_ROUTE']])->fetch()['UF_COM_ROUTE'];

        $comRoutesEmployees = UserTable::getList([
            'filter' => ['UF_COM_ROUTE' => $userComRoutes],
            'order' => ['ID' => 'ASC']
        ])->fetchCollection()->getIdList();

        return in_array($assignedID, $comRoutesEmployees);
    }
}