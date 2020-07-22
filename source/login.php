<?php
//require 'password.php';   // password_verfy()はphp 5.5.0以降の関数のため、バージョンが古くて使えない場合に使用
// セッション開始 newverjone
session_start();
require_once 'common.php';
require_once 'database.php';
$dbuser = new DBUser();
$errorMessage = "";
$userid = $_POST["userid"];
$userdata = $dbuser->authentication($_POST["userid"], $_POST["password"]);
if ($userdata != "") {
    if ($userdata->isRegisterd()) {
        $_SESSION["USER"] = $userdata;
        header("Location: index.php");  // メイン画面へ遷移
        exit();  // 処理終了
    } else {
        $_SESSION["USER"] = $userdata;
        header("Location: login2.php");  // メイン画面へ遷移
        exit();  // 処理終了
    }
} else {
    $errorMessage = $userdata->$errorMessage;
}

?>

<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <title><?php _L('ログイン', 'Đăng nhập'); ?></title>
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

            input:invalid {
                background-color: pink;
            }
        }
    </style>
</head>

<body>
    <h1><?php _L('ログイン', 'Đăng nhập'); ?></h1>
    <form id="loginForm" name="loginForm" action="" method="POST" onSubmit="return Onsubmit()">
        <fieldset>
            <legend><?php _L('ログインフォーム', 'Mẫu đăng nhập'); ?></legend>
            <div>
                <font color="#ff0000"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?></font>
            </div>
            <table>
                <tr>
                    <th><label for="userid"><?php _L('ユーザーID', 'Tên tài khoản') ?></label></th>
                    <td><input type="text" id="userid" name="userid" placeholder="<?php _L('ユーザーIDを入力', 'Nhập tên tài khoản') ?>" autocapitalize="off" pattern='^.+$' value="<?php if (!empty($_POST["userid"])) {
                                                                                                                                                                                    echo htmlspecialchars($_POST["userid"], ENT_QUOTES);
                                                                                                                                                                                } ?>"></td>
                </tr>
                <tr>
                    <th><label for="password"><?php _L('パスワード', 'Mật khẩu') ?></label></th>
                    <td><input type="password" id="password" name="password" pattern='^.+$' value="" placeholder="<?php _L('パスワードを入力', 'Nhập mật khẩu') ?>"></td>
                </tr>
            </table>
            <input type="submit" id="login" name="login" value="<?php _L('送信', 'Gửi') ?>">
        </fieldset>
    </form>

    <!-- script  -->
    <script src="../js/loader.js"></script>
    <script type="text/javascript">
        var loader = new Loader();

        function onSubmit() {
            if (document.getElementById("userid") === "") {
                alert("<?php _L('ユーザーIDを入力してください', 'Nhập tên tài khoản') ?>")
                return false;
            }
            if (document.getElementById("password") === "") {
                alert("<?php _L('パスワードを入力してください', 'Nhập mật khẩu') ?>")
                return false;
            }
            loader.start();
            return true;
        }
    </script>

</body>

</html>