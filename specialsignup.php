<?php
//require 'password.php';   // password_hash()はphp 5.5.0以降の関数のため、バージョンが古くて使えない場合に使用
// セッション開始
session_start();

// ログイン状態チェック
if (!isset($_SESSION["ID"])) {
	header("Location: Login.php");
	exit;
}

$db['host'] = "localhost";  // DBサーバのURL
$db['user'] = "studyjapanese";  // ユーザー名
$db['pass'] = "m1234567890";  // ユーザー名のパスワード
$db['dbname'] = "login_management";  // データベース名
$db['studydbname'] = "selfstudy"; //学習用データベース名

$userid = $_SESSION["ID"];
// エラーメッセージ、登録完了メッセージの初期化
$errorMessage = "";
$signUpMessage = "";

// ログインボタンが押された場合
if (isset($_POST["signUp"])) {
	// 1. ユーザIDの入力チェック
	if (empty($_POST["username"])) {  // 値が空のとき
		$errorMessage = 'ユーザーIDが未入力です。';
	} else if (empty($_POST["password"])) {
		$errorMessage = 'パスワードが未入力です。';
	} else if (empty($_POST["password2"])) {
		$errorMessage = 'パスワードが未入力です。';
	}

	if (!empty($_POST["username"]) && !empty($_POST["password"]) && !empty($_POST["password2"]) && $_POST["password"] === $_POST["password2"]) {
		// 入力したユーザIDとパスワードを格納
		$username = $_POST["username"];
		$password = $_POST["password"];

		// 2. ユーザIDとパスワードが入力されていたら認証する
		$dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);

		// 3. エラー処理
		try {
			$pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
			$stmt = $pdo->query("select COUNT(*) from userData where name='$username'");
			if ($stmt->fetchColumn() == 0) {
				if (preg_match("/^=.+$/", $username)) {
					$errorMessage = 'ユーザー名の最初の文字は「=」以外にしてください。';
				} else {
					$stmt = $pdo->prepare("update userdata set name=? , password=? where id=?");

					$stmt->execute(array($username, password_hash($password, PASSWORD_DEFAULT), $userid));  // パスワードのハッシュ化を行う（今回は文字列のみなのでbindValue(変数の内容が変わらない)を使用せず、直接excuteに渡しても問題ない）
					//アクセスレベルに対して、初回データベース処理を行う
					$stmt = $pdo->query("select * from userdata where id=$userid");
					foreach ($stmt as $row) {
						$row["accesslevel"];
					}
					if ($row["accesslevel"] == "C") {
						$studydsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['studydbname']);
						$studypdo = new PDO($studydsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
						$stmt = $studypdo->query("select * from userdata where id=$userid");
						foreach ($stmt as $row) {
							$row["efficiency"];
							$row["addcount"];
							$row["lesson"];
							$row["autoadd"];
							$row["startday"];
						}
						$lesson = $row["lesson"];
						//autoaddがある場合、毎日の追加量を設定します
						if ($row["autoadd"]) {
							if ($row["efficiency"] >= 200) {
								$row["addcount"] = 40;
							} elseif ($row["efficiency"] >= 170) {
								$row["addcount"] = 30;
							} else {
								$row["addcount"] = 20;
							}
						} else {
							$row["addcount"] = 0;
						}
						$efficiency = $row["efficiency"];
						//クイズユーザーデータのアップデート
						$date = new DateTime();
						$date->modify("+3 hour"); //翌日の4時までは前日扱い(+ベトナムタイムゾーン7)
						$stmt = $studypdo->prepare("update userdata set addcount=? , startday=? where id=?");
						$stmt->execute(array($row["addcount"], $date->format("Y-m-d"), $userid));
						$addcount = $row["addcount"];
						//ユーザ用のクイズデータ作成
						//学習済みの課までのクイズデータをフェッチ
						$stmt = $studypdo->query("select * from quizes where lesson <= $row[lesson] order by schedule");
						$quizdata;
						$count = 0;
						foreach ($stmt as $row) {
							$quizdata[$count] = array($row["id"], $row["lesson"], $row["schedule"], $row["quiztype"]);
							$count++;
						}
						//インターバルを[4]に設定する
						for ($i = 0; $i < $count; $i++) {
							if ($lesson > 15) {
								$quizdata[$i][4] = floor(30 * ($lesson - $quizdata[$i][1]) / $lesson) + 1;
							} else {
								$quizdata[$i][4] = ($lesson - $quizdata[$i][1]) * 2 + 1;
							}
						}
						//期限を設定する
						//復習用空を確保する
						$dayarray = array();
						$daycount = 0;
						$cardcount = 0;
						$daycards = 400;
						while ($cardcount < $count) {
							$daycards -= $addcount;
							if ($daycards < 100) $daycards = 100;
							$dayarray[$daycount] = $daycards;
							$cardcount += $daycards;
							$daycount++;
						}
						//期限データを[5]に詰めていく
						for ($i = $count - 1; $i >= 0; $i--) {
							//日にちを決定
							$j = mt_rand(0, $quizdata[$i][4]);
							while (1) {
								if ($j >= $daycount) $j = 0;
								if ($dayarray[$j] > 0) {
									$dayarray[$j]--;
									$quizdate = clone $date;
									$quizdate->modify(sprintf("+%d day", $j));
									$quizdata[$i][5] = $quizdate->format("Y-m-d");
									break;
								}
								$j++;
							}
						}
						//カードデータに詰めていく
						$studypdo->beginTransaction();
						for ($i = 0; $i < $count; $i++) {
							$stmt = $studypdo->prepare("INSERT INTO usercarddata (userid,cardid,scheduled,expierdate,`interval`,efficiency) VALUES (?,?,?,?,?,?)");
							$stmt->execute(array($userid, $quizdata[$i][0], $quizdata[$i][2], $quizdata[$i][5], $quizdata[$i][4], $efficiency));
						}
						$studypdo->commit();
					}
					$signUpMessage = '登録が完了しました。あなたのユーザー名は ' . $username . ' です。パスワードは ' . $password . ' です。';  // ログイン時に使用するIDとパスワード
				}
			} else {
				//USER名が使用されている
				$errorMessage = 'ユーザー名が既に使用されています';
			}
		} catch (PDOException $e) {
			$errorMessage = 'データベースエラー';
			// $e->getMessage() でエラー内容を参照可能（デバッグ時のみ表示）
			echo $e->getMessage();
		}
	} else if ($_POST["password"] != $_POST["password2"]) {
		$errorMessage = 'パスワードに誤りがあります。';
	}
}

