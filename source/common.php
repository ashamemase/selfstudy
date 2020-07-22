<?php

// 
function _L($japanese, $vietnamese)
{
    if ($_SESSION['LANG'] == 'vn')  return $vietnamese;
    else return $japanese;
}

function getTodayModified()
{
    $ret = new DateTime();
    $ret->modify("+3 hour");
    return $ret;
}

class Userdata
{
    public $id;
    public $subid;
    public $name;
    public $password;
    public $realname;
    public $schoolid;
    public $teacherid;
    public $accesslevel;
    public $classid;
    public $validdate;
    public $status;

    //flags
    const REGISTERD = 1;
    const SOUND = 2;
    const CLASSKEY = 4;
    const TEACHERKEY = 8;
    const SCHOOLKEY = 16;
    const TEACHER = Userdata::CLASSKEY;
    const TEACHERHASKEY = Userdata::CLASSKEY & Userdata::TEACHERKEY;
    const MASTER = Userdata::TEACHERHASKEY & Userdata::SCHOOLKEY;

    public function __construct($json)
    {
        $this->id = $json->id;
        $this->subid = $json->subid;
        $this->name = $json->name;
        $this->password = $json->password;
        $this->realname = $json->realname;
        $this->schoolid = $json->schoolid;
        $this->teacherid = $json->teacherid;
        $this->accesslevel = $json->accesslevel;
        $this->classid = $json->classid;
        $this->validdate = $json->validdate;
        $this->status = $json->status;
    }

    //geter
    public function isRegisterd()
    {
        return $this->status & Userdata::REGISTERD != 0 ? true : false;
    }
    public function allowSound()
    {
        return $this->status & Userdata::SOUND != 0 ? true : false;
    }
    public function hasTeacherKey()
    {
        return $this->status & Userdata::TEACHERKEY != 0 ? true : false;
    }
    public function hasSchoolKey()
    {
        return $this->status & Userdata::SCHOOLKEY != 0 ? true : false;
    }
    public function hasClassKey()
    {
        return $this->status & Userdata::CLASSKEY != 0 ? true : false;
    }
    public function hasQuizAccess()
    {
        return ($this->status & Userdata::REGISTERD) && !($this->status & Userdata::CLASSKEY);
    }
}

class WordUserCardData
{
    public $id;
    public $userid;
    public $cardid;
    public $scheduled;
    public $expiredata;
    public $interval;
    public $efficiency;

    public function __construct($json)
    {

        $this->id = $json->id;
        $this->userid = $json->userid;
        $this->cardid = $json->cardid;
        $this->scheduled = $json->scheduled;
        $this->expiredata = $json->expiredata;
        $this->interval = $json->interval;
        $this->efficiency = $json->efficiency;
    }
}

class WordUserData
{
    public $id;
    public $lesson;
    public $addcount;
    public $startday;
    public $efficiency;
    public $autoadd;
    public $lastaccessed;


    public function __construct($json)
    {
        $this->id = $json->id;
        $this->lesson = $json->lesson;
        $this->addcount = $json->addcount;
        $this->startday = $json->startday;
        $this->efficiency = $json->efficiency;
        $this->autoadd = $json->autoadd;
        $this->lastaccessed = $json->lastaccessed;
    }

    public function calcAutoaddcount()
    {
        if ($this->autoadd) {
            $x = $this->efficiency;
            $y = floor(0.000131 * $x * $x * $x - 0.0714285 * $x * $x + 13.285 * $x - 807);
            $y = $y < 0 ? 0 : $y;
            $this->addcount = $y > 40 ? 40 : $y;
        } else {
            $this->addcount = 0;
        }
    }
}

class WordCardData
{
    public $id;
    public $linked_id;
    public $schedule;
    public $lesson;
    public $quiztype;
    public $soundfile;
    public $kanji;
    public $hiragana;
    public $complement;
    public $vietnamese;
    public $form;
    public $politeform;
    public $pf_soundfil;

    public function __construct($json)
    {
        $this->id = $json->id;
        $this->linked_id = $json->linked_id;
        $this->schedule = $json->schedule;
        $this->lesson = $json->lesson;
        $this->quiztype = $json->quiztype;
        $this->soundfile = $json->soundfile;
        $this->kanji = $json->kanji;
        $this->hiragana = $json->hiragana;
        $this->complement = $json->complement;
        $this->vietnamese = $json->vietnamese;
        $this->form = $json->form;
        $this->politeform = $json->politeform;
        $this->pf_soundfil = $json->pf_soundfil;
    }
}

class WordLog
{
    public $id;
    public $userid;
    public $cardid;
    public $rank;
    public $timesolved;
    public $timespend;
    public $newflag;

    public function __construct($json)
    {
        $this->id = $json->id;
        $this->userid = $json->userid;
        $this->cardid = $json->cardid;
        $this->rank = $json->rank;
        $this->timesolved = $json->timesolved;
        $this->timespend = $json->timespend;
        $this->newflag = $json->newflag;
    }
}
//
//  変換用文字列メモ
//    public \$([^;]+);
//    $this->$1=$json->$1;
// ([^\s]+).+$
// public $$$1;