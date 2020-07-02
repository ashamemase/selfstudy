<?php
session_start();

// ログイン状態チェック
if (!isset($_SESSION["NAME"])) {
    header("Location: Login.php");
    exit;
}
// アクセスレベルチェック
if ($_SESSION["LEVEL"] != "C") {
    header("Location: index.php");
    exit;
}

//データベース設定
$db['host'] = "localhost";  // DBサーバのURL
$db['user'] = "studyjapanese";  // ユーザー名
$db['pass'] = "m1234567890";  // ユーザー名のパスワード
$db['dbname'] = "selfstudy";  // データベース名

$dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);
$pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

// エラーメッセージの初期化
$errorMessage = "";





//今日の日付取得
$today = new DateTime(); //今日の日付
$today->modify("+4 hour"); //翌日の4時までは前日扱い+ベトナムのタイムゾーン+7 (-4+7)

//ユーザーデータの取得
$userdata;

try {
    $stmt = $pdo->query("SELECT * FROM userdata where id=$_SESSION[ID]");
    foreach ($stmt as $row) {
        $row["lesson"];
        $row["addcount"];
        $row["lastaccessed"];
        $row["autoadd"];
    }
    $userdata = $row;
} catch (PDOException $e) {
    $errorMessage = 'データベースエラー';
    //$errorMessage = $sql;
    //$e->getMessage() でエラー内容を参照可能（デバッグ時のみ表示）
    echo $e->getMessage();
}

//
// 処理部
//
$quizdata; //問題データ
$reload = false; //リロードフラグ
$hascookie = false; //セーブデータ存在フラグ

//クッキーの確認
if (isset($_COOKIE["quizdatadate"])) { //クッキーが残っている
    //クッキーの日付チェック
    $dateLastAccess = new DateTime($_COOKIE["quizdatadate"]);
    $dateLastAccess->setTime(23, 59, 59);
    if ($dateLastAccess < $today) { //クッキーが古い
        $reload = true;
        $hascookie = true;
    } else { //当日のクッキー
        $hascookie = true;
    }
} else { //クッキーが残っていない
    //今日の学習分のチェック
    $quizdata = getQuizData();
    if (count($quizdata) == 0) {
        //終了処理
    }
}

//
// 処理用関数部
//

//当日分までのクイズの配列を返します
function getQuizData()
{
    global $pdo, $dns, $db, $today, $_SESSION, $userdata;
    //当日までの復習カードを取得します
    $stmt = $pdo->prepare("select id, linked_id from quizes left join (select userid, cardid, expierdate from usercarddata where userid=? and expierdate <= ?) as t2 on quizes.id = t2.cardid where t2.expierdate is not null LIMIT 0, 1000");
    $stmt->execute(array($_SESSION["ID"], $today->format("Y-m-d")));
    $ret = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //最終アクセスと比較して、当概日の新規アクセスの場合、場合によって新規カードを付与します
    if ($userdata["autoadd"] && $userdata["addcount"] > 0 && ($userdata["lastaccessed"] == null || $today < datetime2timestamp($userdata["lastaccessed"]))) {
        $stmt = $pdo->prepare("SELECT quizes.id ,quizes.linked_id  from quizes left join (select cardid from usercarddata where userid=?)as limitedtable  on quizes.id = limitedtable.cardid where limitedtable.cardid is null LIMIT 0, 1000");
        $stmt->execute(array($_SESSION["ID"]));
        while ($row2 = $stmt->fetch(PDO::FETCH_ASSOC)) {
            //関連するカードの除外
            $flag = false;
            foreach ($ret as $row) {
                if ($row2["linked_id"] == $row["linked_id"]) {
                    $flag = true;
                    break;
                }
            }
            if (!$flag) {
                $row2["newflag"] = true;
                $ret[] = $row2;
                $userdata["addcount"]--;
            }
            if ($userdata["addcount"] <= 0) break;
        }
    }
    return $ret;
}

//問題データをデータベースから読み取り、JSON形式で読み取らせます

