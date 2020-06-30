
<?php
//echo $_SERVER['HTTP_USER_AGENT'];
//echo "<br />".$_SERVER ['REMOTE_ADDR'];
//blog.csdn.net/chWow/java/article/details/73647919

date_default_timezone_set('Asia/tokyo');

//OSのバージョンの獲得
function get_os($agent) {

    if (preg_match ( '/win/i', $agent ) && strpos ( $agent, '95' )) {
        $os = 'Windows 95';
    } else if (preg_match ( '/win 9x/i', $agent ) && strpos ( $agent, '4.90' )) {
        $os = 'Windows ME';
    } else if (preg_match ( '/win/i', $agent ) && preg_match ( '/98/i', $agent )) {
        $os = 'Windows 98';
    } else if (preg_match ( '/win/i', $agent ) && preg_match ( '/nt 6.0/i', $agent )) {
        $os = 'Windows Vista';
    } else if (preg_match ( '/win/i', $agent ) && preg_match ( '/nt 6.1/i', $agent )) {
        $os = 'Windows 7';
    } else if (preg_match ( '/win/i', $agent ) && preg_match ( '/nt 6.2/i', $agent )) {
        $os = 'Windows 8';
    } else if (preg_match ( '/win/i', $agent ) && preg_match ( '/nt 10.0/i', $agent )) {
        $os = 'Windows 10'; // 添加win10判断
    } else if (preg_match ( '/win/i', $agent ) && preg_match ( '/nt 5.1/i', $agent )) {
        $os = 'Windows XP';
    } else if (preg_match ( '/win/i', $agent ) && preg_match ( '/nt 5/i', $agent )) {
        $os = 'Windows 2000';
    } else if (preg_match ( '/win/i', $agent ) && preg_match ( '/nt/i', $agent )) {
        $os = 'Windows NT';
    } else if (preg_match ( '/win/i', $agent ) && preg_match ( '/32/i', $agent )) {
        $os = 'Windows 32';
    } else if (preg_match ( '/linux/i', $agent )) {
        if(preg_match("/Mobile/", $agent)){
            if(preg_match("/QQ/i", $agent)){
                $os = "Android QQ Browser";
            }else{
                $os = "Android Browser";
            }
        }else{
            $os = 'PC-Linux';
        }
    } else if (preg_match ( '/Mac/i', $agent )) {
        if(preg_match("/Mobile/", $agent)){
            if(preg_match("/QQ/i", $agent)){
                $os = "IPhone QQ Browser";
            }else{
                $os = "IPhone Browser";
            }
        }else{
            $os = 'Mac OS X';
        }
    } else if (preg_match ( '/unix/i', $agent )) {
        $os = 'Unix';
    } else if (preg_match ( '/sun/i', $agent ) && preg_match ( '/os/i', $agent )) {
        $os = 'SunOS';
    } else if (preg_match ( '/ibm/i', $agent ) && preg_match ( '/os/i', $agent )) {
        $os = 'IBM OS/2';
    } else if (preg_match ( '/Mac/i', $agent ) && preg_match ( '/PC/i', $agent )) {
        $os = 'Macintosh';
    } else if (preg_match ( '/PowerPC/i', $agent )) {
        $os = 'PowerPC';
    } else if (preg_match ( '/AIX/i', $agent )) {
        $os = 'AIX';
    } else if (preg_match ( '/HPUX/i', $agent )) {
        $os = 'HPUX';
    } else if (preg_match ( '/NetBSD/i', $agent )) {
        $os = 'NetBSD';
    } else if (preg_match ( '/BSD/i', $agent )) {
        $os = 'BSD';
    } else if (preg_match ( '/OSF1/i', $agent )) {
        $os = 'OSF1';
    } else if (preg_match ( '/IRIX/i', $agent )) {
        $os = 'IRIX';
    } else if (preg_match ( '/FreeBSD/i', $agent )) {
        $os = 'FreeBSD';
    } else if (preg_match ( '/teleport/i', $agent )) {
        $os = 'teleport';
    } else if (preg_match ( '/flashget/i', $agent )) {
        $os = 'flashget';
    } else if (preg_match ( '/webzip/i', $agent )) {
        $os = 'webzip';
    } else if (preg_match ( '/offline/i', $agent )) {
        $os = 'offline';
    } else {
        $os = 'unknown';
    }
    return $os;
}

//クライアントのログデータ記録
function clientlog($student_id, $group_id,$cqchat_id,$course_id,$group_member,$page, $begin, $end) {
    $useragent = $_SERVER ['HTTP_USER_AGENT'];
    $clientip = $_SERVER ['REMOTE_ADDR'];

    $os = get_os ( $useragent );

    $time = date ( 'Y-m-d H:i:s' );

    $data = array();
    array_push($data, $student_id);
    array_push($data, $group_id);
    array_push($data, $cqchat_id);
    array_push($data, $course_id);
    array_push($data, $group_member);
    array_push($data, $useragent);
    array_push($data, $clientip);
    array_push($data, $os);
    array_push($data, $time);
    array_push($data, strtotime($time));
    array_push($data, $page);
    array_push($data, date ( 'Y-m-d H:i:s' ,$begin));
    array_push($data, date ( 'Y-m-d H:i:s' ,$end));
    array_push($data, $begin);
    array_push($data, $end);

    $filename = "log/".$student_id.".csv";
    if (! file_exists ( $filename )) {
        $f = fopen ( $filename, "w+" );
        fputcsv($f, ["user_id","group_id","cqchat_id","course_id","group_member","useragent", "clientip", "os", "time", "page", "course_begin", "course_end","course_begin_timestamp","course_begin_timestamp"]);
        fputcsv($f, $data);
        fclose($f);
    }else{
        $f = fopen ( $filename, "a" );
        fputcsv($f, $data);
        fclose($f);
    }


}

//開始時間と終了時間の獲得
function get_begin_end($b, $e){
    if($b){
        $begin = $b;
        $begin = strtotime(date("$begin 00:00:00"));
    }else{
        $begin = time();
    }
    if($e){
        $end = $e;
        $end = strtotime(date("$end 00:00:00"));
    }else{
        $end = time();
    }

    return [$begin, $end];
}




