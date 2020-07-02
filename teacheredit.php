<?php
session_start();

// ログイン状態チェック
if (!isset($_SESSION["NAME"])) {
    header("Location: Login.php");
    exit;
}
// アクセスレベルチェック
if ($_SESSION["LEVEL"] != "X" && $_SESSION["LEVEL"] != "A") {
    header("Location: index.php");
    exit;
}
if ($_SESSION["SCHOOLID"] == "" && $_SESSION["LEVEL"] == "X") {
    $_SESSION["SCHOOLID"] = $_GET['schoolid'];
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
    <style>
        table {
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        .number {
            width: 30px;
        }

        .level {
            width: 30px;
        }

        .efficent {
            width: 30px;
        }

        .keycheck {
            display: none;
        }

        .keycheck+label:after {
            content: "　";
        }

        .keycheck:checked+label:after {
            content: "\1F511";
        }

        input:invalid {
            background-color: pink;
        }
    </style>
</head>

<body>
    <input type="text" id="schoolid" hidden value="<?php echo $_SESSION["SCHOOLID"] ?>" />
    <?php
    if ($_SESSION["LEVEL"] == "X") {
        echo "<h1>学校ID= $_SESSION[SCHOOLID] </h1>";
    }
    ?>
    <p>デフォルトのパスワード<input type="text" id="defaultpass" value="pass" /></p>
    <table id="maintable">
        <tr id="0">
            <?php
            if ($_SESSION["LEVEL"] == "X") {
                echo "<td>本ID</td>";
            }
            ?>
            <td>番号</td>
            <td>本名</td>
            <td>ID</td>
            <td>パスワード</td>
            <td>状態</td>
            <td>鍵</td>
            <td>移動</td>
            <td>削除</td>
        </tr>

        <?php
        $dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);
        try {
            $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

            $stmt = $pdo->query("SELECT * FROM userdata where schoolid=$_SESSION[SCHOOLID] and (accesslevel='A' or accesslevel='B') order by subid");
            foreach ($stmt as $row) {
                $row["id"];
                $row["subid"];
                $row["realname"];
                $row["name"];
                $row["password"];
                $row["teacherid"];
                $row["accesslevel"];


                echo "<tr id='$row[id]'>";
                if ($_SESSION["LEVEL"] == "X") {
                    echo "<td><a href='classedit.php?teacherid=$row[id]'>$row[id]</a></td>";
                }
                echo "<td><input type='text' id='$row[id]_subid' class='number' pattern='^\d+$' maxlength='3' onchange='OnChange(this)' value='$row[subid]' /></td>";
                echo "<td><input type='text' id='$row[id]_realname' onchange='OnChange(this)' pattern='^.+$' value='" . htmlspecialchars($row["realname"], ENT_QUOTES) . "' /></td>";
                if (preg_match("/^=.+$/", $row["name"])) {
                    echo "<td><input type='text' id='$row[id]_name' onchange='OnChange(this)' pattern='^[=|*].+$' value='" . htmlspecialchars($row["name"], ENT_QUOTES) . "' /></td>";
                    echo "<td><input type='text' id='$row[id]_password' onchange='OnChange(this)' value='" . htmlspecialchars($row["password"], ENT_QUOTES) . "' /></td>";
                } else {
                    echo "<td><input type='text' id='$row[id]_name' onchange='OnChange(this)' pattern='^[=|*].+$' value='***************' /></td>";
                    echo "<td><input type='text' id='$row[id]_password' onchange='OnChange(this)' value='***************' /></td>";
                }
                if (preg_match("/^=.+$/", $row["name"])) {
                    echo "<td id='$row[id]_status'>仮登録済み</td>";
                } else {
                    echo "<td id='$row[id]_status'>登録済み</td>";
                }

                echo "<td><input type='checkbox' id='$row[id]_key' class='keycheck' onchange='OnChange(this)'" . ($row["accesslevel"] == "A" ? "checked" : "") . "/><label for='$row[id]_key'></label></td>";
                echo "<td><select id='$row[id]_move' onchange='OnChange(this)'></select></td>";
                echo "<td><button id='$row[id]_delete' onclick='OnDel(\"1\")'>×</button> <input type='text' hidden value='$row[id]' onchange='OnChange(this)' /></td>";
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
        var idcount = 0;
        var subidcount = 0;
        var idschool;
        var json = {};
        var options = {
            "": 0,
            "remove": -2
        };
        window.addEventListener('DOMContentLoaded', function() {
            var table = document.getElementById("maintable");
            idschool = document.getElementById("schoolid").value;
            json["schoolid"] = idschool;
            for (let row in table.rows) {
                if (table.rows[row].id == undefined) continue;
                var id = table.rows[row].id;
                if (id > idcount) idcount = id;
                if (id == "0") continue;
                var select = document.getElementById(String(id) + "_move");
                var subid = (document.getElementById(String(id) + "_subid")).value;
                if (subid > subidcount) subidcount = subid;
                AddOptions(select);
            }

        })

        function OnAdd() {
            idcount++;
            subidcount++;
            var defaultpass = document.getElementById("defaultpass").value;
            var table = document.getElementById("maintable");
            var row = table.insertRow(-1);
            row.id = String(idcount);
            <?php
            if ($_SESSION["LEVEL"] == "X") {
                echo "var col0= row.insertCell(-1);";
            }
            ?>
            var col1 = row.insertCell(-1);
            var col2 = row.insertCell(-1);
            var col3 = row.insertCell(-1);
            var col4 = row.insertCell(-1);
            var col5 = row.insertCell(-1);
            var col6 = row.insertCell(-1);
            var col7 = row.insertCell(-1);
            var col8 = row.insertCell(-1);
            col1.innerHTML = "<input type='text' id='" + String(idcount) + "_subid' class='number' pattern='^\\d+$' maxlength='3'  onchange='OnChange(this)' value='" + String(subidcount) + "'/>";
            col2.innerHTML = "<input type='text' id='" + String(idcount) + "_realname' required onchange='OnChange(this)'/>";
            col3.innerHTML = "<input type='text' id='" + String(idcount) + "_name' pattern='^=.+$' value='=" + String(idschool) + "-" + String(subidcount) + "'  onchange='OnChange(this)'>";
            col4.innerHTML = "<input type='text' id='" + String(idcount) + "_password' value='" + defaultpass + "'  onchange='OnChange(this)'/>";
            col5.innerHTML = "仮登録中";
            col5.id = String(idcount) + "_status";
            col6.innerHTML = "<input type='checkbox' class='keycheck' id='" + String(idcount) + "_key'  onchange='OnChange(this)'/><label for='" + String(idcount) + "_key'></label>";
            col7.innerHTML = "<select id='" + String(idcount) + "_move' onchange='OnChange(this)'></select>";
            col8.innerHTML = "<button id='" + String(idcount) + "_delete' onclick=\"OnDel('" + String(idcount) + "')\">×</button>";

            AddOptions(document.getElementById(String(idcount) + "_move"));
            json[String(idcount)] = {};
            json[String(idcount)]["mode"] = "create";
            json[String(idcount)]["name"] = "=" + String(idschool) + "-" + String(subidcount);
            json[String(idcount)]["password"] = defaultpass;
            json[String(idcount)]["subid"] = subidcount;
            json[String(idcount)]["key"] = false;
        }

        function AddOptions(obj) {
            for (let key in options) {
                var option = document.createElement("option");
                option.text = key;
                option.value = options[key];
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
            if (name == "key") {
                json[String(id)][name] = obj.checked
            } else {
                json[String(id)][name] = obj.value;
            }

            if (name != "name" && name != "subid") return;
            //subid,nameの重複チェック
            obj.setCustomValidity("");
            var arraytable = [];
            var table = document.getElementById("maintable");
            for (let row in table.rows) {
                if (table.rows[row].id == undefined) continue;
                var id2 = table.rows[row].id;
                if (id2 == "0") continue;
                arraytable.push([table.rows[row].id, (document.getElementById(String(id2) + "_subid")).value, (document.getElementById(String(id2) + "_name")).value]);
            }
            console.log(arraytable, obj.value);
            for (let i = 0; i < arraytable.length; i++) {
                if (id == arraytable[i][0]) continue;
                if (name == "subid") {
                    if (obj.value == arraytable[i][1]) {
                        obj.setCustomValidity("番号が重複しています");
                    }
                } else if (name == "name") {
                    if (obj.value == arraytable[i][2]) {
                        obj.setCustomValidity("IDが重複しています");
                    }
                }
            }
        }

        function OnSend() {

            //データの正当性チェック(名前があるか、パスがあるか、ID、本名があるか、IDはユニークかなど
            var table = document.getElementById("maintable");
            var arraytable = [];
            var regex1 = new RegExp(/^=.+$/);
            var regex2 = new RegExp(/^\*+$/);
            var haskey = false;
            for (let row in table.rows) {
                if (table.rows[row].id == undefined) continue;
                var id = table.rows[row].id;
                if (id == "0") continue;
                arraytable.push([(document.getElementById(String(id) + "_subid")).value, (document.getElementById(String(id) + "_realname")).value, (document.getElementById(String(id) + "_name")).value,
                    (document.getElementById(String(id) + "_password")).value, (document.getElementById(String(id) + "_key")).checked
                ]);
            }
            for (let i = 0; i < arraytable.length; i++) {
                console.log(arraytable[i]);
                if (arraytable[i][0] == "") {
                    alert("空の番号があります");
                    return;
                } else if (arraytable[i][1] == "") {
                    alert("空の名前があります");
                    return;
                } else if (arraytable[i][2] == "") {
                    alert("空のIDがあります");
                    return;
                } else if (!regex1.test(arraytable[i][2]) && !regex2.test(arraytable[i][2])) {
                    alert("IDが不正です。\n仮登録やパスワード変更の時は「=」から始まる名前を\nそうでなければ「*」としてください");
                    return;
                } else if (arraytable[i][3] == "") {
                    alert("空のpasswordがあります");
                    return;
                }
                for (let j = 0; j < arraytable.length; j++) {
                    if (j == i) continue;
                    if (arraytable[i][0] == arraytable[j][0]) {
                        alert("番号の重複があります");
                        return;
                    } else if (arraytable[i][2] == arraytable[j][2]) {
                        alert("IDの重複があります");
                        return;
                    }
                }
                if (arraytable[i][4]) haskey = true;

            }
            if (haskey == false) {
                alert("少なくとも一人に鍵を渡してください");
                return;
            }

            var data = JSON.stringify(json);
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
            xmlHttpRequest.open('POST', 'tablemodify.php', true);
            xmlHttpRequest.setRequestHeader('Content-Type', 'application/json');
            xmlHttpRequest.send(data);

        }
    </script>

</body>

</html>