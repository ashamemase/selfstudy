<?php
//require 'password.php';   // password_hash()はphp 5.5.0以降の関数のため、バージョンが古くて使えない場合に使用
// セッション開始
session_start();
require_once 'common.php';
require_once 'database.php';
require_once 'wordquiz.php';

$dbuser = new DBUser();
$wordquiz;
$userid = $_SESSION["USER"]["id"];

if (!isset($_SESSION['USER'])) {
    header("Location: Login.php");
    exit;
}
// エラーメッセージ、登録完了メッセージの初期化
$errorMessage = "";
$signUpMessage = "";

if (!isset($_POST["signUp"])) {
    $username = $_POST["username"];
    try {
        if (!$dbuser->Register($userid, $_POST["username"], $_POST["password"]))
            throw new Exception(($dbuser->$errorMessage));
        $user = new Userdata($dbuser->getRowFromID($userid));
        if ($user->hasQuizAccess) {
            $wordquiz = new WordQuiz($userid);
            $wordquiz->initializeWordQuiz();
        }
        $signUpMessage = _L(
            '登録が完了しました。あなたのユーザー名は ' . $username . ' です。パスワードは ' . $_POST["password"] . ' です。',
            'Đăng ký đã được hoàn thành. Tên tài khoản là ' . $username . ' . Mật khẩu là ' . $_POST["password"]
        );
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

//学校名、教師名取得
$belongs = "";
if ($row = $dbuser->getRowFromID($_SESSION["USER"]["schoolid"]))
    $belongs = _L("学校名: ", "Tên trung tâm: ") . $row["name"] . "\n";
if ($row = $dbuser->getRowFromID($_SESSION["USER"]["teacherid"]))
    $belongs = $belongs . _L("教師名: ", "Tên giáo viên: ") . $row["name"];
?>

<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <title>新規登録</title>
    <link rel="stylesheet" type="text/css" href="./css/loader.css">
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
    </div>
    <h1>新規登録画面</h1>
    <h2><?php echo htmlspecialchars($belongs, ENT_QUOTES); ?><h2>
            <form id="loginForm" name="loginForm" action="" method="POST">
                <fieldset>
                    <legend>新規登録フォーム</legend>
                    <div>
                        <font color="#ff0000"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?></font>
                    </div>
                    <div>
                        <font color="#0000ff"><?php echo htmlspecialchars($signUpMessage, ENT_QUOTES); ?></font>
                    </div>
                    <table>
                        <tr>
                            <th><label for="username">ユ<?php _L("新しいユーザー名", "Tên tài khoản mới") ?></label></th>
                            <td><input type="text" id="username" name="username" placeholder=<?php _L("新しいユーザー名を入力", "Nhập tên tài khoản mới") ?>"ユーザー名を入力" autocapitalize="none" value="<?php if (!empty($_POST["username"])) {
                                                                                                                                                                                                echo htmlspecialchars($_POST["username"], ENT_QUOTES);
                                                                                                                                                                                            } ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="password"><?php _L('パスワード', 'Mật khẩu') ?></label></th>
                            <td><input type="password" id="password" name="password" value="" placeholder="<?php _L('パスワードを入力', 'Nhập mật khẩu') ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="password2"><?php _L('パスワード（確認用）', 'Mật khẩu (Xác nhận)') ?></label></th>
                            <td><input type="password" id="password2" name="password2" value="" placeholder="<?php _L('パスワードを再入力', 'Nhập lại mật khẩu') ?>"></td>
                        </tr>
                    </table>
                    <input type="submit" id="signUp" name="signUp" value="<?php _L("新規登録", "Đăng ký") ?>" onsubmit="return onSubmit()">
                </fieldset>
            </form>
            <br>
            <form action="Login.php">
                <input type="submit" value="戻る">
            </form>
            <script src="../js/loader.js"></script>

            <script>
                var loader = new loader();

                function onSubmit() {
                    if (document.getElementById("username") === "") {
                        alert("<?php _L('ユーザー名を入力してください', 'Nhập tên tài khoản') ?>");
                        return false;
                    }
                    if (document.getElementById("password") === "") {
                        alert("<?php _L('パスワードを入力してください', 'Nhập mật khẩu') ?>");
                        return false;
                    }
                    if (document.getElementById("password") !== document.getElementById("password2")) {
                        alert("<?php _L('確認用パスワードが違います', 'Lỗi mật khẩu xác nhận') ?>");
                        return false;
                    }
                    loader.start();
                    return true;
                }
            </script>
</body>

</html>