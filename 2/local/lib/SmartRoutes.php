<?php

use Bitrix\Main\UserTable;

class SmartRoutes
{
	public static function getRoutesBossesIDs($routesIDs)
	{
		$routesBosses = \Bitrix\Iblock\Model\Section::compileEntityByIblock(25)::getList([
			'filter' => [
				'ID' => $routesIDs,
			],
			'select' => ['UF_BOSS', 'UF_BOSS_OTHER']

		])->fetchCollection();

		return array_values(array_unique(array_merge(
			$routesBosses->getUfBossList(),
			SmartAPI::mergeArrays($routesBosses->getUfBossOtherList())
		)));
	}

	public static function getUserBossesIDs(int $userID = null)
	{
		global $USER;
		$userID = $userID ?: $USER->GetID();

		$userRoutesIDs = self::getUserRoutesIDs($userID);
		$routesBossesIDs = self::getRoutesBossesIDs($userRoutesIDs);
//		return $routesBossesIDs;
		return array_values(array_diff($routesBossesIDs, [$userID, 0]));

	}

	public static function getUserRoutesIDs($userID = null)
	{
		global $USER;
		$userID = $userID ?: $USER->GetID();

		return UserTable::getByPrimary($userID, ['select' => ['UF_COM_ROUTE']])->fetch()['UF_COM_ROUTE'];
	}
}