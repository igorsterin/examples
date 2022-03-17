<?php

class SmartAPI
{
	const UF_CRM_SOURCE_FIELD_ID = 272;

    public static function getUserAllowedProjects($userID = null) {
        global $USER;
        $userID = $userID ?: $USER->GetID();

        return \Bitrix\Socialnetwork\UserToGroupTable::getList([
            'filter' => ['USER_ID' => $userID]
        ])->fetchCollection()->getGroupIdList();
    }

	public static function getUserAllowedDealCategories($userID = null) {
		global $USER;
		$userID = $userID ?: $USER->GetID();

		$ArrUserCategoryID = $USER->GetByID($userID)->fetch();
		$UserCategoryID = $ArrUserCategoryID["UF_CATEGORY_DEAL"];
		sort($UserCategoryID);

		//Если не выбранно, то доступна только общая воронка
		if($UserCategoryID == NULL)
			$UserCategoryID[] = 785;

		return \Bitrix\Iblock\ElementPropertyTable::getList([
			'select' => ['VALUE', 'IBLOCK_ELEMENT_ID'],
			'filter' => ['IBLOCK_PROPERTY_ID' => 113, 'IBLOCK_ELEMENT_ID' => $UserCategoryID],
		])->fetchCollection()->getValueList();
	}

    public static function filterItemsByIDs ($arrItems, $arrFilterIDs)
    {
        $result = [];
        foreach ($arrItems as $arrItem) {
            if (in_array($arrItem['ID'], $arrFilterIDs) || $arrItem['NAME'] == 'Все сделки') $result[] = $arrItem;
        }
        return $result;
    }

	public static function mergeArrays($arrays): array
	{
		$mergedArray = [];
		foreach ($arrays as $array) {
			if (is_array($array)) $mergedArray = array_merge($mergedArray, $array);
		}
		return $mergedArray;
	}

	public static function getUfCrmSourceBySonet($sonetEnumID)
	{
		$sonetEnumValue = CUserFieldEnum::GetList([], ['ID' => $sonetEnumID])->Fetch()['VALUE'];
		return CUserFieldEnum::GetList([], ['VALUE' => $sonetEnumValue, 'USER_FIELD_ID' => self::UF_CRM_SOURCE_FIELD_ID])->Fetch()['ID'];
	}

	public static function startRobotsAndBPsOnAdd($entityID, $entityType)
	{
		if (gettype($entityID) != 'integer') return;
		$arErrors = [];
		\CCrmBizProcHelper::AutoStartWorkflows(
			$entityType,
			$entityID,
			 \CCrmBizProcEventType::Create,
			$arErrors
		);
		(new \Bitrix\Crm\Automation\Starter($entityType, $entityID))
			->setUserIdFromCurrent()
			->runOnAdd();
	}
}