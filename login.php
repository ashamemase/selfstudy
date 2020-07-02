<?php
//require 'password.php';   // password_verfy()はphp 5.5.0以降の関数のため、バージョンが古くて使えない場合に使用
// セッション開始
session_start();

$db['host'] = "localhost";  // DBサーバのURL
$db['user'] = "studyjapanese";  // ユーザー名
$db['pass'] = "m1234567890";  // ユーザー名のパスワード
$db['dbname'] = "login_management";  // データベース名

// エラーメッセージの初期化
$errorMessage = "";

// ログインボタンが押された場合
if (isset($_POST["login"])) {
    // 1. ユーザIDの入力チェック
    if (empty($_POST["userid"])) {  // emptyは値が空のとき
        $errorMessage = 'ユーザーIDが未入力です。';
    } else if (empty($_POST["password"])) {
        $errorMessage = 'パスワードが未入力です。';
    }

    if (!empty($_POST["userid"]) && !empty($_POST["password"])) {
        // 入力したユーザIDを格納
        $userid = $_POST["userid"];

        // 2. ユーザIDとパスワードが入力されていたら認証する
        $dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);

        // 3. エラー処理
        try {
            $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

            $stmt = $pdo->prepare('SELECT * FROM userData WHERE name = ?');
            $stmt->execute(array($userid));

            $password = $_POST["password"];

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                //仮登録用のIDの場合
                if (preg_match("/^=.+$/", $userid)) {
                    if ($password == $row['password']) {
                        session_regenerate_id(true);
                        // 入力したIDのユーザーデータを取得
                        $_SESSION["ID"] = $row['id'];
                        header("Location: specialsignup.php");  // メイン画面へ遷移
                        exit();  // 処理終了

                    } else {
                        // 認証失敗
                        $errorMessage = 'ユーザーIDあるいはパスワードに誤りがあります。';
                    }
                } else {
                    if (password_verify($password, $row['password'])) {
                        session_regenerate_id(true);

                        // 入力したIDのユーザーデータを取得
                        $id = $row['id'];
                        $sql = "SELECT * FROM userData WHERE id = $id";  //入力したIDからユーザーデータを取得
                        $stmt = $pdo->query($sql);
                        foreach ($stmt as $row) {
                            $row['name'];  // ユーザー名
                            $row['accesslevel']; //アクセスレベル
                            $row['schoolid'];
                            $row['teacherid'];
                        }
                        $_SESSION["ID"] = $row['id'];
                        $_SESSION["SUBID"] = $row['subid'];
                        $_SESSION["NAME"] = $row['name'];
                        $_SESSION["LEVEL"] = $row['accesslevel'];
                        $_SESSION["SCHOOLID"] = $row['schoolid'];
                        $_SESSION["TEACHERID"] = $row['teacherid'];
                        if ($_SESSION["LEVEL"] == "A" || $_SESSION["LEVEL"] == "B") $_SESSION["TEACHERID"] = $row['id'];
                        header("Location: index.php");  // メイン画面へ遷移
                        exit();  // 処理終了
                    } else {
                        // 認証失敗
                        $errorMessage = 'ユーザーIDあるいはパスワードに誤りがあります。';
                    }
                }
            } else {
                // 4. 認証成功なら、セッションIDを新規に発行する
                // 該当データなし
                $errorMessage = 'ユーザーIDあるいはパスワードに誤りがあります。';
            }
        } catch (PDOException $e) {
            $errorMessage = 'データベースエラー';
            //$errorMessage = $sql;
            // $e->getMessage() でエラー内容を参照可能（デバッグ時のみ表示）
            // echo $e->getMessage();
        }
    }
}
?>

<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <title>ログイン</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0,minimum-scale=1.0">
    <style>
        @media screen and (max-width: 480px) {
            table {
                width: 100%;
            }

            table th,
            table td {
                display: block;
                text-align: left;
                width: 100%;
            }

            input {
                size: 80;
            }

            input[type="text"],
            textarea {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="loaderbody">
        <div class="loader"></div>
    </div>
    <h1>ログイン画面</h1>
    <form id="loginForm" name="loginForm" action="" method="POST" onSubmit="loadloader()">
        <fieldset>
            <legend>ログインフォーム</legend>
            <div>
                <font color="#ff0000"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?></font>
            </div>
            <table>
                <tr>
                    <th><label for="userid">ユーザーID</label></th>
                    <td><input type="text" id="userid" name="userid" placeholder="ユーザーIDを入力" autocapitalize="off" value="<?php if (!empty($_POST["userid"])) {
                                                                                                                                echo htmlspecialchars($_POST["userid"], ENT_QUOTES);
                                                                                                                            } ?>"></td>
                </tr>
                <tr>
                    <th><label for="password">パスワード</label></th>
                    <td><input type="password" id="password" name="password" value="" placeholder="パスワードを入力"></td>
                </tr>
            </table>
            <input type="submit" id="login" name="login" value="ログイン">
        </fieldset>
    </form>
    <br>
    <!--       <form action="signup.php">
            <fieldset>          
                <legend>新規登録フォーム</legend>
                <input type="submit" value="新規登録">
            </fieldset>
        </form>
            -->
    <script type="text/javascript">
        function loadloader() {
            document.getElementById("loaderbody").style.display = "flex";
            document.getElementById("loader").style.zindex = 21474836479;
        }
    </script>

</body>

</html>