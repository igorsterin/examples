<?php

class tzStructure
{

    public static function getUserEmployees($currentUser = ""): array
    {

        $userDepartmentsTree = self::getUserDepartmentsTree($currentUser);

        return self::getAllUsersDepartment($userDepartmentsTree);
    }

    public static function getAllDepartmentsInTree($departments): array
    {
        $departmentsInTree = $departments;

        $filter = $departments;
        do {
            $subDepartments = Bitrix\Iblock\Model\Section::compileEntityByIblock(3)::getList([
                'filter' => ['IBLOCK_SECTION_ID' => $filter],
            ])->fetchCollection()->getIdList();

            $departmentsInTree = array_values(array_unique(array_merge($departmentsInTree, $subDepartments)));
            $filter = $subDepartments;

        } while (count($subDepartments) > 0);

        return $departmentsInTree;
    }

    public static function getUserDirectDepartments($currentUser)
    {
        $userDepartmentsByUfHead = \Bitrix\Iblock\Model\Section::compileEntityByIblock(3)::getList([
            'filter' => ['UF_HEAD' => $currentUser],
        ])->fetchCollection()->getIdList();

        $userDepartmentsByStructure = CUser::GetByID($currentUser)->fetch()["UF_DEPARTMENT"];

        return array_values(array_unique(array_merge($userDepartmentsByUfHead, $userDepartmentsByStructure)));
    }

    public static function getUserDepartmentsTree($currentUser): array
    {
        $userDepartments = self::getUserDirectDepartments($currentUser);

        return self::getAllDepartmentsInTree($userDepartments);
    }

    public static function getAllUsersDepartment($userDepartmentsTree): array
    {
        $arAllUsersDepartment = CIntranetUtils::GetDepartmentEmployees($userDepartmentsTree, false, false, 'N');
        $result = [];
        while ($arUserDepartment = $arAllUsersDepartment->Fetch()) $result[] = $arUserDepartment['ID'];

        return $result;
    }

	public static function isUserInGroup($user, $group)
	{
		return !!(\Bitrix\Main\UserGroupTable::getList([
			'filter' => ['USER_ID' =>  $user, 'GROUP_ID' => $group]
		])->fetch());
	}
	public static function getFilterAllowedAssigned($allowedUsers, $filterAssignedBy) {

		if ($filterAssignedBy == []) {
			$result = $allowedUsers;
		} else {
			$result = array_values(array_intersect($allowedUsers, $filterAssignedBy));
			$result = $result == [] ? false : $result;
		}
		return $result;
	}
}