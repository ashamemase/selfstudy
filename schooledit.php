<?php
session_start();

// ログイン状態チェック
if (!isset($_SESSION["NAME"])) {
    header("Location: Login.php");
    exit;
}
// アクセスレベルチェック
if ($_SESSION["LEVEL"] != "X"){
	header("Location: index.php");
	exit;
}

$db['host'] = "localhost";  // DBサーバのURL
$db['user'] = "studyjapanese";  // ユーザー名
$db['pass'] = "m1234567890";  // ユーザー名のパスワード
$db['dbname'] = "login_management";  // データベース名

// エラーメッセージの初期化
$errorMessage = "";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title></title>
</head>
<body>
    <table id="maintable">
        <tr id="0">
            <td>ID</td>
            <td>学校名</td>
            <td>状態</td>
            <td>削除</td>
        </tr>
        
        <?php
        $dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);
        try {
            $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));

            $stmt = $pdo->query('SELECT * FROM schooldata');
            foreach($stmt as $row){
            	$row["id"];
            	$row["name"];
            	$row["status"];
            	echo "<tr id='$row[id]'>";
            	echo "<td><a href='teacheredit.php?schoolid=$row[id]'>$row[id]</a></td>";
            	echo "<td><input type='text' id='$row[id]_name' onchange='OnChange(this)' value='".htmlspecialchars($row["name"],ENT_QUOTES)."' /></td>";
            	echo "<td><select id='$row[id]_status' onchange='OnChange(this)' value='$row[status]' data-value='$row[status]'/></td>";
            	echo "<td><button id='$row[id]_delete' onclick='OnDel(\"$row[id]\")'>×</button> <input type='text' hidden value='1' onchange='OnChange(this)' /></td>";
            	echo "</tr>";
            }


        } catch (PDOException $e) {
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
        var json = {};
        var statuses = { "通常": "n", "制限": "r", "禁止": "b", "削除": "d" };

        window.addEventListener('DOMContentLoaded', function () {
            var table = document.getElementById("maintable");

            for (let row in table.rows) {
                if (table.rows[row].id == undefined) continue;
                var id = table.rows[row].id;
                if (id>idcount) idcount=id;
                if (id == "0") continue;
                var select = document.getElementById(String(id) + "_status");
                AddSelect(select);
            }
        })

        function OnAdd() {
            idcount++;
            var table = document.getElementById("maintable");
            var row = table.insertRow(-1);
            row.id = String(idcount);
            var col1 = row.insertCell(-1);
            var col2 = row.insertCell(-1);
            var col3 = row.insertCell(-1);
            var col4 = row.insertCell(-1);

            col2.innerHTML = "<input type='text' id='" + String(idcount) + "_name'  onchange='OnChange(this)'/>";
            col3.innerHTML = "<select id='" + String(idcount) + "_status' onchange='OnChange(this)' value='n'></select>";
            col4.innerHTML = "<button id='" + String(idcount) + "_delete' onclick=\"OnDel('" + String(idcount) + "')\">×</button>";

            AddSelect(document.getElementById(String(idcount) + "_status"));
            json[String(idcount)] = {};
            json[String(idcount)]["mode"] = "create";
            json[String(idcount)]["status"] = "n";
        }

        function AddSelect(obj) {
 			var value=obj.getAttribute("data-value");
        	var index=0;
            for (let key in statuses) {
                var option = document.createElement("option");
                option.text = key;
                option.value = statuses[key];
                obj.appendChild(option);
                if(value==statuses[key] && value!=""){
                	obj.selectedIndex = index;
                	obj.value=value;
                }                
                index++;
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
            console.log("call OnChange" + obj.id)
            var id = obj.id;
            var words = id.split('_');
            id = words[0];
            var name = words[1];
            if (!(String(id) in json)) {
                json[String(id)] = {};
                json[String(id)]["mode"] = "update";
            }

            json[String(id)][name] = obj.value;
        }
        
        function OnSend(){
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
        	xmlHttpRequest.open('POST','tablemodify.php',false);
        	xmlHttpRequest.setRequestHeader('Content-Type','application/json');
        	xmlHttpRequest.send(data);
        	
        }
    </script>
</body>
</html>