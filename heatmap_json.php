<?php
//Logincheck.phpと同じローカルの仮ユーザーログイン検証、
//BookRollのデータベースにアクセスし、タイムスタンプで閲覧時間に変更、
//ユーザーごとにJson形式（data/ユーザーID_heatmap.json）に保存

date_default_timezone_set('Asia/tokyo');

//TODO：BookRollのデータベースにSSHトンネル立てるクラス、現在利用なし、
//原因；BookRollのデータベースにアクセスするには、コマンドで実行する以後、Passphraseを入力する2つのステップが必要、
//LinuxでBatコマンドで実行する可能が、PHPのexecの関数で実行不可能

//代替：ターミナルでSSHトンネル作成のコマンド入力、BookRollのデータベースにアクセス
require_once "SSHTunnel.php";

//Jsonの形式でデータ保存のクラス
class JsonObject
{

}

//POSTでユーザーID,始まり時間と終わり時間を獲得
$student = $_POST['student'];
if($_POST['begin']){
    $begin = $_POST['begin'];
    $begin = strtotime($begin);
}else{
    $begin = time();
}
if($_POST['end']){
    $end = $_POST['end'];
    $end = strtotime($end);
}else{
    $end = time();
}

//TODO：テスト用のローカルのデータベース、修正可
$dsn = 'mysql:dbname=bagujo;host=127.0.0.1;port=3306';
$dbuser = 'onigiri';
$dbpassword = 'angelfantuan';

try {
    $dbh = new PDO($dsn, $dbuser, $dbpassword);
} catch (PDOException $e) {
    echo "接続失敗: " . $e->getMessage() . "\n";
}

$users_l = $dbh->query("select * from users");
$loginstatus = "false";
$student_l = array();
foreach ($users_l as $row) {
    //TODO：グループIDとユーザーIDの検証、テスト用の'1'を修正
    if ($row['GroupID'] == '1') {
        array_push($student_l, $row['StudentID']);
    }                            //change $_POST


    if ($row['StudentID'] == $student) {
        $loginstatus = "success";
    }
}

echo $loginstatus;

//トンネルのセッティングにより、BookRollのデータベースにアクセス
$dsn_bookr = 'mysql:dbname=bookroll;host=127.0.0.1;port=3307';
$user_bookr = 'student';
$password_bookr = 'ledsbr';

try {
    $dsn_bookr = new PDO($dsn_bookr, $user_bookr, $password_bookr);
} catch (PDOException $e) {
    echo "接続失敗: " . $e->getMessage() . "\n";
} finally {

    $jsonString = new JsonObject();
    //TODO：テスト用の'1'を修正
    $jsonString->id = 'グループ 1';    //change $_POST
    $jsonString->children = array();

    for ($i = 0; $i < count($student_l); $i++) {


        //TODO：ユーザーIDごとにoperationを検索するSQLコマンド、
        //%課題協学第1回%はテスト用、コース名を表す変数に変更
        $select_cour_st = <<<ss
        SELECT * FROM bookroll.br_event_log 
        left join bookroll.br_contents on bookroll.br_event_log.contents_id = bookroll.br_contents.contents_id 
        where bookroll.br_contents.title like N'%課題協学第1回%' AND bookroll.br_event_log.user_id = '$student_l[$i]@FE290BBB-CB35-A016-DE38-DE8E06D6D7A7'
ss;
        $result = $dsn_bookr->query($select_cour_st);

        $read_time = array();
        for ($t = 1; $t < 39; $t++){  //check
            $read_time[(string)$t] = 0;
        }

        $tm_tmp = 0;
        $open_book = false;
        $is_first_open = true;

        //タイムスタンプで時間範囲に変更
        foreach($result as $row){
            if ($is_first_open == true){
                if($row['operation_name'] != "REGIST CONTENTS" && $row['operation_name'] != "OPEN" && $row['operation_name'] != "CLOSE"){
                    $open_book = True;
                    $is_first_open = false;
                    $tm_tmp = strtotime($row['operation_date']);
                    continue;
                }elseif ($row['operation_name'] == "CLOSE"){
                    continue;
                }
                $is_first_open = false;
            }

            $tm = strtotime($row['operation_date']);
            if ($tm >= $begin && $tm < $end){

                if($row['operation_name'] == "CLOSE"){
                    $read_time[$row['page_no']] += ($tm-$tm_tmp);
                    $open_book = false;
                }

                if($open_book == true){
                    $read_time[$row['page_no']] += ($tm-$tm_tmp);
                    $tm_tmp = $tm;
                }

                if ($row['operation_name'] == "OPEN"){
                    $tm_tmp = $tm;
                    $open_book = true;
                }

            }
        }

        $jsonString->children[$i]= new JsonObject();
        $jsonString->children[$i]->id = $student_l[$i];
        $jsonString->children[$i]->children = array();

        foreach ($read_time as $key => $value){

            $jsonString->children[$i]->children[$key] = new JsonObject();
            $jsonString->children[$i]->children[$key]->id = $key;
            $jsonString->children[$i]->children[$key]->size = $value ;

        }

    }


    file_put_contents('data/' . $student . '_heatmap.json', json_encode($jsonString));


//    SSHTunnel::stop();
    exit;
}





