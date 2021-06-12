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
                <li ><a class="dropdown-item" href="behavior.php">BookRoll学習活動</a></li>
                <li role="separator" class="divider"></li>
                <li><a class="dropdown-item" href="heatmap.php">教材の閲覧時間</a></li>
            </ul>
        </li>
        <li role="presentation">
            <a class="nav-link" href="discussion.php">授業中の発言分類結果</a>
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
            <h3 style="margin-left: 30px; margin-top: 10px"><strong>基本情報</strong></h3>
        </div>
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

<img src="images/system_intro_1.png", alt="system_intro", style="margin-left: 50px">
<?php
//
//setcookie("SESSION", $_COOKIE['SESSION']);
//
//?>

<?php

    session_start();
    //POSTデータをセッションとして保存
    //var_dump($_POST);
    if(isset($_POST["logined_cqchat_userid"])){
        $user_id = $_POST["logined_cqchat_userid"];
        $clicked_user_id = $_POST["clicked_cqchat_userid"];
        $user_lmsid = $_POST["logined_lms_userid"];
        $clicked_user_lmsid = $_POST["clicked_lms_userid"];
        $cqchat_id = $_POST["cqchat_id"];
        $cqchat_name = $_POST["cqchat_name"];
        $groupid = $_POST["groupid"];
        $group_member_id = $_POST["group_member_id"];
        $group_member_lmsuserid = $_POST["group_member_lmsuserid"];

        $group_member_username = $_POST["group_member_username"];

        //get course name
        $context_label = substr($_POST["context_label"],0,4);


        //id to name

        $result = array();

        $group_member_lmsuserid_l = explode(',', $group_member_lmsuserid);
        $group_member_username_l = explode(',', $group_member_username);

        for($i = 0; $i<count($group_member_lmsuserid_l);$i++){
            $result[$group_member_lmsuserid_l[$i]]=$group_member_username_l[$i];
        }

        $course_begin = array();
        $course_end = array();


        $course_name = array();
        $dis_title = array();
        $cqchat_courseid = "";

//        if (($handle = fopen("setting_csv/course_name.csv", "r")) !== FALSE) {
//            while (($data = fgetcsv($handle))) {
//                $data[1] = str_replace(["\r\n", "\r", "\n"], '', $data[1]);
//                $tmp = explode(',', $data[0]);
//                $course_name[$tmp[1]] = $tmp[0];
//                $dis_title[$tmp[1]] = $data[1];
//            }
//            $tmp_key = array_keys($dis_title, $cqchat_name)[0];
//            $cqchat_courseid = $course_name[$tmp_key];
//        }
//        fclose($handle);

        if (($handle = fopen("setting_csv/course_name.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle))) {
                $data[2] = str_replace(["\r\n", "\r", "\n"], '', $data[2]);
                $tmp = explode(',', $data[0]);
                $course_name[$data[1]] = $data[0];
                $dis_title[$data[1]] = $data[2];
            }
            $tmp_key = array_keys($dis_title, $cqchat_name)[0];
            $cqchat_courseid = $course_name[$tmp_key];
        }
        fclose($handle);

        if (($handle = fopen("setting_csv/course_time.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle))) {

                $data[1] = str_replace(["\r\n", "\r", "\n"], '', $data[1]);
                $course_begin[$data[0]] = $data[1];

                $data[2] = str_replace(["\r\n", "\r", "\n"], '', $data[2]);
                $course_end[$data[0]] = $data[2];

                $tmp_key = array_keys($dis_title, $cqchat_name)[0];
                $_SESSION["course_begin"] = $course_begin[$tmp_key];
                $_SESSION["course_end"] = $course_end[$tmp_key];
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


        if (array_key_exists($clicked_user_lmsid, $result)){
            $_SESSION["clicked_lms_username"] = $result[$clicked_user_lmsid];
            $_SESSION["logined_cqchat_userid"] = $user_id;
            $_SESSION["clicked_cqchat_userid"] = $clicked_user_id;
            $_SESSION["logined_lms_userid"] = $user_lmsid;
            $_SESSION["clicked_lms_userid"] = $clicked_user_lmsid;
            $_SESSION["cqchat_id"] = $cqchat_id;
            $_SESSION["cqchat_courseid"] = $cqchat_courseid;
            $_SESSION["groupid"] = $groupid;
            $_SESSION["group_member_id"] = $group_member_id;
            $_SESSION["group_member_lmsuserid"] = $group_member_lmsuserid;
            $_SESSION["group_member_id_list"] = $group_member_str;
            $_SESSION["result"] = $result;



            $_SESSION["cqchat_name"] = $cqchat_name;
            $_SESSION["course_name"] = $course_name;
            $_SESSION["context_label"] = $context_label;

            $_SESSION["all"] = $_POST;

            $userid_timestamp = array_keys($_POST);


            //get discussion begin and end's index
            $sp_index_begin = array_keys($userid_timestamp,"common_groupingid");
            $sp_index_end = array_keys($userid_timestamp,"cqchat_name");


            $timestamp_l = array();

            for($i = $sp_index_begin[0] + 1; $i<$sp_index_end[0];$i++){
                $tmp_ts_id = explode('_',$userid_timestamp[$i]);

                $tmp_timestamp = $tmp_ts_id[count($tmp_ts_id)-1];
                array_push($timestamp_l, $tmp_timestamp);
            }

            $_SESSION["dis_begin"] = date ( 'Y-m-d H:i:s' ,min($timestamp_l));
            $_SESSION["dis_end"] = date ( 'Y-m-d H:i:s' ,max($timestamp_l));

        }else{
            echo "<script> alert(\"未登録\");</script>";
            $_SESSION = array();
            session_destroy();
        }



    }

?>

</body>

<script type="text/javascript">

    function load_page() {

        name_st = "<?php echo $_SESSION["clicked_lms_username"]; ?>";
        course = sessionStorage.getItem("cqchat_courseid");
        groupid = "<?php echo $_SESSION["group_member_id_list"]; ?>";

        // $("#st-status").html(st);
        // $("#user_id").html("<img src='images/user_icon.png' alt= 'user_icon'>"　+ ' ' +　st);
        $("#st-status").html(name_st);
        $("#user_id").html("<img src='images/user_icon.png' alt= 'user_icon'>"　+ '  ' +　name_st);
        $("#course_id").html("<img src='images/course_icon.png' alt= 'course_icon'>"　+ '  ' +　course);
        $("#group_id").html("<img src='images/group_icon.png' alt= 'group_icon'>" + groupid);
    }

    console.log("<?php echo $_SESSION["cqchat_name"]; ?>");
    sessionStorage.setItem("logined_cqchat_userid",<?php echo $_SESSION["logined_cqchat_userid"]; ?>);
    sessionStorage.setItem("clicked_cqchat_userid",<?php echo $_SESSION["clicked_cqchat_userid"]; ?>);
    sessionStorage.setItem("logined_lms_userid",<?php echo $_SESSION["logined_lms_userid"]; ?>);
    sessionStorage.setItem("clicked_lms_userid",<?php echo $_SESSION["clicked_lms_userid"]; ?>);
    sessionStorage.setItem("cqchat_id",<?php echo $_SESSION["cqchat_id"]; ?>);
    sessionStorage.setItem("cqchat_courseid","<?php echo $_SESSION["cqchat_courseid"]; ?>");
    sessionStorage.setItem("groupid",<?php echo $_SESSION["groupid"]; ?>);
    sessionStorage.setItem("name_st","<?php echo $_SESSION["clicked_lms_username"]; ?>");

    sessionStorage.setItem("course_begin","<?php echo $_SESSION["course_begin"]; ?>");
    sessionStorage.setItem("course_end","<?php echo $_SESSION["course_end"]; ?>");
    sessionStorage.setItem("cqchat_name", "<?php echo $_SESSION["cqchat_name"]; ?>");
    sessionStorage.setItem("context_label", "<?php echo $_SESSION["context_label"]; ?>");



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



