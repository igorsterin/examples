<?php

class SmartDoubleCompaniesAPI
{

	/**
	 * Используется в БП Согласования проекта
	 *
	 * @param $companyID
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getDoubleCompanies($companyID)
	{
		$allDoubleCompaniesIDs = SmartAPI::mergeArrays([
			self::getDoubleCompaniesByTitleOrUfAddress($companyID),
			self::getDoubleCompaniesByMultiFields($companyID),
			self::getDoubleCompaniesByRqINN($companyID)
		]);

		return \Bitrix\Crm\CompanyTable::getList([
			'filter' => ['ID' => $allDoubleCompaniesIDs],
			'select' => ['ID', 'TITLE']
		])->fetchAll();
	}

	public static function getDoubleCompaniesByTitleOrUfAddress($companyID): array
	{
		$companyFields = \Bitrix\Crm\CompanyTable::getByPrimary($companyID, [
			'select' => ['TITLE', 'UF_ADDRESS']
		])->fetch();
		return \Bitrix\Crm\CompanyTable::getList([
			'filter' => [
				['!=ID' => $companyID, 'TITLE' => $companyFields['TITLE']],
				['!=ID' => $companyID, 'UF_ADDRESS' => $companyFields['UF_ADDRESS'], '!=UF_ADDRESS' => null],
				'LOGIC' => 'OR'
			]
		])->fetchCollection()->getIdList();
	}

	public static function getDoubleCompaniesByMultiFields($companyID): array
	{
		$companyMultiFieldsArray = \Bitrix\Crm\FieldMultiTable::getList([
			'filter' => [
				'ENTITY_ID' => 'COMPANY',
				'ELEMENT_ID' => $companyID,
				'TYPE_ID' => ['PHONE', 'EMAIL']
			]
		])->fetchCollection()->getValueList();
		return array_unique(\Bitrix\Crm\FieldMultiTable::getList([
			'filter' => [
				'ENTITY_ID' => 'COMPANY',
				'!=ELEMENT_ID' => $companyID,
				'VALUE' => $companyMultiFieldsArray
			],
		])->fetchCollection()->getElementIdList());
	}

	public static function getDoubleCompaniesByRqINN($companyID): array
	{
			$companyRqINN = \Bitrix\Crm\RequisiteTable::getList([
			'filter' => [
				'ENTITY_TYPE_ID' => 4,
				'ENTITY_ID' => $companyID
			]
		])->fetch()['RQ_INN'];
		return $companyRqINN ? \Bitrix\Crm\RequisiteTable::getList([
			'filter' => [
				'ENTITY_TYPE_ID' => 4,
				'!=ENTITY_ID' => $companyID,
				'RQ_INN' => $companyRqINN
			]
		])->fetchCollection()->getEntityIdList() : [];
	}
}