<?php

class SmartCustomFilter
{

	public static function getProjectsWithRoutesWithoutDeals($routeID)
	{
		$connection = Bitrix\Main\Application::getConnection();
		$sql = "
		SELECT 
GROUP_ID
FROM
b_sonet_user2group
JOIN
b_uts_iblock_25_section ON b_uts_iblock_25_section.UF_BOSS  = b_sonet_user2group.USER_ID
WHERE b_uts_iblock_25_section.VALUE_ID = {$routeID} AND GROUP_ID NOT IN
(
SELECT  
u2g.GROUP_ID AS PROJECT_ID
FROM b_uts_iblock_25_section AS b25
JOIN b_user AS bu ON b25.UF_BOSS = bu.ID
JOIN b_iblock_section AS bis ON bis.ID = b25.VALUE_ID
JOIN b_sonet_user2group AS u2g ON b25.UF_BOSS = u2g.USER_ID
JOIN b_uts_crm_deal AS ucd ON ucd.UF_PROJECT = u2g.GROUP_ID
JOIN b_crm_deal AS bcd ON ucd.VALUE_ID = bcd.ID
JOIN b_crm_deal_category AS dc ON dc.ID = bcd.CATEGORY_ID
JOIN b_iblock_element_property AS iep ON iep.IBLOCK_ELEMENT_ID = b25.UF_CATEGORY_DEAL
WHERE b25.VALUE_ID = {$routeID} AND dc.ID = iep.VALUE
)
		";

		$recordset = $connection->query($sql);
		$result = [];

		while ($record = $recordset->fetch()) {
			$result[] = intval($record['GROUP_ID']);
		}
		return $result;
	}
}