function readSQLQuizdata()
{
    global $pdo, $quizdata;
    try {
        $inclause = "0";
        foreach ($quizdata as $quiz) {
            $inclause .= ("," . (string) ($quiz["id"]));
        }
        $stmt = $pdo->prepare("select * from quizes where id in ($inclause) ORDER BY RAND()");
        $stmt->execute();
        $str = "";
        echo "var quizes = [";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($str != "") echo $str . "\n";
            $flag = false;
            //newflagが立っているかを調べる
            foreach ($quizdata as $quiz) {
                if ($quiz["id"] == $row["id"]) {
                    if (isset($quiz["newflag"]) && $quiz["newflag"] = true) {
                        $flag = true;
                    }
                    break;
                }
            }
            if (!isset($row["compliment"])) $row["compliment"] = "";
            $str = sprintf("[%d,'%d.mp3','%s','%s','%s',,,,%d,,,%d,'%s',],", $row["quiztype"], $row["soundfile"], $row["kanji"], $row["hiragana"], $row["vietnamese"], $row["id"], $flag ? 0 : 1, $row["complement"]);
        }
        echo substr($str, 0, -1) . "];";
        $str = substr($str, 0, -1);
    } catch (PDOException $e) {
        $errorMessage = 'データベースエラー';
        //$errorMessage = $sql;
        //$e->getMessage() でエラー内容を参照可能（デバッグ時のみ表示）
        echo $e->getMessage();
    }
}

function datetime2timestamp($datetime)
{
    //PHPのタイムスタンプをMySQLのdatetime型に変換。
    $re = "/(\d+)-(\d+)-(\d+)/";
    if (preg_match($re, $datetime, $m)) {
        return mktime(23, 59, 59, $m[2], $m[3], $m[1]);
    }
    return "";
}

function readLocalQuizdata()
{
    echo "var quizes=JSON.parse(localStorage.getItem('quizdata'));";
}

//
// HTML 部
//
?>



<!DOCTYPE html>

<html lang="en" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="utf-8" />
    <title></title>
    <link rel="stylesheet" type="text/css" href="./css/reset.css">
    <link rel="stylesheet" type="text/css" href="./css/sans-selif.css">
    <link rel="stylesheet" type="text/css" href="./css/base.css">
    <link rel="stylesheet" type="text/css" href="./css/loader.css">
</head>

