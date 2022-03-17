<?php

class DoublesAPI
{
    const TYPE_LEAD = 1;
    const TYPE_DEAL = 2;

    public static function testDAP()
    {
        return 'class DoublesApi';
    }

    public static function getOwnerTypeID($type)
    {
        $type = strtoupper($type);
        switch ($type) {
            case 'LEAD':
                return self::TYPE_LEAD;
            case 'DEAL':
                return self::TYPE_DEAL;
            default:
                return false;
        }
    }

    public static function getLeadsDeals($leadIDs)
    {
        return Bitrix\Crm\DealTable::getList([
            'filter' => ['LEAD_ID' => $leadIDs]
        ])->fetchCollection()->getIdList();
    }

    public static function getCrmEntityTasks($entityID, $ownerType = null)
    {
        $ownerTypeID = self::getOwnerTypeID($ownerType);
        if (!$ownerTypeID) return false;
       return \Bitrix\Crm\ActivityTable::getList([
            'filter' => [
                'PROVIDER_ID' => 'TASKS',
                'OWNER_ID' => $entityID,
                'OWNER_TYPE_ID' => $ownerTypeID
            ],
           'select' => ['ASSOCIATED_ENTITY_ID', 'SUBJECT']
        ])->fetchAll();
    }

    public static function getMailLeads($mailIDs)
    {
           $mailLeads =  \Bitrix\Crm\ActivityTable::getList([
                'filter' => ['UF_MAIL_MESSAGE' => $mailIDs],
                'select' => ['OWNER_ID', 'UF_MAIL_MESSAGE']
            ]);
           $result = [];
           while ($message = $mailLeads->fetch()) {
               $result[$message['UF_MAIL_MESSAGE']][] = $message['OWNER_ID'];
           }
           return $result;
    }

    public static function getDoubleLeads($mailLeads, $showKeyMail = false)
    {
        $result = [];
        foreach ($mailLeads as $keyMail => $mail) {
            foreach ($mail as $keyLead => $lead) {
                if ($showKeyMail) {
                    $result[$keyMail][$lead] = $keyLead === 0 ? 'not double' : 'double';
                } else {
                    $result[$lead] = $keyLead === 0 ? 'not double' : 'double';
                }
            }
        }
        return $result;
    }

    public static function filterLeadsByNonClosedDeals($leadIDs)
    {
        return get_object_vars(Bitrix\Crm\DealTable::getList([
            'filter' => ['LEAD_ID' => $leadIDs, 'CLOSED' => 'N']
        ])->fetchCollection())/*->getLeadIdList()*/;
    }

    public static function deleteDouble()
    {
        return \Bitrix\Crm\DealTable::delete()->getData();
    }
}