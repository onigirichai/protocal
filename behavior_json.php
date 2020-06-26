<?php
//Logincheck.phpと同じローカルの仮ユーザーログイン検証、
//BookRollのデータベースにアクセスし、operationを集計し、
//ユーザーごとにJson形式（data/ユーザーID_behavior.json）に保存

date_default_timezone_set('Asia/tokyo');

//TODO：BookRollのデータベースにSSHトンネル立てるクラス、現在利用なし、
//原因；BookRollのデータベースにアクセスするには、コマンドで実行する以後、Passphraseを入力する2つのステップが必要、
//LinuxでBatコマンドで実行する可能が、PHPのexecの関数で実行不可能

//代替：ターミナルでSSHトンネル作成のコマンド入力、BookRollのデータベースにアクセス
require_once 'SSHTunnel.php';

//Jsonの形式でデータ保存のクラス
class JsonObject{

}

session_start();

//POSTでユーザーID,始まり時間と終わり時間を獲得
$student = $_SESSION["logined_lms_userid"];
$group_member = $_SESSION["group_member_lmsuserid"];
$group_id = $_SESSION["groupid"];
$id_name = $_SESSION["result"] ;

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

$student_l = array();

$student_l = explode(',', $group_member);

$student_name_l = array();

foreach ($student_l as $value){
    $student_name_l[$value] = $id_name[$value];
}

echo "success";

//トンネルのセッティングにより、BookRollのデータベースにアクセス
$dsn_bookr = 'mysql:dbname=bookroll;host=127.0.0.1;port=3307';
//$dsn_bookr = 'mysql:dbname=bookroll;host=192.168.100.13;port=3306';
$user_bookr = 'student';
$password_bookr = 'ledsbr';

try {
    $dsn_bookr = new PDO($dsn_bookr, $user_bookr , $password_bookr);
} catch (PDOException $e) {
    echo "接続失敗: " . $e->getMessage() . "\n";
}finally{

    $jsonString = new JsonObject();

    $jsonString->id = 'グループ '. $group_id;    //change $_POST
    $jsonString->children = array();

    for ($i = 0; $i < count($student_l); $i++){
//        $timestamp = array();
        $operation = array();

        //TODO：ユーザーIDごとにoperationを検索するSQLコマンド、
        //%課題協学第1回%はテスト用、コース名を表す変数に変更
        $select_cour_st = <<<ss
        SELECT * FROM bookroll.br_event_log 
        left join bookroll.br_contents on bookroll.br_event_log.contents_id = bookroll.br_contents.contents_id 
        where bookroll.br_contents.title like N'%課題協学第1回%' AND bookroll.br_event_log.user_id = '$student_l[$i]@FE290BBB-CB35-A016-DE38-DE8E06D6D7A7'
ss;
        $result = $dsn_bookr->query($select_cour_st);

        //タイムスタンプとoperationネームを獲得
        foreach($result as $row){
            $tm = strtotime($row['operation_date']);
            if ($tm >= $begin && $tm < $end){
                array_push($operation,$row['operation_name']);
            }
        }
        $op_count = array_count_values($operation);
        $jsonString->children[$i]= new JsonObject();
        $jsonString->children[$i]->id = $student_name_l[$student_l[$i]];
        $jsonString->children[$i]->children = array();

        //TODO：if文をfor文に変更、ソースコード洗練
        for ($j = 0; $j<3; $j++){
            $operation_count = 0;
            if ( array_key_exists('ADD MEMO', $op_count) ) {
                $jsonString->children[$i]->children[$operation_count] = new JsonObject();
                $jsonString->children[$i]->children[$operation_count]->id = 'メモ';
                $jsonString->children[$i]->children[$operation_count]->size = $op_count['ADD MEMO'] ;
                $operation_count++;
            }
            if ( array_key_exists('ADD MARKER', $op_count) ) {
                $jsonString->children[$i]->children[$operation_count] = new JsonObject();
                $jsonString->children[$i]->children[$operation_count]->id = 'マーカー';
                $jsonString->children[$i]->children[$operation_count]->size = $op_count['ADD MARKER'] ;
                $operation_count++;
            }
            if ( array_key_exists('ADD BOOKMARK', $op_count) ) {
                $jsonString->children[$i]->children[$operation_count] = new JsonObject();
                $jsonString->children[$i]->children[$operation_count]->id = 'ブックマーク';
                $jsonString->children[$i]->children[$operation_count]->size = $op_count['ADD BOOKMARK'] ;
                $operation_count++;
            }

        }

    }


    file_put_contents('data/'.$student.'_behavior.json', json_encode($jsonString));

    $dsn_bookr = null;
    $result = null;

//    SSHTunnel::stop();
    exit;
}





