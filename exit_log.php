<?php

date_default_timezone_set('Asia/tokyo');

session_start();

require_once "comm_function.php";

$student = $_SESSION["logined_lms_userid"];//ユーザーID
$group_member = $_SESSION["group_member_lmsuserid"];//グループメンバーのID
$group_id = $_SESSION["groupid"];//グループのID
$course_id = $_SESSION["cqchat_courseid"];//グループのID
$cqchat_id = $_SESSION["cqchat_id"];
$id_name = $_SESSION["result"] ;//実名とBookRollのIDの配列
$function = $_POST['function'];//readかsearch
$course_name = $_POST['course_pick']?$_POST['course_pick']:$course_id;//スライド名

//タイムスタンプに変換
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


//ログ保存　"log/user_ud.csv"
clientlog($student, $group_id,$cqchat_id,$course_name,$group_member,"exit",$begin,$end,$function);