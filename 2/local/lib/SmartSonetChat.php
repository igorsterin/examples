<?php

use Bitrix\Im\Model\ChatTable;

class SmartSonetChat
{
    public static function getListAdminsToHideInChat($groupID = [], $chatID = null) {
        $admins = APIComponents::getIdEntities(APIComponents::getUsersGroup([1]));

        $chatsMembers = self::getChatMembersByGroups($groupID, $chatID);
        $listAdminsToHide = [];

        foreach ($chatsMembers as $chat => $members) {
            $listAdminsToHide[$chat] = array_values(array_diff($admins, $members));
        }

        return $listAdminsToHide;
    }

    public static function getGroupsMembers($groupID = []) {

        $groupsMembers = [];

       // $deactivedUsers = self::getDeactivedUsers();
        $UserToGroups = CSocNetUserToGroup::getList(['ID' => 'DESC'], [/*'!=USER_ID' => $deactivedUsers,*/ 'GROUP_ID' => $groupID]);

        while ($UserToGroup = $UserToGroups->Fetch()) {
            $groupsMembers[$UserToGroup['GROUP_ID']][] = (int)$UserToGroup['USER_ID'];
        }
        return $groupsMembers;
    }

    public static function getGroupChatIDs($groupID = null) {
        $groupChats =  ChatTable::getList([
            'filter' => $groupID ? ['ENTITY_TYPE' => 'SONET_GROUP', 'ENTITY_ID' => $groupID] : ['ENTITY_TYPE' => 'SONET_GROUP'],
            'select' => ['ID', 'ENTITY_ID']
        ])->fetchAll();

        $result = [];
        foreach ($groupChats as $groupChat) {
            $result[$groupChat['ENTITY_ID']] = (int)$groupChat['ID'];
        }

        return $result;
    }

    public static function getChatMembersByGroups($groupID = [], $chatID = null) {
        $groupChats = self::getGroupChatIDs($groupID);
        $groupsMembers = self::getGroupsMembers($groupID);
        $chatMembers = [];

        foreach ($groupChats as $group => $chat) {
            $chatMembers[$chat] = $groupsMembers[$group];
        }

        return $chatID ? [$chatID => $chatMembers[$chatID]] : $chatMembers;
    }

    public static function getDeactivedUsers() {
        return \Bitrix\Main\UserTable::getList(['filter' => ['ACTIVE' => 'N']])->fetchCollection()->getIdList();
    }
}