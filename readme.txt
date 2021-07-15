root
 -css
    -bootstrap_3.3.6_css_bootstrap.css "bootstrap"というフロントエンドのフレームワーク
 -data
    -「コース名」.txt 　"BookRoll"上のコース名に対するすべてのスライドの名前リスト
    -[logined_lms_userid]_behavior.json　[clicked_lms_userid]が所属するグループにおける"BookRoll"上の認知的学習活動数に関するデータの保存
    -[logined_lms_userid]_heatmap.json　[clicked_lms_userid]が所属するグループにおける"BookRoll"上の認知的学習活動時間に関するデータの保存
    -[logined_lms_userid]_discussion.json　[clicked_lms_userid]が所属するグループにおける参加度に関するデータの保存
 -images
    -chat_icon.png　"discussion.php"の議論テーマを意味するアイコン
    -course_icon.png　"index.php", "behavior.php",　"heatmap.php"の検索されたスライド名を意味するアイコン
    -group_icon.png　"index.php"のグループメンバーの名前リストを意味するアイコン
    -user_icon.png クリックされたユーザーを意味するアイコン
    -time_icon.png　選択された期間を意味するアイコン
    -ui-icons_[...].png　カレンダーの提示に関するアイコン
    -ui-bg_glass_[...].png カレンダーの提示に関する線
    -notification.png　"behavior.php",　"heatmap.php"にある注意事項に関する画像
    -system_intro.png　"index.php"にあるシステムの使用仕方に関する画像
 -js
    -bootstrap_3.3.6_js_bootstrap.js "bootstrap"に関するJavaScriptの関数
    -http_d3js.org_d3.v4.js　D3という描画ライブラリー
    -jquery_2.1.1_jquery.js　jquery
    -jquery-ui.js
 -log
    -[logined_lms_userid].csv　3つのページ（"behavior", "heatmap", "discussion")にアクセスしたログまたページを閉じる（exit）行動のログデータ
 -setting_csv
    -「スライド名」.csv　スライドの各ページの内容に関するURL
    -course_name.csv　スライド名と議論のテーマの紐づけ
    -course_time.csv　スライドに関する授業の期間

フロントエンド
 -send.html *START Cqchatシステムから仮のデータをポストする　
 -index.php　*SECOND　send.htmlでポストされたデータを受取、データを_SESSIONかつsessionStorageに貯蔵、3つのページ（behavior.php, heatmap.php, discussion.php）にアクセス
 -behavior.php　"BookRoll"上の認知的学習活動数（ブックマーク、マーカー、メモ）を"Sunburst"で可視化
 -heatmap.php　"BookRoll"上の認知的学習活動時間（各スライドの閲覧時間）を"Heatmap"で可視化、サムネイル画像の提供（具体的な閲覧時間、スライドの内容）
 -discussion.php　"Cqchat"のデータベースからポストされた参加度（社会的存在感に関する発言）を"Sunburst"で可視化

バックエンド
 -behavior_json.php　"behavior.php"のバックグラウンド処理、"BookRoll"データベースにアクセス、データを集計し、ディレクトリのdata/[logined_lms_userid]_behavior.jsonに貯蔵；　"behavior"という引数を"clientlog"という関数で引き出し
 -heatmap_json.php　"heatmap.php"のバックグラウンド処理、"BookRoll"データベースにアクセス、データを集計し、ディレクトリのdata/[logined_lms_userid]_heatmap.jsonに貯蔵；　"heatmap"という引数を"clientlog"という関数で引き出し
 -discussion_json.php　"discussion.php"のバックグラウンド処理、データを集計し、ディレクトリのdata/[logined_lms_userid]_discussion.jsonに貯蔵；　"discussion"という引数を"clientlog"という関数で引き出し
 -comm_function.php　タイムスタンプの変換；　3つのページ（"behavior", "heatmap", "discussion")にアクセスしたログまたページを閉じる（exit）を、ディレクトリのlog/[logined_lms_userid].csvに保存
 -exit_log.php　"exit"という引数を"clientlog"という関数で引き出し
 -emm.php　"BookRoll"データベースへのアクセスに対するテスト