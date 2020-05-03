<?php
//Logincheck.phpと同じローカルの仮ユーザーログイン検証、
//仮のCQCHATに集計されたmdl_cqchat_social_presence_pointのテーブルにアクセスし、ポイントを集計し、
//ユーザーごとにJson形式（data/ユーザーID_discussion.json）に保存

date_default_timezone_set('Asia/tokyo');

require_once 'SSHTunnel.php';

class JsonObject{

}

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

try{
    $dbh = new PDO($dsn, $dbuser, $dbpassword);
}catch (PDOException $e){
    echo "接続失敗: " . $e->getMessage() . "\n";
}finally{

    $users_l = $dbh->query("select * from users");
    $loginstatus = "false";
    $student_l = array();
    foreach ($users_l as $row){
        //TODO：グループIDとユーザーIDの検証、テスト用の'1'を修正
        if ($row['GroupID'] == '1'){
            array_push($student_l, $row['StudentID']);
        }                            //change $_POST


        if ($row['StudentID'] == $student){
            $loginstatus = "success";
        }
    }

    echo $loginstatus;

    $dis_l = array("thread", "reference", "quoting", "question", "appreciation", "agreement");

    $dis_jp = array("スレッド", "参照", "引用", "質問", "感謝", "同意");

    $jsonString = new JsonObject();
    $jsonString->id = 'グループ 1';    //change $_POST
    $jsonString->children = array();

    for ($i = 0; $i < count($student_l); $i++){

        $discussion = array(
            "thread" => 0,
            "reference" => 0,
            "quoting" => 0,
            "question" => 0,
            "appreciation" => 0,
            "agreement" => 0
        );

        //TODO：ｓQLコマンド、ユーザーIDに対応する仮のテーブル「discussion」にあるsocial_presenceのポイント
        $select_dis = <<<ss
        SELECT * FROM discussion
        where StudentID = '$student_l[$i]'
ss;
        $result = $dbh->query($select_dis);

        //TODO：仮のテーブル「discussion」にある「DiscussionDate」というタイムスタンプを表示するカラム
        foreach($result as $row){
            $tm = strtotime($row['DiscussionDate']);
            if ($tm >= $begin && $tm < $end){
                for ($d = 0; $d < count($dis_l); $d++){
                    $discussion[$dis_l[$d]] += $row[$dis_l[$d]];
                }
            }
        }

        $jsonString->children[$i]= new JsonObject();
        $jsonString->children[$i]->id = $student_l[$i];
        $jsonString->children[$i]->children = array();

        $operation_count = 0;

        for ($j = 0; $j<count($dis_l); $j++){

            if ( $discussion[$dis_l[$j]] ) {
                $jsonString->children[$i]->children[$operation_count] = new JsonObject();
                $jsonString->children[$i]->children[$operation_count]->id = $dis_jp[$j];
                $jsonString->children[$i]->children[$operation_count]->size = $discussion[$dis_l[$j]] ;
                $operation_count++;
            }

        }

    }

//    echo urldecode( json_encode($jsonString));
    file_put_contents('data/'.$student.'_discussion.json', json_encode($jsonString));


//    SSHTunnel::stop();
    exit;
}