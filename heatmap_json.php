<?php
//Logincheck.phpと同じローカルの仮ユーザーログイン検証、
//BookRollのデータベースにアクセスし、タイムスタンプで閲覧時間に変更、
//ユーザーごとにJson形式（data/ユーザーID_heatmap.json）に保存

date_default_timezone_set('Asia/tokyo');

//TODO：BookRollのデータベースにSSHトンネル立てるクラス、現在利用なし、
//原因；BookRollのデータベースにアクセスするには、コマンドで実行する以後、Passphraseを入力する2つのステップが必要、
//LinuxでBatコマンドで実行する可能が、PHPのexecの関数で実行不可能

//代替：ターミナルでSSHトンネル作成のコマンド入力、BookRollのデータベースにアクセス
require_once "comm_function.php";

//Jsonの形式でデータ保存のクラス
class JsonObject
{

}
session_start();

//POSTでユーザーID,始まり時間と終わり時間を獲得
$student = $_SESSION["logined_lms_userid"];
$group_member = $_SESSION["group_member_lmsuserid"];
$group_id = $_SESSION["groupid"];
$course_id = $_SESSION["cqchat_courseid"];
$cqchat_id = $_SESSION["cqchat_id"];
$id_name = $_SESSION["result"] ;


list($begin, $end) = get_begin_end($_POST['begin'], $_POST['end']);

//ログ保存　"log/user_ud.csv"
clientlog($student, $group_id,$cqchat_id,$course_id,$group_member,"heatmap",$begin,$end);

$student_l = array();

$student_l = explode(',', $group_member);

$student_name_l = array();

foreach ($student_l as $value){
    $student_name_l[$value] = $id_name[$value];
}

echo "success";

//トンネルのセッティングにより、BookRollのデータベースにアクセス
$dsn_bookr = 'mysql:dbname=bookroll;host=127.0.0.1;port=3307;charset=utf8';
//$dsn_bookr = 'mysql:dbname=bookroll;host=192.168.100.13;port=3306;charset=utf8';
$user_bookr = 'student';
$password_bookr = 'ledsbr';

try {
    $dsn_bookr = new PDO($dsn_bookr, $user_bookr, $password_bookr);
} catch (PDOException $e) {
    echo "接続失敗: " . $e->getMessage() . "\n";
} finally {

    $jsonString = new JsonObject();
    //TODO：テスト用の'1'を修正
    $jsonString->id = 'グループ '. $group_id;    //change $_POST
    $jsonString->children = array();

    // Collect pages from the material
    $page_images = array();
    $page_no = 0;
    $version = '';
    $viewer_url = '';

//    $page_markers = array();
    $select_cour_page = <<<ss
        SELECT * FROM bookroll.br_contents_file 
        left join bookroll.br_contents on bookroll.br_contents_file.contents_id = bookroll.br_contents.contents_id 
        where bookroll.br_contents.title = '$course_id'
ss;
    $result_page = $dsn_bookr->query($select_cour_page);
//    foreach($result_page as $line){
//        $time = $line['created'];
//        $tt = $line['title'];
//        $page_no = $line['page'];
//        $version = $line['version'];
//        $viewer_url = $line['viewer_url'];
//    }
    foreach($result_page as $line){
        $time = $line['created'];
        if (strtotime($time)>strtotime(date("2020-01-01 00:00:00"))){
            $page_no = $line['page'];
            $version = $line['version'];
            $viewer_url = $line['viewer_url'];
        }
    }

    $bookroll_host = 'la.ait.kyushu-u.ac.jp/qu/bookroll';


    for ($i = 1; $i <= $page_no; $i++) {
        $tmp = array();
        array_push($tmp, $i);
        array_push($tmp, "https://{$bookroll_host}/contents/unzipped/{$viewer_url}_{$version}/OPS/images/out_{$i}.jpg");
        array_push($page_images, $tmp);
    }


    $filename = "setting_csv/".$course_id.".csv";
    if (! file_exists ( $filename )) {
        $f = fopen($filename, "w");
        foreach ($page_images as $line) {
            fputcsv($f, $line);
        }
        fclose($f);
    }

    for ($i = 0; $i < count($student_l); $i++) {


        //TODO：ユーザーIDごとにoperationを検索するSQLコマンド、
        //%課題協学第1回%はテスト用、コース名を表す変数に変更
        $select_cour_st = <<<ss
        SELECT * FROM bookroll.br_event_log 
        left join bookroll.br_contents on bookroll.br_event_log.contents_id = bookroll.br_contents.contents_id 
        where bookroll.br_contents.title = '$course_id' AND bookroll.br_event_log.user_id = '$student_l[$i]@FE290BBB-CB35-A016-DE38-DE8E06D6D7A7'
ss;
        $result = $dsn_bookr->query($select_cour_st);

        $read_time = array();
        for ($t = 1; $t < $page_no + 1; $t++){  //check
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
        $jsonString->children[$i]->id = $student_name_l[$student_l[$i]];
        $jsonString->children[$i]->children = array();

        foreach ($read_time as $key => $value){

            $jsonString->children[$i]->children[$key] = new JsonObject();
            $jsonString->children[$i]->children[$key]->id = $key;
            $jsonString->children[$i]->children[$key]->size = $value ;

        }

    }


    file_put_contents('data/' . $student . '_heatmap.json', json_encode($jsonString));



    $dsn_bookr = null;
    $result = null;
//    SSHTunnel::stop();
    exit;
}





