<?php
//Logincheck.phpと同じローカルの仮ユーザーログイン検証、
//仮のCQCHATに集計されたmdl_cqchat_social_presence_pointのテーブルにアクセスし、ポイントを集計し、
//ユーザーごとにJson形式（data/ユーザーID_discussion.json）に保存

date_default_timezone_set('Asia/tokyo');

require_once 'SSHTunnel.php';

class JsonObject{

}
session_start();

$student = $_SESSION["logined_lms_userid"];
$group_member = $_SESSION["group_member_lmsuserid"];
$tmp_all = $_SESSION["all"];

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

//$dsn = 'mysql:dbname=bagujo;host=127.0.0.1;port=3306';
//$dbuser = 'onigiri';
//$dbpassword = 'angelfantuan';

$student_l = array();

$student_l = explode(',', $group_member);

echo "success";

$dis_st = array();

foreach ($student_l as $st){
    $dis_st[$st] = array();
}

$userid_timestamp = array_keys($tmp_all);

$tmp = array_keys($userid_timestamp,"common_groupingid");

for($i = $tmp[0] + 1; $i<count($tmp_all);$i++){
    $tmp_ts_id = explode('_',$userid_timestamp[$i]);
    $tmp_timestamp = $tmp_ts_id[count($tmp_ts_id)-1];
    $tmp_member_id = $tmp_ts_id[count($tmp_ts_id)-2];
    array_push($dis_st[$tmp_member_id], $tmp_timestamp.','.$tmp_all[$userid_timestamp[$i]]);
}

$dis_l = array("thread", "reference", "quoting", "question", "appreciation", "agreement", "disagreement", "advice");

$dis_jp = array("thread", "reference", "quoting", "question", "appreciation", "agreement", "disagreement", "advice");

$jsonString = new JsonObject();
$jsonString->id = 'グループ '.$_SESSION["groupid"];    //change $_POST
$jsonString->children = array();

for ($i = 0; $i < count($student_l); $i++){

    $discussion = array(
        "thread" => 0,
        "reference" => 0,
        "quoting" => 0,
        "question" => 0,
        "appreciation" => 0,
        "agreement" => 0,
        "disagreement" => 0,
        "advice" => 0
    );

    foreach($dis_st[$student_l[$i]] as $value){
        $tmp_ts_socialvalue = explode(',',$value);
        $timestamp = $tmp_ts_socialvalue[0];
        if ($timestamp >= $begin && $timestamp < $end){
            for ($d = 0; $d < count($dis_l); $d++){
                $discussion[$dis_l[$d]] += $tmp_ts_socialvalue[$d + 6];
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

echo urldecode( json_encode($jsonString));
file_put_contents('data/'.$student.'_discussion.json', json_encode($jsonString));


//    SSHTunnel::stop();
exit;
