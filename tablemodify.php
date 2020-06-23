<?php
//require 'password.php';   // password_verfy()はphp 5.5.0以降の関数のため、バージョンが古くて使えない場合に使用
// セッション開始
session_start();

$db['host'] = "localhost";  // DBサーバのURL
$db['user'] = "studyjapanese";  // ユーザー名
$db['pass'] = "m1234567890";  // ユーザー名のパスワード
$db['dbname'] = "login_management";  // データベース名
$db['studydbname'] = "selfstudy";  // studyデータベース名

// ログイン状態チェック
//if (!isset($_SESSION["NAME"])) {
//    header("Location: Login.php");
//    exit;
//}
// アクセスレベルチェック
//if ($_SESSION["LEVEL"] != "X"){
//	header("Location: index.php");
//	exit;
//}
// エラーメッセージの初期化
$errorMessage = "";
	$headers=getallheaders();
	$post_body=file_get_contents('php://input');
	echo "body".$post_body;
	$arr = json_decode($post_body,true);
    $dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);
    $studydsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['studydbname']);
//パスワード変更が必要ないデータは、削除します
	foreach($arr as $key=>$value){
		if($key=="schoolid" || $key=="teacherid") continue;
		$passwordflag=true;
		foreach($value as $key2=>$value2){
			if($key2=="name" && preg_match("/^\*+$/",$value2)){
				$passwordflag=false;
			}
		}
		if(!$passwordflag){
			if(isset($arr[$key]["password"])) unset($arr[$key]["password"]);
			if(isset($arr[$key]["name"])) unset($arr[$key]["name"]);
		}	
	}
    try {
        $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
        $studypdo = new PDO($studydsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
        //school edit
		if(strpos($headers["Referer"],'schooledit.php') !== false){
			//unique属性の更新に関する物だけ、先にゴミを付与して並べ替えによる重複を防ぐ
			foreach($arr as $key=>$value){
				$sql ="";
				if($value["mode"]=="update" && array_key_exists('name', $value)){
					$sql="select name from schooldata where id=".$key;
					$stmt = $pdo->query($sql);
					foreach($stmt as $row){
						$sql="update schooldata set name = '".$row["name"]."gomigomigomigomi' where id=$key";
						$pdo->query($sql);
					}
				}
			}
			foreach($arr as $key=>$value){
				$sql ="";
				if($value["mode"]=="create"){
					$sql="INSERT INTO schooldata (name, status) VALUES ('$value[name]','$value[status]')";	
				}elseif($value["mode"]=="update"){
					$sql="UPDATE schooldata SET ";
					foreach($value as $key2=>$value2){
						if($key2 !== "mode"){
							$sql=$sql."$key2 = '$value2', ";
						}
					}
					$sql = substr( $sql,0, strlen($sql)-2);
					$sql = $sql. " WHERE id = $key";
				}
				echo $sql;
				if($pdo->query($sql)=== TRUE){
					echo " success \n";
				}
			}
		//teacheredit
		}elseif(strpos($headers["Referer"],'teacheredit.php') !== false){
		//unique属性の更新に関する物だけ、先にゴミを付与して並べ替えによる重複を防ぐ
			foreach($arr as $key=>$value){
				$sql ="";
				if($key=="schoolid") continue;
				if($value["mode"]=="update" && array_key_exists('name', $value)){
					$sql="select name from schooldata where id=".$key;
					$stmt = $pdo->query($sql);
					foreach($stmt as $row){
						$sql="update schooldata set name = '".$row["name"]."gomigomigomigomi' where id=$key";
						$pdo->query($sql);
					}
				}
			}
		//パスワード変更の場合には仮登録に
			$schoolid=$arr["schoolid"];
			$date=new DateTime();
			foreach($arr as $key=>$value){
				if($key=="schoolid") continue;
				$sql ="";
				if($value["mode"]=="create"){
					$date->modify('+2 day');
					$stmt=$pdo->prepare("INSERT INTO userdata (subid,name,password,realname,schoolid,accesslevel,validdate) VALUES (?,?,?,?,?,?,?)");
					$stmt->execute(array($value["subid"],$value["name"],$value["password"],$value["realname"],$schoolid,$value["key"]?"A":"B",$date->format("Y-m-d")));
				}elseif($value["mode"]=="update"){
					$sql="UPDATE userdata SET ";
					foreach($value as $key2=>$value2){
						if($key2 !== "mode"){
							if($key2 == "key"){
								$sql=$sql."key = '".($value2?"A":"B")."' ,";
							}else{
								$sql=$sql."$key2 = '$value2', ";
							}
						}
					}
					$sql = substr( $sql,0, strlen($sql)-2);
					$sql = $sql. " WHERE id = $key";
					if($pdo->query($sql)=== TRUE){
					echo " success \n";
					}
				}
			}
//classedit
		}elseif(strpos($headers["Referer"],'classedit.php') !== false){
			foreach($arr as $key=>$value){
				$sql ="";
				if($key=="schoolid" || $key=="teacherid") continue;
				if($value["mode"]=="update" && array_key_exists('name', $value)){
					$sql="select name from schooldata where id=".$key;
					$stmt = $pdo->query($sql);
					foreach($stmt as $row){
						$sql="update schooldata set name = '".$row["name"]."gomigomigomigomi' where id=$key";
						$pdo->query($sql);
					}
				}
			}
			$schoolid=$arr["schoolid"];
			$teacherid=$arr["teacherid"];
			$date=new DateTime();
			foreach($arr as $key=>$value){
				if($key=="schoolid" || $key=="teacherid") continue;
				$sql ="";
				if($value["mode"]=="create"){
					$date->modify('+2 day');
					$stmt=$pdo->prepare("INSERT INTO userdata (subid,name,password,realname,schoolid,teacherid,accesslevel,validdate) VALUES (?,?,?,?,?,?,?,?)");
					$stmt->execute(array($value["subid"],$value["name"],$value["password"],$value["realname"],$schoolid,$teacherid,"C",$date->format("Y-m-d")));
					$lastid=$pdo->lastInsertId();
					$stmt=$studypdo->prepare("INSERT INTO userdata (id,lesson,efficiency,autoadd) VALUES (?,?,?,?)");
					$stmt->execute(array($lastid,$value["lesson"],$value["efficiency"],$value["autoadd"]));
				}elseif($value["mode"]=="update"){
					$sql="UPDATE userdata SET ";
					$studysql="UPDATE userdata SET ";
					$pdoflag=false;
					$studypdoflag=false;
					foreach($value as $key2=>$value2){
						if($key2 !== "mode"){						
							if($key2 != "lesson" || $key2 != "lesson" || $key2 != "lesson"){
								$pdoflag=true;
								$sql=$sql."$key2 = '$value2', ";
							}else{
								$studypdoflag=true;
								$studysql=$sql."$key2 = '$value2', ";
							
							}
							
						}
					}
					$sql = substr( $sql,0, strlen($sql)-2);
					$sql = $sql. " WHERE id = $key";
					if($pdoflag) $pdo->query($sql);
					
					$studysql = substr( $sql,0, strlen($sql)-2);
					$studysql = $sql. " WHERE id = $key";
					if($studypdoflag) $studypdo->query($studysql);

				}
			}

		}
		

    } catch (PDOException $e) {
        $errorMessage = 'データベースエラー';
        //$errorMessage = $sql;
         //$e->getMessage() でエラー内容を参照可能（デバッグ時のみ表示）
         echo $e->getMessage();
    }
    

//	header("Location:index.php");
?>