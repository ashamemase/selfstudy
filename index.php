<?php
session_start();

// ログイン状態チェック
if (!isset($_SESSION["NAME"])) {
    header("Location: Login.php");
    exit;
}
?>

<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="./css/loader.css">
    <link rel="stylesheet" type="text/css" href="./css/mainbase.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0,minimum-scale=1.0">
    <title>メイン</title>
</head>

<body>
    <div class="loaderbody" id="loaderbody">
        <div class="loader"></div>
    </div>
    <h1>メイン画面</h1>
    <!-- ユーザーIDにHTMLタグが含まれても良いようにエスケープする -->
    <p>ようこそ<u><?php echo htmlspecialchars($_SESSION["NAME"], ENT_QUOTES); ?></u>さん</p> <!-- ユーザー名をechoで表示 -->
    <p>あなたのアクセスレベルは<u><?php echo htmlspecialchars($_SESSION["LEVEL"], ENT_QUOTES); ?></u>です</p>
    <ul>
        <?php
        if ($_SESSION["LEVEL"] == "X") {
            echo "<li><a href='schooledit.php'>学校の編集</a></li>";
        }
        if ($_SESSION["LEVEL"] == "A") {
            echo "<li><a href='teacheredit.php'>教師の編集</a></li>";
        }
        if ($_SESSION["LEVEL"] == "B") {
            echo "<li><a href='classedit.php'>クラスの編集</a></li>";
        }
        if ($_SESSION["LEVEL"] == "C") {
            echo "<li><a href='quiz.php' onClick='loadloader()'>クイズの開始</a></li>";
        }
        ?>
        <li><a href="logout.php">ログアウト</a></li>
    </ul>

    <script type="text/javascript">
        function loadloader() {
            document.getElementById("loaderbody").style.display = "flex";
            document.getElementById("loaderbody").style.zIndex = 21474836479;
        }
    </script>
</body>

</html>