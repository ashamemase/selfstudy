<?php
//************************************************* 
//
//
//
require_once 'common.php';

class _DBbase
{
    protected $host = "localhost";
    protected $user = "studyjapanese";
    protected $password = "m1234567890";
    protected $dbname = "";
    public $pdo;
    public $errormessage = "";

    protected $databaseerrorstr;

    public function __construct()
    {
        $this->databaseerrorstr = fn () => _L('データベースエラー', 'Lỗi cơ sở dữ liệu');

        try {
            // MySQLサーバへ接続
            $dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $this->host, $this->dbname);
            $this->pdo = new PDO($dsn, $this->user, $this->password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } catch (PDOException $e) {
            var_dump($e->getMessage());
        }
    }

    public function getRowFromID($id)
    {
        if ($id === null) return null;
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM ? WHERE id=?');
            $stmt->execute(array($this->tablename, $id));
            return $stmt->fetch((PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            $this->errormessage = $this->databaseerrorstr;
        }
    }
}

class DBUser extends _DBbase
{
    public $userdata = [];
    protected $dbname = "login_management";
    private $tablename = "userData";

    //パスワード認証。成功したらUserdata型を返す。失敗の場合はerrormessageをセットし、nullが返る
    public function authentication($name, $pass)
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM ? WHERE name = ?');
            $stmt->execute(array($this->tablename, $name));

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $user = new Userdata($row);
                if ($user->isRegisterd()) {
                    //登録済みの場合
                    if (password_verify($pass, $row['password'])) return $user;
                    //                    session_regenerate_id(true);
                    //                    $_SESSION['USER']=$user;
                } else {
                    //仮登録の場合
                    if ($pass == $row['password']) return $user;
                }
            }
            //           if (preg_match("/^=.+$/", $userid)) {
            //                   header("Location: specialsignup.php");  // メイン画面へ遷移
            // 認証失敗
            $this->errormessage = _L('ユーザーIDあるいはパスワードに誤りがあります。', 'Lỗi tên tài khoản hoặc mật khẩu');
        } catch (PDOException $e) {
            $this->errorMessage = $this->databaseerrorstr;
        }
    }

    //nameとpassを登録します。失敗したらfalseが返ります
    public function Register($id, $name, $pass)
    {
        if ($this->CountUserName($name) > 0) {
            $this->errorMessage = _L("そのユーザー名はすでに使用されています", "Tên tài khoản này đã có người dùng");
            return false;
        }
        if ($this->errormessage !== null) return false;
        try {
            $userdata = new Userdata($this->getRowFromID($id));
            $status = $userdata->status | Userdata::REGISTERD;
            $stmt = $this->pdo->prepare("UPDATE ? SET name=?, password=? status=? where id=?");
            $stmt->execute(array($this->dbname, $name, password_hash($pass, PASSWORD_DEFAULT), $status, $id));
        } catch (PDOException $e) {
            $this->errorMessage = $this->databaseerrorstr;
            return false;
        }
        return true;
    }

    //ユーザー名が使用済みかどうかカウントを返します
    public function CountUserName($name)
    {
        try {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) AS cnt FROM ? WHERE name = ?');
            $stmt->execute(array($this->tablename, $name));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row["cnt"];
        } catch (PDOException $e) {
            $this->errorMessage = $this->databaseerrorstr;
        }
        return -1;
    }
}

class DBWordQuizUserData extends _DBbase
{
    protected $dbname = "selfstudy";
    private $tablename = "userdata";

    public function GetQuizUserDataFromId($id)
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * from ? WHERE id=?');
            $stmt->execute(array($this->tablename, $id));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== null) return new WordUserData(($row));
            else throw new Exception('Data not found (DBWQUD)');
        } catch (Exception $e) {
            $this->errormessage = $e->getMessage();
            return null;
        }
    }

    public function SetInitialUserData(WordUserData $userdata)
    {
        $date = getTodayModified();
        try {
            $stmt = $this->pdo->prepare('UPDATE ? SET startday=? addcount=? where id =?');
            $stmt->execute(array($this->dbname, $date->format("Y-m-d"), $userdata->addcount, $userdata->id));
        } catch (PDOException $e) {
            $this->errormessage = $e->getMessage();
        }
    }
}

class DBWordQuizUserCardData extends _DBbase
{
    protected $dbname = "selfstudy";
    private $tablename = "usercarddata";
    private $dbWQData;
    private $userid;

    public function __construct($id)
    {
        $this->userid = $id;
        $dbWQData = new DBWordQuizData();
        parent::__construct();
    }

    public function SetInitialUserCardData(WordUserData $userdata)
    {
        //ユーザ用のクイズデータ作成
        //学習済みの課までのクイズデータをフェッチ
        $lesson = $userdata->lesson;
        $stmt = $this->dbWQData->pdo->prepare("SELECT * FROM quizes where lesson <= ? order by schedule");
        $stmt->execute(array($lesson));
        $quizdata = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $quiz = array();
            $quiz["id"] = $row["id"];
            $quiz["lesson"] = $row["lesson"];
            $quiz["schedule"] = $row["schedule"];
            $quiz["quiztype"] = $row["quiztype"];
            if ($userdata->lesson > 15) {
                $quiz["interval"] = floor(30 * ($lesson - $quiz["lesson"]) / $lesson) + 1;
            } else {
                $quiz["interval"] = 2 * ($lesson - $quiz["lesson"]) + 1;
            }
            $quizdata[] = $quiz;
        }
        //復習のスケジューリングするための入れ物を用意
        $cardcount = count($quizdata);
        $dayarray = array();
        $cards = 0;
        $daycards = 400;
        while ($cards < $cardcount) {
            $daycards -= $userdata->addcount;
            $daycards = $daycards < 100 ? 100 : $daycards;
            $dayarray[] = $daycards;
            $cards += $daycards;
        }
        //期限データをセット
        $daycount = count($dayarray);
        $today = getTodayModified();
        foreach ($quizdata as $key => $value) {
            $day = mt_rand(0, $value["interval"]);
            while (1) {
                $day = $day >= $daycount ? 0 : $day;
                if ($dayarray[$day] <= 0) {
                    $day++;
                    continue;
                }
                $dayarray[$day]--;
                $quizday = clone $today;
                $quizday->modify(sprintf("+%d day", $day));
                $quizdata[$key]["expierdate"] = $quizday->format("Y-m-d");
                break;
            }
        }
        //データベース登録
        $this->pdo->beginTransaction();
        foreach ($quizdata as $quiz) {
            $stmt -= $this->pdo->prepare("INSERT INTO ? (userid,cardid,scheduled,expierdate,`interval`,efficiency) VALUES (?,?,?,?,?,?)");
            $stmt->execute(array($this->dbname, $userdata->id, $quiz["schedule"], $quiz["expiredate"], $quiz["interval"], $userdata->efficiency));
        }
        $this->pdo->commit();
    }
}

class DBWordQuizData extends _DBbase
{
    protected $dbname = "selfstudy";
    private $tablename = "quizes";
}

class DBWordLog extends _DBbase
{
    protected $dbname = "selfstudy";
    private $tablename = "log";
}
