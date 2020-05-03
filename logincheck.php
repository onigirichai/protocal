<?php
//ログインチェックの機能
//仮のユーザーとパスワードを検証
//POSTでCQCHATから送信したコースIDとグループIDも受取可能

//ユーザーIDとパスワード
$student = $_POST['student'];
$password = $_POST['password'];
//TODO：テスト用のローカルのデータベース、修正可
//TODO：複数のPHPファイルがこの変数を使用するには、クラスを立てる可
$dsn = 'mysql:dbname=bagujo;host=127.0.0.1;port=3306';
$dbuser = 'onigiri';
$dbpassword = 'angelfantuan';
try{
    $dbh = new PDO($dsn, $dbuser, $dbpassword);
}catch (PDOException $e){
    echo "接続失敗: " . $e->getMessage() . "\n";
}
//TODO：ローカルで立てたデータベースのテーブル（users）に対応するSQLコマンド
$result = $dbh->query("select * from users");
$loginstatus = "false";
foreach ($result as $row){
    if ($row['StudentID'] == $student){
        if($row['Password'] == $password){
            $loginstatus = "success";
        }else{
            $loginstatus = "パスワード間違い";
        }
    }
}

echo $loginstatus;