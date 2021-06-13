<?php

date_default_timezone_set('Asia/tokyo');

session_start();

require_once "comm_function.php";

$student = $_SESSION["logined_lms_userid"];
$group_member = $_SESSION["group_member_lmsuserid"];
$group_id = $_SESSION["groupid"];
$course_id = $_SESSION["cqchat_courseid"];
$cqchat_id = $_SESSION["cqchat_id"];
$id_name = $_SESSION["result"] ;
$function = $_POST['function'];
$course_name = $_POST['course_pick']?$_POST['course_pick']:$course_id;

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