<?php
//Logincheck.phpと同じローカルの仮ユーザーログイン検証、
//仮のCQCHATに集計されたmdl_cqchat_social_presence_pointのテーブルにアクセスし、ポイントを集計し、
//ユーザーごとにJson形式（data/ユーザーID_discussion.json）に保存

date_default_timezone_set('Asia/tokyo');

require_once 'comm_function.php';

class JsonObject{

}
session_start();



$student = $_SESSION["logined_lms_userid"];
$group_member = $_SESSION["group_member_lmsuserid"];
$group_id = $_SESSION["groupid"];
$course_id = $_SESSION["cqchat_courseid"];
$cqchat_id = $_SESSION["cqchat_id"];
$id_name = $_SESSION["result"] ;
$tmp_all = $_SESSION["all"];

list($begin, $end) = get_begin_end($_POST['begin'], $_POST['end']);

//ログ保存　"log/user_ud.csv"
clientlog($student, $group_id,$cqchat_id,$course_id,$group_member,"discussion",$begin,$end);

//$dsn = 'mysql:dbname=bagujo;host=127.0.0.1;port=3306';
//$dbuser = 'onigiri';
//$dbpassword = 'angelfantuan';

$student_l = array();

$student_l = explode(',', $group_member);

$student_name_l = array();

foreach ($student_l as $value){
    $student_name_l[$value] = $id_name[$value];
}

echo "success";

$dis_st = array();

foreach ($student_l as $st){
    $dis_st[$st] = array();
}

$userid_timestamp = array_keys($tmp_all);

$tmp = array_keys($userid_timestamp,"common_groupingid");

$timestamp_l = array();

for($i = $tmp[0] + 1; $i<count($tmp_all)-2;$i++){
    $tmp_ts_id = explode('_',$userid_timestamp[$i]);

    $tmp_timestamp = $tmp_ts_id[count($tmp_ts_id)-1];
    array_push($timestamp_l, $tmp_timestamp);

    $tmp_member_id = $tmp_ts_id[count($tmp_ts_id)-2];
    array_push($dis_st[$tmp_member_id], $tmp_timestamp.','.$tmp_all[$userid_timestamp[$i]]);
}

$_SESSION["dis_begin"] = date ( 'Y-m-d H:i:s' ,min($timestamp_l));
$_SESSION["dis_end"] = date ( 'Y-m-d H:i:s' ,max($timestamp_l));

$dis_l = array("emotion","humor","selfdisclosure","paralanguage","value","thread", "reference", "quoting", "question", "appreciation", "agreement", "disagreement", "advice","vocatives","inclusive","phatics","social_sharing","reflection");

$dis_jp = array("感情","ユーモア","自己開示","パラ言語","価値","スレッド", "参照", "引用", "質問", "感謝", "同意", "不同意", "提案", "呼格", "集団言葉", "挨拶", "情報共有", "省察");

$jsonString = new JsonObject();
$jsonString->id = 'グループ '.$group_id;    //change $_POST
$jsonString->children = array();

for ($i = 0; $i < count($student_l); $i++){

    $discussion = array(
        "emotion" => 0,
        "humor" => 0,
        "selfdisclosure" => 0,
        "paralanguage" => 0,
        "value" => 0,
        "thread" => 0,
        "reference" => 0,
        "quoting" => 0,
        "question" => 0,
        "appreciation" => 0,
        "agreement" => 0,
        "disagreement" => 0,
        "advice" => 0,
        "vocatives" => 0,
        "inclusive" => 0,
        "phatics" => 0,
        "social_sharing" => 0,
        "reflection" => 0
    );

    foreach($dis_st[$student_l[$i]] as $value){
        $tmp_ts_socialvalue = explode(',',$value);
        $timestamp = $tmp_ts_socialvalue[0];
        if ($timestamp >= $begin && $timestamp < $end){
            for ($d = 0; $d < count($dis_l); $d++){
                $discussion[$dis_l[$d]] += $tmp_ts_socialvalue[$d + 2];
            }
        }
    }

    $jsonString->children[$i]= new JsonObject();
    $jsonString->children[$i]->id = $student_name_l[$student_l[$i]];
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

$dsn_bookr = null;
$result = null;
//    SSHTunnel::stop();
exit;
