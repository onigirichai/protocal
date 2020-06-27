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
            <h1> 個人学習・参加度の可視化システム</h1>
        </div>
    </div>
    <!--    ナビゲーション・バー（ログイン、授業前の個人学習、授業中の発言分類結果）-->
    <ul style="margin-bottom: 50px" class="nav nav-tabs">
        <li role="presentation" class="active">
            <a class="nav-link active" href="#">ログイン</a>
        </li>
        <li role="presentation" class="dropdown">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="true">授業前の個人学習<span class="caret"></span></a>

            <ul class="dropdown-menu">
                <li ><a class="dropdown-item" href="bookq_behavior.html">BookRoll学習活動</a></li>
                <li role="separator" class="divider"></li>
                <li><a class="dropdown-item" href="heatmap.html">教材の閲覧時間</a></li>
            </ul>
        </li>
        <li role="presentation">
            <a class="nav-link" href="discussion.html">授業中の発言分類結果</a>
        </li>

        <li role="presentation" style="margin-left: 150px; margin-top: 10px">
            <img src='images/user_s_icon.png' alt= 'user_icon'>
        </li>
        <li id="st-status" role="presentation" style="margin-left: 20px; margin-top: 10px"></li>
    </ul>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h3 id = "user_id" style="margin-left: 30px; margin-top: 10px"></h3>
        </div>
        <div class="col-md-12">
            <h3 id = "course_id" style="margin-left: 30px; margin-top: 10px"></h3>
        </div>
        <div class="col-md-12">
            <h3 id = "group_id" style="margin-left: 30px; margin-top: 10px"></h3>
        </div>
    </div>
</div>
<?php
//
//setcookie("SESSION", $_COOKIE['SESSION']);
//
//?>

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

        //id to name

        $result = array();

        if (($handle = fopen("data/lti_list.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle))) {
                $data[2] = str_replace(["\r\n", "\r", "\n"], '', $data[2]);
                $result[$data[1]] = $data[2];
                }
            }
            fclose($handle);

        $course_begin = array();
        $course_end = array();

        if (($handle = fopen("data/course_time.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle))) {

                $data[1] = str_replace(["\r\n", "\r", "\n"], '', $data[1]);
                $course_begin[$data[0]] = $data[1];

                $data[2] = str_replace(["\r\n", "\r", "\n"], '', $data[2]);
                $course_end[$data[0]] = $data[2];
            }
        }
        fclose($handle);



        $group_member_l = explode(',', $group_member_lmsuserid);

        $group_member_str = "";

        $flag = true;
        foreach ($group_member_l as $value){
            if ($flag){
                $group_member_str .= '  '.$result[$value];
                $flag = false;
            }else{
                $group_member_str .= ', '.$result[$value];
            }
        }

        $_SESSION["logined_cqchat_userid"] = $user_id;
        $_SESSION["clicked_cqchat_userid"] = $clicked_user_id;
        $_SESSION["logined_lms_userid"] = $user_lmsid;
        $_SESSION["clicked_lms_userid"] = $clicked_user_lmsid;
        $_SESSION["cqchat_id"] = $cqchat_id;
        $_SESSION["cqchat_courseid"] = $cqchat_courseid;
        $_SESSION["groupid"] = $groupid;
        $_SESSION["group_member_id"] = $group_member_id;
        $_SESSION["group_member_lmsuserid"] = $group_member_lmsuserid;

        if (array_key_exists($user_lmsid, $result)){
            $_SESSION["logined_lms_username"] = $result[$user_lmsid];
        }else{
            echo "<script> alert(\"未登録\");</script>";
        }

        $_SESSION["group_member_id_list"] = $group_member_str;
        $_SESSION["result"] = $result;

        $_SESSION["course_begin"] = $course_begin[$cqchat_courseid];
        $_SESSION["course_end"] = $course_end[$cqchat_courseid];

        $_SESSION["all"] = $_POST;

    }

?>

</body>

<script type="text/javascript">

    function load_page() {

        name_st = "<?php echo $_SESSION["logined_lms_username"]; ?>";
        course = sessionStorage.getItem("cqchat_courseid");
        groupid = "<?php echo $_SESSION["group_member_id_list"]; ?>";

        // $("#st-status").html(st);
        // $("#user_id").html("<img src='images/user_icon.png' alt= 'user_icon'>"　+ ' ' +　st);
        $("#st-status").html(name_st);
        $("#user_id").html("<img src='images/user_icon.png' alt= 'user_icon'>"　+ '  ' +　name_st);
        $("#course_id").html("<img src='images/course_icon.png' alt= 'course_icon'>"　+ '  ' +　course);
        $("#group_id").html("<img src='images/group_icon.png' alt= 'group_icon'>" + groupid);
    }


    sessionStorage.setItem("logined_cqchat_userid",<?php echo $_SESSION["logined_cqchat_userid"]; ?>);
    sessionStorage.setItem("clicked_cqchat_userid",<?php echo $_SESSION["clicked_cqchat_userid"]; ?>);
    sessionStorage.setItem("logined_lms_userid",<?php echo $_SESSION["logined_lms_userid"]; ?>);
    sessionStorage.setItem("clicked_lms_userid",<?php echo $_SESSION["clicked_lms_userid"]; ?>);
    sessionStorage.setItem("cqchat_id",<?php echo $_SESSION["cqchat_id"]; ?>);
    sessionStorage.setItem("cqchat_courseid",<?php echo $_SESSION["cqchat_courseid"]; ?>);
    sessionStorage.setItem("groupid",<?php echo $_SESSION["groupid"]; ?>);
    sessionStorage.setItem("name_st","<?php echo $_SESSION["logined_lms_username"]; ?>");

    sessionStorage.setItem("course_begin","<?php echo $_SESSION["course_begin"]; ?>");
    sessionStorage.setItem("course_end","<?php echo $_SESSION["course_end"]; ?>");



    // function getCSV() {
    //     return new Promise(function (resolve) {
    //         req = new XMLHttpRequest();
    //         req.open("get", "data/lti_list.csv",true);
    //
    //
    //         req.onload = function () {
    //             lti_dict = convertCSVtoDict(req.responseText);
    //             resolve(lti_dict)
    //         };
    //         req.onerror = function () {
    //             console.log("error");
    //         };
    //
    //         req.send(null);
    //     })
    //
    // }
    //
    // function convertCSVtoDict(str) {
    //     result = {};
    //     tmp = str.split("\n");
    //     for(i=0;i<tmp.length;++i){
    //         tmp_list = tmp[i].split(',');
    //         result[tmp_list[1]] = tmp_list[2]
    //     }
    //
    //     return result;
    //
    // }
</script>



