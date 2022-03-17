<?php

class MailDoubleLeads
{
    public $mailID;
    public $doubleCollection = [];
    public $filteredDoubleCollection = [];
    public $deleteResult = [];


    public function __construct($mailID)
    {
        $this->mailID = $mailID;
        $mailLeadsIDs = \Bitrix\Crm\ActivityTable::getList([
            'filter' => ['UF_MAIL_MESSAGE' => $this->mailID],
        ])->fetchCollection()->getOwnerIdList();

        $i = 0;
        foreach ($mailLeadsIDs as $mailLeadID) {
            if (!\Bitrix\Crm\LeadTable::getById($mailLeadID)->fetch()) continue;
            $this->doubleCollection[$i] = new DoubleObj($mailLeadID);
            $this->doubleCollection[$i]->isDouble = $i > 0;
            $this->doubleCollection[$i]->doubleIsProcessed = $this->doubleCollection[$i]->dealStage && !($this->doubleCollection[$i]->dealStage == 'C2:NEW' || $this->doubleCollection[$i]->dealStage =='C2:PREPARATION');
            $i++;
        }

        $this->filterDoublesByProcessedStatus();
    }

    public function filterDoublesByProcessedStatus()
    {

        foreach ($this->doubleCollection as $item) {
            if (!$item->doubleIsProcessed) {
                $this->filteredDoubleCollection[] = $item;

            }
        }
    }

    public function deleteDoubles()
    {
        if (count($this->doubleCollection) < 2) return $this;
        if (count($this->filteredDoubleCollection) == count($this->doubleCollection)) {
            foreach ($this->filteredDoubleCollection as $item) {
                if ($item->isDouble) $item->deleteObjEntities();
            }
        } else {
            foreach ($this->filteredDoubleCollection as $item) {
                $item->deleteObjEntities();
            }
        }
        return $this;
    }

    public function getDeleteResult()
    {
        $result = [];
        foreach ($this->doubleCollection as $item) {
            $result[] = [
                'LEAD_ID' => $item->leadID,
                'DEAL_ID' => $item->dealID,
                'DEAL_STAGE' => $item->dealStage,
                'IS_DELETED' => $item->isDeleted
            ];
        }
        return $result;
    }
}