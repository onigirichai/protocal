<!--
バグジョシステムのホームページ
各メンバーのプロフを表示する
POSTデータに対応するデータを受け取る機能
-->
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>バグジョ</title>

    <script src="js/jquery_2.1.1_jquery.js"></script>
    <script src="js/bootstrap_3.3.6_js_bootstrap.js"></script>
    <link href="css/bootstrap_3.3.6_css_bootstrap.css" rel="stylesheet"/>

</head>

<body onload="load_page()">

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1> バグジョ・システム</h1>
        </div>
    </div>
    <!--    ナビゲーション・バー（ログイン、授業前、授業中）-->
    <ul style="margin-bottom: 50px" class="nav nav-tabs">
        <li role="presentation" class="active">
            <a class="nav-link active" href="#">ログイン</a>
        </li>
        <li role="presentation" class="dropdown">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="true">授業前<span class="caret"></span></a>

            <ul class="dropdown-menu">
                <li ><a class="dropdown-item" href="bookq_behavior.html">BookRoll学習活動</a></li>
                <li role="separator" class="divider"></li>
                <li><a class="dropdown-item" href="heatmap.html">ヒートマップ</a></li>
            </ul>
        </li>
        <li role="presentation">
            <a class="nav-link" href="discussion.html">授業中</a>
        </li>

        <li role="presentation" style="margin-left: 150px; margin-top: 10px">
            ログインID
        </li>
        <li id="st-status" role="presentation" style="margin-left: 20px; margin-top: 10px"></li>
    </ul>



<?php

    session_start();
    //POSTデータをセッションとして保存

    if(isset($_POST["logined_cqchat_userid"])){
        $user_id = $_POST["logined_cqchat_userid"];
        $clicked_user_id = $_POST["clicked_cqchat_userid"];
        $user_lmsid = $_POST["logined_lms_userid"];
        $clicked_user_lmsid = $_POST["clicked_lms_userid"];
        $cqchat_id = $_POST["cqchat_id"];
        $cqchat_courseid = $_POST["cqchat_courseid"];
        $groupid = $_POST["groupid"];
        $group_member_id = $_POST["group_member_id"];
        $group_member_lmsuserid = $_POST["group_member_lmsuserid"];

        $_SESSION["logined_cqchat_userid"] = $user_id;
        $_SESSION["clicked_cqchat_userid"] = $clicked_user_id;
        $_SESSION["logined_lms_userid"] = $user_lmsid;
        $_SESSION["clicked_lms_userid"] = $clicked_user_lmsid;
        $_SESSION["cqchat_id"] = $cqchat_id;
        $_SESSION["cqchat_courseid"] = $cqchat_courseid;
        $_SESSION["groupid"] = $groupid;
        $_SESSION["group_member_id"] = $group_member_id;
        $_SESSION["group_member_lmsuserid"] = $group_member_lmsuserid;
        $_SESSION["all"] = $_POST;

    }

?>

</body>

<script type="text/javascript">
//    保存
    function load_page() {
        var st = sessionStorage.getItem("logined_lms_userid");
        var begin = sessionStorage.getItem("datepicker_begin");
        var end = sessionStorage.getItem("datepicker_end");

        $("#st-status").html(st);
    }
    sessionStorage.setItem("logined_cqchat_userid",<?php echo $_SESSION["logined_cqchat_userid"]; ?>);
    sessionStorage.setItem("clicked_cqchat_userid",<?php echo $_SESSION["clicked_cqchat_userid"]; ?>);
    sessionStorage.setItem("logined_lms_userid",<?php echo $_SESSION["logined_lms_userid"]; ?>);
    sessionStorage.setItem("clicked_lms_userid",<?php echo $_SESSION["clicked_lms_userid"]; ?>);
    sessionStorage.setItem("cqchat_id",<?php echo $_SESSION["cqchat_id"]; ?>);
    sessionStorage.setItem("cqchat_courseid",<?php echo $_SESSION["cqchat_courseid"]; ?>);
    sessionStorage.setItem("groupid",<?php echo $_SESSION["groupid"]; ?>);

</script>

