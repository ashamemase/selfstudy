<?php
//require 'password.php';   // password_verfy()はphp 5.5.0以降の関数のため、バージョンが古くて使えない場合に使用
// セッション開始
session_start();

$db['host'] = "localhost";  // DBサーバのURL
$db['user'] = "studyjapanese";  // ユーザー名
$db['pass'] = "m1234567890";  // ユーザー名のパスワード
$db['dbname'] = "selfstudy";  // データベース名

// エラーメッセージの初期化
$errorMessage = "";

//今日の日付取得
$today = new DateTime(); //今日の日付
$today->modify("+3 hour"); //翌日の4時までは前日扱い(+ベトナムタイムゾーン7)

//post取得
$headers = getallheaders();
$post_body = file_get_contents('php://input');
echo "body" . $post_body;
$arr = json_decode($post_body, true);

//データベース設定
$dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);

//テーブルに変更を加えます
$enum = array(
    'type' => 0, 'soundfile' => 1, 'kanji' => 2, 'hiragana' => 3, 'vietnamese' => 4, 'form' => 5, 'politeform' => 6,
    'politeformsoundfile' => 7, 'quizid' => 8, 'timesolved' => 9, 'rank' => 10, 'newflag' => 11, 'compliment' => 12,
    'timespend' => 13
);

try {
    $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    foreach ($arr as $key => $value) {
        $stmt = $pdo->prepare("select * from usercarddata where userid=? and cardid=?");
        $stmt->execute(array($_SESSION["ID"], $value[$enum["quizid"]]));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row == "") continue;
        //新規カード
        if ($row["expierdate"] == "") $newcard = true;
        else $newcard = false;
        //難易度別判定
        switch ($value[$enum["rank"]]) {
            case 0: //retry
                if (!$newcard) {
                    $row["expierdate"] = $today->format("Y-m-d");
                    $row["efficiency"] -= 20;
                    $row["efficiency"] = $row["efficiency"] < 130 ? 130 : $row["efficiency"];
                    $row["interval"] = 1;
                }
                break;
            case 3: //difficult
                if (!$newcard) {
                    $row["efficiency"] -= 15;
                    $row["efficiency"] = $row["efficiency"] < 130 ? 130 : $row["efficiency"];
                    $blank = ($today->diff(new Datetime($row["expierdate"])))->d;
                    $blank *= 0.25;
                    $newinterval = floor(($row["interval"] + $blank) * 1.2);
                    if ($newinterval < 1) $newinterval = 1;
                    $row["interval"] = $newinterval;
                    $row["expierdate"] = ($today->modify("+$row[interval] day"))->format("Y-m-d");
                } else {
                    $row["efficiency"] -= 15;
                    $row["efficiency"] = $row["efficiency"] < 130 ? 130 : $row["efficiency"];
                    $row["interval"] = 1;
                    $row["expierdate"] = ($today->modify("+$row[interval] day"))->format("Y-m-d");
                }
                break;
            case 4: //normal
                if (!$newcard) {
                    $blank = ($today->diff(new Datetime($row["expierdate"])))->d;
                    $blank *= 0.5;
                    $newinterval = floor(($row["interval"] + $blank) * $row["efficiency"] / 100);
                    if ($newinterval < 1) $newinterval = 1;
                    $row["interval"] = $newinterval;
                    $row["expierdate"] = ($today->modify("+$row[interval] day"))->format("Y-m-d");
                } else {
                    $row["interval"] = 1;
                    $row["expierdate"] = ($today->modify("+$row[interval] day"))->format("Y-m-d");
                }
                break;
            case 5: //easy
                if (!$newcard) {
                    $row["efficiency"] += 15;
                    $blank = ($today->diff(new Datetime($row["expierdate"])))->d;
                    $newinterval = floor(($row["interval"] + $blank) * $row["efficiency"] / 100 * 1.3);
                    $row["interval"] = $newinterval;
                    $row["expierdate"] = ($today->modify("+$row[interval] day"))->format("Y-m-d");
                } else {
                    $row["efficiency"] += 15;
                    $row["interval"] = 3;
                    $row["expierdate"] = ($today->modify("+$row[interval] day"))->format("Y-m-d");
                }
                break;
        }

        //個人カード情報をアップデートします
        if (!$newcard || $value[$enum["rank"]] >= 3) {
            $stmt = $pdo->prepare("update usercarddata set expierdate = ?, `interval`= ?, efficiency=? where userid=? and cardid=?");
            $stmt->execute(array($row["expierdate"], $row["interval"], $row["efficiency"], $_SESSION["ID"], $value[$enum["quizid"]]));
        }
        //ログを記録します

        $stmt = $pdo->prepare("insert into log (userid, cardid ,`rank`, timesolved, timespend, newflag) values (?,?,?,FROM_UNIXTIME(?),?,?)");
        $stmt->execute(array(
            $_SESSION["ID"], $value[$enum["quizid"]], $value[$enum["rank"]],
            floor($value[$enum["timesolved"]] / 1000), $value[$enum["timespend"]], $value[$enum["newflag"]]
        ));
    }
    //統計処理
    $stmt = $pdo->prepare("select avg(efficiency) from usercarddata where userid=? and `interval` >= 0");
    $stmt->execute(array($_SESSION["ID"]));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $ave = $row["avg(efficiency)"];
    //autoaddがある場合、addcountを設定
    $stmt = $pdo->prepare(("select autoadd from userdata where id=?"));
    $stmt->execute(array($_SESSION["ID"]));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row["autoadd"]) $addcount = floor(($ave - 200) / 3 + 40);
    else $addcount = 0;
    if ($addcount > 40) $addcount = 40;
    $stmt = $pdo->prepare("update userdata set efficiency=?, addcount=? where id=?");
    $stmt->execute(array($ave, $addcount, $_SESSION["ID"]));
} catch (PDOException $e) {
    $errorMessage = 'データベースエラー';
    //$errorMessage = $sql;
    //$e->getMessage() でエラー内容を参照可能（デバッグ時のみ表示）
    echo $e->getMessage();
}

/*//リロード処理はここまで
if ($_GET["reload"] == 1) {
    header("Location: quiz.php");
    exit;
}

//正答率
$result = array_filter($arr, function ($v, $k) {
    global $enum, $arr;
    return (array_search($v[$enum["quizid"]], array_column($arr, "quizid")) == $k) && $v[$enum["newflag"]] == false;
}, ARRAY_FILTER_USE_BOTH);
$count = array_count_values(array_column($result, "rank"));
$score = floor((count($result) - count($count["0"])) / count($result) * 100);*/




function datetime2timestamp($datetime)
{
    //PHPのタイムスタンプをMySQLのdatetime型に変換。
    $re = "/(\d+)-(\d+)-(\d+)/";
    if (preg_match($re, $datetime, $m)) {
        return mktime(23, 59, 59, $m[2], $m[3], $m[1]);
    }
    return "";
}
//	header("Location:index.php");

/*
<!DOCTYPE html>

<html lang="en" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0,minimum-scale=1.0">
    <title></title>


</head>

<body>
    <h1>お疲れさまでした</h1>
    <p>今日の勉強は終了しています</p>
    <table width="100%">
        <th>今日の成績は</th>
        <tr>
            <td align="center"><?php echo $score ?>点です</td>
        </tr>
    </table>
    <a href="index.php">サイトトップへ戻る</a>

</body>*/
