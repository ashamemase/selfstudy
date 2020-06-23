<?php
session_start();

// ログイン状態チェック
if (!isset($_SESSION["NAME"])) {
    header("Location: Login.php");
    exit;
}
// アクセスレベルチェック
if ($_SESSION["LEVEL"] != "X" && $_SESSION["LEVEL"] != "A" && $_SESSION["LEVEL"] != "B" ){
	header("Location: index.php");
	exit;
}
if($_SESSION["SCHOOLID"]=="" && $_SESSION["LEVEL"] == "X"){
	$_SESSION["SCHOOLID"]=$_GET['schoolid'];
}

$db['host'] = "localhost";  // DBサーバのURL
$db['user'] = "studyjapanese";  // ユーザー名
$db['pass'] = "m1234567890";  // ユーザー名のパスワード
$db['dbname'] = "login_management";  // データベース名
$db['studydbname'] = "selfstudy"; //学習用データベース名
// エラーメッセージの初期化
$errorMessage = "";

//学校所属の教師名取得
if($_SESSION["TEACHERID"]=="" && $_SESSION["LEVEL"] == "X"){
	$_SESSION["TEACHERID"]=$_GET['teacherid'];

}

$dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);
$teachertable=array();
try{
	if(!isset($pdo)) $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
	$stmt = $pdo->query("SELECT * FROM userdata where schoolid=$_SESSION[SCHOOLID] order by subid");
	foreach($stmt as $row){
		$row["id"];
		$row["realname"];
		$teachertable[$row["id"]] = $row["realname"];
		if($row["id"]==$_SESSION["TEACHERID"]) $realname=$row["realname"];
	}
}catch (PDOException $e){
	$errorMessage = 'データベースエラー';
	//$errorMessage = $sql;
	//$e->getMessage() でエラー内容を参照可能（デバッグ時のみ表示）
	echo $e->getMessage();
}
$teachertable=$teachertable + array('-1'=>'school', '-2'=>'remove');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title></title>
    <style>
        table {
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        .number{
            width:30px;
        }
        .level{
            width:30px;
        }
        .efficent{
            width:30px;
        }
        input:invalid{
        	background-color: pink;
        }
    </style>
</head>
<body>

    <?php
    	echo "<input type='text' id='schoolid' hidden value='$_SESSION[SCHOOLID]' />";
    	echo "<input type='text' id='teacherid' hidden value='$_SESSION[TEACHERID]' />";
       	if($_SESSION["LEVEL"] == "X"){
        	echo "<h1>学校ID= $_SESSION[SCHOOLID] </h1>";
        }
        if(isset($realname)) echo "<h1>".htmlspecialchars($realname, ENT_QUOTES)." 先生のクラス</h1>";
    ?>
    <p>デフォルトのパスワード<input type="text" id="defaultpass" value="pass" /></p>
    <table id="maintable">
        <tr id="0">
            <td>番号</td>
            <td>本名</td>
            <td>ID</td>
            <td>パスワード</td>
            <td>状態</td>
            <td>進度</td>
            <td>レベル</td>
            <td>自動追加</td>
            <td>移動</td>
            <td>削除</td>
        <?php if($_SESSION["LEVEL"] == "X")echo "<td>本ID</td>"; ?>
        
        </tr>
        <?php
        	$studydsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['studydbname']);

	        try{
				if(!isset($pdo)) $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
				if(!isset($studypdo)) $studypdo = new PDO($studydsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
				
				$stmt = $pdo->query("SELECT * FROM userdata where teacherid=$_SESSION[TEACHERID] order by 'subid'");
				foreach($stmt as $row){
	            	$row["id"];
	            	$row["subid"];
	            	$row["realname"];
	            	$row["name"];
	            	$row["password"];
	            	$row["teacherid"];
	            	$row["accesslevel"];
		        	echo "<tr id='$row[id]'>";
		            echo "<td><input type='text' id='$row[id]_subid' class='number' pattern='^\\d+$' maxlength='3' onchange='OnChange(this)' value='$row[subid]' /></td>";
		            echo "<td><input type='text' id='$row[id]_realname' onchange='OnChange(this)' value='$row[realname]' /></td>";
	            	if(preg_match("/^=.+$/",$row["name"])){
		             	echo "<td><input type='text' id='$row[id]_name' onchange='OnChange(this)' pattern='^[=|*].+$' value='".htmlspecialchars($row["name"],ENT_QUOTES)."' /></td>";
		            	echo "<td><input type='text' id='$row[id]_password' onchange='OnChange(this)' value='".htmlspecialchars($row["password"],ENT_QUOTES)."' /></td>";	
	            		echo "<td id='$row[id]_status'>仮登録済み</td>";
	            	}else{
		            	echo "<td><input type='text' id='$row[id]_name' onchange='OnChange(this)' pattern='^[=|*].+$' value='***************' /></td>";
		            	echo "<td><input type='text' id='$row[id]_password' onchange='OnChange(this)' value='***************' /></td>";
		            	echo "<td id='$row[id]_status'>登録済み</td>";
		            }
		            $studystmt = $studypdo->query("select * from userdata where id = $row[id]");
		            foreach($studystmt as $studyrow){
		            	$studyrow["lesson"];
		            	$studyrow["efficiency"];
		            	$studyrow["autoadd"];
		            }
		            echo "<td>～<input type='text' id='$row[id]_lesson' maxlength='10' class='level' onchange='OnChange(this)' pattern='^([0-9]|[1-4][0-9]|50|)$' value='$studyrow[lesson]' />課まで</td>";
		            echo "<td><input type='text' id='$row[id]_efficency' class='efficent' pattern='^(1[3-9][0-9]|[2-9][0-9][0-9])$' value='$studyrow[efficiency]' onchange='OnChange(this)' />%</td>";
		            if($studyrow["autoadd"]){
		            	echo "<td><input type='checkbox' id='$row[id]_autoadd' onchange='OnChange(this)' checked /></td>";
		            }else{
		            	echo "<td><input type='checkbox' id='$row[id]_autoadd' onchange='OnChange(this)'/></td>";
		            }
		            
		            echo "<td><select id='$row[id]_move' onchange='OnChange(this)'></select></td>";
		            echo "<td><button id='$row[id]_delete' onclick='OnDel(\"$row[id]\")'>×</button> <input type='text' hidden value='1' onchange='OnChange(this)' /></td>";
		            if($_SESSION["LEVEL"] == "X")echo "<td>$row[id]</td>";
		       		echo "</tr>";
	            }
			}catch (PDOException $e){
				$errorMessage = 'データベースエラー';
				//$errorMessage = $sql;
				//$e->getMessage() でエラー内容を参照可能（デバッグ時のみ表示）
				echo $e->getMessage();
			}
		?>

    </table>
    <button id="add" onclick="OnAdd()">+</button>
    <hr>
    <button id="send" onclick="OnSend()">更新</button>
    
    <script>
        var idcount=0;
        var subidcount=0;
        var idschool;
        var idteacher;
        var json = {};
        var teachers = <?php echo "{'':0";
        	foreach($teachertable as $key=>$value){
        		echo ",'$value':$key";
        	}
        	echo ",'school':-1,'remove':-2};";
        ?>
        window.addEventListener('DOMContentLoaded', function () {
            var table = document.getElementById("maintable");
            idcount = table.rows.length - 1;
            idschool = document.getElementById("schoolid").value;
            idteacher = document.getElementById("teacherid").value;
            json["schoolid"]=idschool;
            json["teacherid"]=idteacher;
            for (let row in table.rows) {
                if (table.rows[row].id == undefined) continue;
                var id = table.rows[row].id;
                if (id == "0") continue;
                var select = document.getElementById(String(id) + "_move");
                var subid = (document.getElementById(String(id)+"_subid")).value;
                if(subid>subidcount) subidcount=subid;
                AddTeacher(select);
            }
        })

        function OnAdd() {
            idcount++;
            subidcount++;
            var defaultpass = document.getElementById("defaultpass").value;
            var table = document.getElementById("maintable");
            var row = table.insertRow(-1);
            row.id = String(idcount);
            var col1 = row.insertCell(-1);
            var col2 = row.insertCell(-1);
            var col3 = row.insertCell(-1);
            var col4 = row.insertCell(-1);
            var col5 = row.insertCell(-1);
            var col6 = row.insertCell(-1);
            var col7 = row.insertCell(-1);
            var col8 = row.insertCell(-1);
            var col9 = row.insertCell(-1);
            var col10 = row.insertCell(-1);
            <?php if($_SESSION["LEVEL"] == "X")echo "var col11 = row.insertCell(-1);"; ?>
            col1.innerHTML = "<input type='text' id='" + String(idcount) + "_subid' class='number' pattern='^\\d+$' maxlength='3'  onchange='OnChange(this)' value='"+String(subidcount)+"'/>";
            col2.innerHTML = "<input type='text' id='" + String(idcount) +"_realname' required  onchange='OnChange(this)'/>";
            col3.innerHTML = "<input type='text' id='" + String(idcount) + "_name' pattern='^=.+$'/ value='=" + String(idschool) + "-" + String(idteacher) + "-" + String(subidcount) + "'  onchange='OnChange(this)'>";
            col4.innerHTML = "<input type='text' id='" + String(idcount) + "_password' value='" + defaultpass + "'  onchange='OnChange(this)'/>";
            col5.innerHTML = "仮登録中";
            col5.id = String(idcount) + "_status";
            col6.innerHTML = "～<input type='text'   id='" + String(idcount) + "_lesson' maxlength='10' class='level'  onchange='OnChange(this)'  pattern='^([0-9]|[1-4][0-9]|50|)$' value='0'/>課まで";
            col7.innerHTML = "<select id='" + String(idcount) + "_efficiency' onchange='OnChange(this)'><option value='200' selected>普通</option><option value='170'>弱い</option><option value='150'>下手</option></select>";
            col8.innerHTML = "<input type='checkbox'  id='" + String(idcount) + "_autoadd'  onchange='OnChange(this)' checked/>";
            col9.innerHTML = "<select id='" + String(idcount) + "_move' onchange='OnChange(this)'></select>";
            col10.innerHTML = "<button id='" + String(idcount) + "_delete' onclick=\"OnDel('" + String(idcount) + "')\">×</button>";

            AddTeacher(document.getElementById(String(idcount) + "_move"));
            json[String(idcount)] = {};
            json[String(idcount)]["mode"] = "create";
            json[String(idcount)]["name"] = "=" + String(idschool) + "-" + String(idteacher) + "-" + String(subidcount) ;
            json[String(idcount)]["password"] = defaultpass;
            json[String(idcount)]["efficiency"] = 200;
            json[String(idcount)]["autoadd"] = true;
            json[String(idcount)]["lesson"] = 0;
            json[String(idcount)]["subid"] = subidcount;;
            
        }

        function AddTeacher(obj) {
            for (let key in teachers) {
                var option = document.createElement("option");
                option.text = key;
                option.value = teachers[key];
                obj.appendChild(option);
            }
        }

        function OnDel(number) {
            var table = document.getElementById("maintable");
            var row = document.getElementById(number);
            var index = row.rowIndex;
            table.deleteRow(index);
            if (number in json) {
                delete json[number]
            }
        }
        function OnChange(obj) {
            var id = obj.id;
            var words = id.split('_');
            id = words[0];
            var name = words[1];
            if (!(String(id) in json)) {
                json[String(id)] = {};
                json[String(id)]["mode"] = "update";
            }
            if (name == "autoadd") {
                json[String(id)][name] =obj.checked
            } else {
                json[String(id)][name] = obj.value;
            }
            if(name != "name" && name != "subid") return;
			//subid,nameの重複チェック
			obj.setCustomValidity("");
			var arraytable=[];
			var table = document.getElementById("maintable");
			for(let row in table.rows){
				if (table.rows[row].id == undefined) continue;
                var id2 = table.rows[row].id;
                if (id2 == "0") continue;
                arraytable.push([table.rows[row].id,(document.getElementById(String(id2) + "_subid")).value,(document.getElementById(String(id2) + "_name")).value]);
			}
			console.log(arraytable,obj.value);
			for(let i=0;i<arraytable.length;i++){
				if(id == arraytable[i][0]) continue;
				if(name == "subid"){
					if(obj.value == arraytable[i][1]){
						obj.setCustomValidity("番号が重複しています");
					}
				}else if(name == "name"){
					if(obj.value == arraytable[i][2]){
						obj.setCustomValidity("IDが重複しています");
					}
				}
			}
        }
        
                 function OnSend(){
         
         	//データの正当性チェック(名前があるか、パスがあるか、ID、本名があるか、IDはユニークかなど
         	var table = document.getElementById("maintable");
         	var arraytable=[];
         	var regex1 = new RegExp(/^=.+$/);
         	var regex2 = new RegExp(/^\*+$/);
         	for (let row in table.rows) {
                if (table.rows[row].id == undefined) continue;
                var id = table.rows[row].id;
                if (id == "0") continue;
                arraytable.push([(document.getElementById(String(id) + "_subid")).value,(document.getElementById(String(id) + "_realname")).value,(document.getElementById(String(id) + "_name")).value,
                	(document.getElementById(String(id) + "_password")).value]);
            }
         	for (let i=0;i<arraytable.length;i++){
         		console.log(arraytable[i]);
         		if(arraytable[i][0]==""){
         			alert("空の番号があります");
         			return;
         		}else if(arraytable[i][1]==""){
         			alert("空の名前があります");
         			return;
         		}else if(arraytable[i][2]==""){
         			alert("空のIDがあります");
         			return;
         		}else if(!regex1.test(arraytable[i][2]) && !regex2.test(arraytable[i][2])){
         			alert("IDが不正です。\n仮登録やパスワード変更の時は「=」から始まる名前を\nそうでなければ「*」としてください");
         			return;
         		}else if(arraytable[i][3]==""){
         			alert("空のpasswordがあります");
         			return;
         		}
         		for(let j=0;j<arraytable.length;j++){
         			if(j==i) continue;
         			if(arraytable[i][0]==arraytable[j][0]){
         				alert("番号の重複があります");
         				return;
         			}else if(arraytable[i][2]==arraytable[j][2]){
         				alert("IDの重複があります");
         				return;
         			}
         		}
         	}
        	var data=JSON.stringify(json);
        	var xmlHttpRequest = new XMLHttpRequest();
        	xmlHttpRequest.onreadystatechange = function()
        	{
        		var READYSTATE_COMPLETED = 4;
        		var HTTP_STATUS_OK = 200;
        		if( this.readyDtate == READYSTATE_COMPLETED && this.status == HTTP_STATUS_OK){
        			alert(this.responseText);
        		}
        	}
        	xmlHttpRequest.onload = function(){
        		var res=xmlHttpRequest.responseText;
        		if(res.length>0) alert(res);
        		document.location.reload();
        	}
        	xmlHttpRequest.onerror = function(){
        		alert("shippai!");
        	}
        	xmlHttpRequest.open('POST','tablemodify.php',true);
        	xmlHttpRequest.setRequestHeader('Content-Type','application/json');
        	xmlHttpRequest.send(data);
        	
        }
    </script>
</body>
</html>