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

<body>

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

        <li id="st-status" style="margin-left: 150px; margin-top: 10px">
        </li>

        <li id="logout" style="margin-left: 20px">
            <button type="button" class="btn btn-danger" onclick="logout()">ログアウト</button>
        </li>
    </ul>



<?php

    session_start();
    //TODO セッションとして保存、テスト用
    if(!isset($_SESSION["logined_cqchat_userid"])){
        $user_id = $_POST["logined_cqchat_userid"];
        $clicked_user_id = $_POST["clicked_cqchat_userid"];
        $cqchat_id = $_POST["cqchat_id"];
        $cqchat_courseid = $_POST["cqchat_courseid"];
        $groupid = $_POST["groupid"];
        $group_member_id = $_POST["group_member_id"];

        $_SESSION["logined_cqchat_userid"] = $user_id;
        $_SESSION["clicked_cqchat_userid"] = $clicked_user_id;
        $_SESSION["cqchat_id"] = $cqchat_id;
        $_SESSION["cqchat_courseid"] = $cqchat_courseid;
        $_SESSION["groupid"] = $groupid;
        $_SESSION["group_member_id"] = $group_member_id;
        $_SESSION["all"] = $_POST;

    }

?>

</body>

<script>
    localStorage.setItem("logined_cqchat_userid",<?php echo $_SESSION["logined_cqchat_userid"]; ?>)
    localStorage.setItem("clicked_cqchat_userid",<?php echo $_SESSION["clicked_cqchat_userid"]; ?>)
    localStorage.setItem("cqchat_id",<?php echo $_SESSION["cqchat_id"]; ?>)
    localStorage.setItem("cqchat_courseid",<?php echo $_SESSION["cqchat_courseid"]; ?>)
    localStorage.setItem("groupid",<?php echo $_SESSION["groupid"]; ?>)
</script>

<!--    echo "<script>localStorage.setItem(\"logined_cqchat_userid\",$user_id)</script>";-->
<!---->

