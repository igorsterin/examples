<?php

class DoubleObj
{
    public $leadID;
    public $leadTasks;
    public $dealID;
    public $dealStage;
    public $dealTasks;
    public $doubleIsProcessed;
    public $isDouble;
    public $hasDeal;
    public $isDeleted;

    public function __construct($leadID)
    {
        if (!\Bitrix\Crm\LeadTable::getById($leadID)->fetch()) return false;
        $this->leadID = $leadID;
        $this->leadTasks = DoublesAPI::getCrmEntityTasks($this->leadID, 'lead');
        $deal = Bitrix\Crm\DealTable::getList([
            'filter' => ['LEAD_ID' => $leadID]
        ])->fetchObject();
        if ($deal) {
            $this->dealID = $deal->getId();
            $this->dealStage = $deal->getStageId();
            $this->dealTasks = DoublesAPI::getCrmEntityTasks($this->dealID, 'deal');
            $this->hasDeal = true;
        } else {
            $this->hasDeal = false;
        }
        $this->isDeleted = false;
    }

    public function isProcessed()
    {
        $this->doubleIsProcessed = $this->dealStage == 'C2:NEW' || $this->dealStage =='C2:PREPARATION';
        return $this;
    }

    public function deleteTasks($tasks)
    {
        foreach ($tasks as $task) {
            (new CTaskItem($task['ASSOCIATED_ENTITY_ID'], 1))->delete();
        }
    }

    public function deleteObjEntities()
    {
        $CCrmLead = new CCrmLead();
        $CCrmLead->Delete($this->leadID);
       // $this->deleteTasks($this->leadTasks);
        $CCrmDeal = new CCrmDeal();
        $CCrmDeal->Delete($this->dealID);
       // $this->deleteTasks($this->dealTasks);
        Log::logFile('', [$CCrmLead->LAST_ERROR, $CCrmDeal->LAST_ERROR], 'deleteDoublesDebug.log');
        $this->isDeleted = true;
    }
}