//学校名、教師名取得
$belongs = "";
$schoolid = 0;
$teacherid = 0;
$dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);

try {
	if (!isset($pdo)) $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

	$stmt = $pdo->query("SELECT * FROM userData WHERE id = $userid");
	foreach ($stmt as $row) {
		$row['accesslevel']; //アクセスレベル
		$row['schoolid'];
		$row['teacherid'];
	}
	$schoolid = $row['schoolid'];
	$teacherid = $row['teacherid'];
	if ($schoolid != "") {
		$stmt = $pdo->query("SELECT * FROM schooldata WHERE id = $schoolid");
		foreach ($stmt as $row) {
			$belongs = "学校名: " . $row['name'] . "\n";
		}
	}
	if ($teacherid != "") {
		$stmt = $pdo->query("SELECT * FROM userdata WHERE id = $teacherid");
		foreach ($stmt as $row) {
			$belongs = $belongs . "教師名: " . $row['realname'];
		}
	}
} catch (PDOException $e) {
	$errorMessage = 'データベースエラー';
	//$errorMessage = $sql;
	// $e->getMessage() でエラー内容を参照可能（デバッグ時のみ表示）
	// echo $e->getMessage();
}
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
	<div class="loaderbody" id="loaderbody">
		<div class="loader"></div>
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
							<th><label for="username">ユーザー名</label></th>
							<td><input type="text" id="username" name="username" placeholder="ユーザー名を入力" autocapitalize="none" value="<?php if (!empty($_POST["username"])) {
																																			echo htmlspecialchars($_POST["username"], ENT_QUOTES);
																																		} ?>"></td>
						</tr>
						<tr>
							<th><label for="password">パスワード</label></th>
							<td><input type="password" id="password" name="password" value="" placeholder="パスワードを入力"></td>
						</tr>
						<tr>
							<th><label for="password2">パスワード(確認用)</label></th>
							<td><input type="password" id="password2" name="password2" value="" placeholder="再度パスワードを入力"></td>
						</tr>
					</table>
					<input type="submit" id="signUp" name="signUp" value="新規登録" onsubmit="loadloader()">
				</fieldset>
			</form>
			<br>
			<form action="Login.php">
				<input type="submit" value="戻る">
			</form>
			<script type="text/javascript">
				function loadloader() {
					document.getElementById("loaderbody").style.display = "flex";
					document.getElementById("loaderbody").style.zIndex = 21474836479;
				}
			</script>
</body>

</html>