<body id="vocab_body" class="first-view">
    <div class="loaderbody" id="loaderbody">
        <div class="loader"></div>
    </div>
    <div id="vocab_topmenu">
        <!-- 上部コントロール-->
        <button type="button" id="menu_button" class="menu_button" value="0"><img src="image/hamburger.png" alt="menu" /></button>
        <spna id="time_left">残り12分</spna>
        <button type="button" id="undo_button" class="undo_button" value="0"><img src="image/undo.png" alt="undo" /></button>
    </div>
    <div id="vocab_progress">
        <!-- 進捗表示-->
        <span id="progress_retry">0</span>
        <span id="progress_review">0</span>
        <span id="progress_new">0</span>
    </div>
    <div id="vocab_main">
        <!-- メインパネル-->
        <span id="question_text">question</span>
        <!--問題文表示部-->
        <button type="button" id="question_sound_button" class="sound_button" onclick="playsound(this.id)">
            <image src="image/speaker.png" alt="play" /></button>
        <br />
        <div id="answer_container">
            <!-- 解答表示部-->
            <hr />
            <span id="answer_text">answer</span>
            <button type="button" id="answer_sound_button" class="sound_button"><img src="image/speaker.png" alt="menu" /></button>
            <br />
            <span id="answer2_text">answer2</span>
            <button type="button" id="answer2_sound_button" class="sound_button"><img src="image/speaker.png" alt="menu" /></button>
        </div>
    </div>
    <div id="vocab_button">
        <!-- ボタン表示-->
        <button type="button" id="answer_button" class="answer_button" value="0" onclick="onAnswer(this.value)">始める</button>
        <button type="button" id="rank_button_fail" class="answer_button" value="0" onclick="onRank(this.value)">もう一度</button>
        <button type="button" id="rank_button_difficult" class="answer_button" value="3" onclick="onRank(this.value)">難しい</button>
        <button type="button" id="rank_button_pass" class="answer_button" value="4" onclick="onRank(this.value)">普通</button>
        <button type="button" id="rank_button_easy" class="answer_button" value="5" onclick="onRank(this.value)">簡単</button>


    </div>


    <script type="text/javascript">
        var reg = /iPhone|iPod|iPad|Android/i;

        var size = window.innerHeight;
        var audio_question = new Audio();
        var audio_answer = new Audio();
        var audio_answer2 = new Audio();
        var quizlength;
        var currentquizcount;
        var newcount = 0;
        var reviewcount = 0;
        var retrycount = 0;
        var finalflag = false;
        var timestart;
        var timeelaps
        const enumquiz = {
            type: 0,
            soundfile: 1,
            kanji: 2,
            hiragana: 3,
            vietnamese: 4,
            form: 5,
            politeform: 6,
            politeformsoundfile: 7,
            quizid: 8,
            timesolved: 9,
            rank: 10,
            newflag: 11,
            compliment: 12,
            timespend: 13
        };

        //type 1=nhat-viet 2=v-j 3 j listening
        //newflag 0=new 1=review
        //クイズデータ作成
        // var quizes=[];
        <?php
        if (!$hascookie) {
            readSQLQuizdata();
        } else {
            readLocalQuizdata();
        }
        if ($reload) echo "var reload=true;";
        else echo "var reload=false;"; //リロードが必要かのフラグ
        ?>


        //
        //イベントハンドラ
        //

        //ディスプレイ整形
        reg.test(navigator.userAgent) && document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll(':root')[0].style.height = `${size}` + 'px'
        })
        window.addEventListener('DOMContentLoaded', function() {

            quizlength = quizes.length;
            currentquizcount = 0;
            document.getElementById("rank_button_fail").style.display = "none";
            document.getElementById("rank_button_difficult").style.display = "none";
            document.getElementById("rank_button_pass").style.display = "none";
            document.getElementById("rank_button_easy").style.display = "none";
            document.getElementById("vocab_main").style.display = "none";

        });

        window.addEventListener('load', (event) => {
            if (reload) {
                onReload();
                return;
            }

            for (let i = 0; i < quizlength; i++) {
                if (quizes[i][enumquiz.timesolved] != null) {
                    currentquizcount++;
                    continue;
                }
                if (quizes[i][enumquiz.newflag] == 0) {
                    newcount++;
                } else if (quizes[i][enumquiz.newflag] == 1) {
                    reviewcount++;
                } else {
                    retrycount++;
                }

            }
            showprogress();
        });

        window.addEventListener("beforeunload", (event) => {
            if (finalflag) { //完全に終了の場合
                document.cookie = "quizdatadate=; max-age=0";
            } else if (reload) {
                //リロードの場合は何もしない
            } else { //途中で終了の場合
                var dt = new Date();
                dt.setHours(dt.getHours() - 4);
                document.cookie = "quizdatadate=" + dt.getFullYear() + "-" + (dt.getMonth() + 1) + "-" + dt.getDate() + "; max-age=31536000";
                try {
                    localStorage.setItem("quizdata", JSON.stringify(quizes));
                } catch (e) {
                    alert(e);
                    event = event || window.event;
                    event.returnValue = e;
                }

            }

        });

        //解答済みの問題をストレージから削除し、テーブルに反映。その後リロードを行う
        function onReload() {
            var save = [];
            var send = [];
            quizes.forEach((quiz) => {
                if (quiz[enumquiz.timesolved] == null) {
                    save.push(quiz.slice());
                } else {
                    send.push(quiz.slice());
                }
            });
            localStorage.setItem("quizdata", JSON.stringify(save));
            var dt = new Date();
            dt.setHours(dt.getHours() + 3);
            document.cookie = "quizdatadate=" + dt.getFullYear() + "-" + (dt.getMonth() + 1) + "-" + dt.getDate() + "; max-age=31536000";
            quizes = send.slice();
            postform();
        }


        //post
        function postform() {
            loader(); //ローダー表示
            var data = JSON.stringify(quizes);
            var xmlHttpRequest = new XMLHttpRequest();
            xmlHttpRequest.onreadystatechange = function() {
                var READYSTATE_COMPLETED = 4;
                var HTTP_STATUS_OK = 200;
                if (this.readyDtate == READYSTATE_COMPLETED && this.status == HTTP_STATUS_OK) {
                    alert(this.responseText);
                }
            }
            xmlHttpRequest.onload = function() {
                var res = xmlHttpRequest.responseText;
                if (res.length > 0) alert(res);
                document.location.reload();
            }
            xmlHttpRequest.onerror = function() {
                alert("send error!");
            }
            xmlHttpRequest.open('POST', 'quiztransact.php', true);
            xmlHttpRequest.setRequestHeader('Content-Type', 'application/json');
            xmlHttpRequest.send(data);
            if (reload) {
                document.location.reload();
            } else {
                document.location.href = "result.php";
            }
        }

        function loadloader() {
            document.getElementById("loaderbody").style.display = "flex";
            document.getElementById("loaderbody").style.zIndex = 21474836479;
        }

        //
        //問題操作部
        //

        function showprogress() {
            document.getElementById("progress_new").innerHTML = newcount;
            document.getElementById("progress_new").style.textDecoration = "none";
            document.getElementById("progress_review").innerHTML = reviewcount;
            document.getElementById("progress_review").style.textDecoration = "none";
            document.getElementById("progress_retry").innerHTML = retrycount;
            document.getElementById("progress_retry").style.textDecoration = "none";
            var time = Math.round((newcount * 30 + (reviewcount + retrycount) * 8) / 60);
            document.getElementById("time_left").innerHTML = `あと${time}分`;

        }

        function createquiz() {
            if (quizlength === currentquizcount) {
                //終了処理へ
                finalflag = true;
                return;
            }
            showquiz();

        };


        function showquiz() {
            var quiz = quizes[currentquizcount];
            document.getElementById("answer_container").style.display = "none";
            switch (quiz[enumquiz.type]) {
                case 1: //simple j-v
                    document.getElementById("question_text").innerHTML = `${quiz[enumquiz.kanji]} <br /><span class="kana">(${quiz[enumquiz.hiragana]})</span><br>${quiz[enumquiz.compliment]}`;
                    document.getElementById("question_sound_button").style.display = "inline-block";
                    audio_question.src = "./sound/" + quiz[enumquiz.soundfile];
                    document.getElementById("answer_text").innerHTML = quiz[enumquiz.vietnamese];
                    document.getElementById("answer_sound_button").style.display = "none";
                    document.getElementById("answer2_text").innerHTML = "";
                    document.getElementById("answer2_sound_button").style.display = "none";
                    playsound("question_sound_button");
                    break;
                case 2: //simple v-n
                    document.getElementById("question_text").innerHTML = quiz[enumquiz.vietnamese];
                    document.getElementById("question_sound_button").style.display = "none";
                    document.getElementById("answer_text").innerHTML = `${quiz[enumquiz.kanji]} <br /><span class="kana">(${quiz[enumquiz.hiragana]})</span>`;
                    audio_answer.src = "./sound/" + quiz[enumquiz.soundfile];
                    document.getElementById("answer_sound_button").style.display = "inline-block";
                    document.getElementById("answer2_text").style.display = "none";
                    document.getElementById("answer2_sound_button").style.display = "none";
                    break;
                case 3: //listening j-v
                    document.getElementById("question_text").innerHTML = `${quiz[enumquiz.compliment]}`;
                    document.getElementById("question_sound_button").style.display = "inline-block";
                    audio_question.src = "./sound/" + quiz[enumquiz.soundfile];
                    document.getElementById("answer_text").innerHTML = `${quiz[enumquiz.kanji]} <br /><span class="kana">(${quiz[enumquiz.hiragana]})</span>`;
                    audio_answer.src = "./sound/" + quiz[enumquiz.soundfile];
                    document.getElementById("answer_sound_button").style.display = "inline-block";
                    document.getElementById("answer2_text").innerHTML = quiz[enumquiz.vietnamese];
                    document.getElementById("answer2_text").style.display = "inline-block"
                    document.getElementById("answer2_sound_button").style.display = "none";
                    playsound("question_sound_button");
                    break;

                default:
                    break;
            }
            document.getElementById("rank_button_fail").style.display = "none";
            document.getElementById("rank_button_difficult").style.display = "none";
            document.getElementById("rank_button_pass").style.display = "none";
            document.getElementById("rank_button_easy").style.display = "none";
            document.getElementById("answer_button").style.display = "block";

            switch (quiz[enumquiz.newflag]) {
                case 0:
                    document.getElementById("progress_new").style.textDecoration = "underline";
                    break;
                case 1:
                    document.getElementById("progress_review").style.textDecoration = "underline";
                    break;
                case 2:
                    document.getElementById("progress_retry").style.textDecoration = "underline";
                    break;
                default:
                    break;
            }
            //解答時間測定脳のタイマー取得
            timestart = (new Date()).getTime();
        }


        function playsound(obj) {
            switch (obj) {
                case "question_sound_button":
                    audio_question.pause();
                    audio_question.currentTime = 0;
                    audio_question.play();
                    break;
                case "answer_sound_button":
                    audio_answer.pause();
                    audio_answer.currentTime = 0;
                    audio_answer.play();
                    break;

            }
        }

        function onAnswer(value) {
            //初回の操作は問題の開始ボタン
            if (value == 0) {
                document.getElementById("vocab_main").style.display = "block";
                document.getElementById("answer_button").innerHTML = "解答を表示";
                document.getElementById("answer_button").value = "1";
                createquiz();
                return;
            }

            //タイム計測
            let t = new Date();
            timeelaps = t.getTime() - timestart;
            let quiz = quizes[currentquizcount];
            quiz[enumquiz.timespend] = timeelaps;
            quiz[enumquiz.timesolved] = t.toISOString().slice(0, 19).replace('T', ' ');

            document.getElementById("answer_container").style.display = "block";
            document.getElementById("rank_button_fail").style.display = "block";
            document.getElementById("rank_button_difficult").style.display = "block";
            if (quizes[currentquizcount][enumquiz.newflag] != 0) document.getElementById("rank_button_pass").style.display = "block";
            document.getElementById("rank_button_easy").style.display = "block";
            document.getElementById("answer_button").style.display = "none";
            if (document.getElementById("answer_sound_button").style.display == "inline-block") playsound("answer_sound_button");

        }

        function onRank(value) {
            let quizold = quizes[currentquizcount];
            let quiz = Array.from(quizes[currentquizcount]);
            quiz[enumquiz.timesolved] = null;
            quiz[enumquiz.rank] = null;
            let newflag_old = quiz[enumquiz.newflag];
            if (value == 0) { //agein
                quiz[enumquiz.newflag] = 2;
                quizes.splice(insertpos(), 0, quiz);
                retrycount++;
                quizlength++;
            } else {
                if (quiz[enumquiz.newflag] == 0 && value == 3) {
                    quiz[enumquiz.newflag] = 1;
                    quizes.splice(insertpos(), 0, quiz);
                    reviewcount++;
                    quizlength++;
                }
            }
            let d = new Date();
            quizold[enumquiz.timesolved] = d.getTime();
            quizold[enumquiz.rank] = value;
            switch (newflag_old) {
                case 0:
                    newcount--;
                    break;
                case 1:
                    reviewcount--;
                    break;
                case 2:
                    retrycount--;
                    break;
                default:
                    break;
            }
            currentquizcount++;
            showprogress();
            createquiz();
        }

        function insertpos() {
            return currentquizcount + 1 + Math.floor((Math.random() + 1) * ((quizlength) - currentquizcount) / 2);
        }
    </script>
</body>

</html>