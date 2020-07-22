<?php
require_once 'database.php';

class WordQuiz
{
    private $dbWQUserData;
    private $dbWQUserCardData;
    private WordUserData $wqUserData;

    public function __construct($id)
    {
        $this->dbWQUserData = new DBWordQuizUserData();
        $this->dbWQUserCardData = new DBWordQuizUserCardData($id);
        $wqUserData = $this->dbWQUserData->GetQuizUserDataFromId($id);
    }

    public function initializeWordQuiz()
    {
        //ユーザーデータのアップデート
        $pdo = $this->dbWQUserData->pdo;
        $this->wqUserData->calcAutoAddCount();
        $this->dbWQUserData->SetInitialUserData($this->wqUserData);
        $this->dbWQUserCardData->SetInitialUserCardData($this->wqUserData);
    }
}
