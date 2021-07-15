<!--
BookRollのテーブルにコラムであるoperationを集計し、
タイムスタンプで期間を選択し、
条件に当てはまる情報を
D3ライブラリで可視化するaaaaaaa
-->
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BookRoll学習活動</title>

    <script src="js/http_d3js.org_d3.v4.js"></script>
    <script src="js/jquery_2.1.1_jquery.js"></script>
    <script src="js/bootstrap_3.3.6_js_bootstrap.js"></script>
    <link href="css/bootstrap_3.3.6_css_bootstrap.css" rel="stylesheet"/>
    <link rel="stylesheet" href="jquery-ui.css" >
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
        <li role="presentation">
            <a href="index.php">ログイン</a>
        </li>
        <li role="presentation" class="dropdown active">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="true">授業前の個人学習<span class="caret"></span></a>

            <ul class="dropdown-menu">
                <li ><a href="bookroll.php">BookRoll学習活動</a></li>
                <li role="separator" class="divider"></li>
                <li><a href="heatmap.php">教材の閲覧時間</a></li>
            </ul>
        </li>
        <li role="presentation">
            <a href="discussion.php">授業中の発言分類結果</a>
        </li>

        <li role="presentation" style="margin-left: 150px; margin-top: 10px">
            <img src='images/user_s_icon.png' alt= 'user_icon'>
        </li>
        <li id="st-status" role="presentation" style="margin-left: 20px; margin-top: 10px"></li>
    </ul>

</div>

<h3 style="margin-left: 30px; margin-top: 10px">グループメンバーの事前学習活動と比較し、<br>次のディスカッションを進ませるようにBookRollの機能を使いましょう！</h3>

<form style="margin-left: 30px">
    <label for="course_pick">教材選択：　</label>
<!--    <input type="text" id="course_pick" style="color: red" autocomplete="off"><br>-->
    <select  name = "name" id="course_pick"  autocomplete="off"></select><br>

    <label for="datepicker_begin">期間選択：　</label>
    <input type="text" id="datepicker_begin"  autocomplete="off">
    <label for="datepicker_end">~</label>
    <input type="text" id="datepicker_end"  autocomplete="off">
    <button type="button" class="btn btn-light" onclick="select_time()">検索</button>
</form>

<h5 style="margin-left: 30px; margin-top: 10px"><strong>*最初の日付は該当授業の前の一週間</strong></h5>
<h5 style="margin-left: 30px; margin-top: 10px">もし他の時間期間に対応する情報を見たい場合は、カレンダーから選択してください</h5>

<img style="margin-left: 30px" src='images/notification.png' alt='notification'>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h3 id = "course_id" style="margin-left: 30px; margin-top: 10px"></h3>
            <h3 id = "time_zone" style="margin-left: 30px; margin-top: 10px"></h3>
        </div>
    </div>
</div>

<div id="group">
    <svg id = "sunburst" style="width: 550px; height: 550px; margin-left: 20px; margin-top: 20px"></svg>
    <svg id = "label" style="width: 550px; height: 550px; margin-left: 20px; margin-top: 20px"></svg>
</div>
</body>

<script src="js/jquery-ui.js"></script>
<script type="text/javascript">
    function load_page() {
        //ユーザーID、カレンダーで選択した始まり時間と終わり時間をPOSTで送信
        var st = sessionStorage.getItem("name_st");

        //select course
        var course = "";
        if (sessionStorage.getItem("course_pick")){
            course = sessionStorage.getItem("course_pick");
        }else{
            course = sessionStorage.getItem("cqchat_courseid");
        }

        var begin = sessionStorage.getItem("datepicker_begin");
        var end = sessionStorage.getItem("datepicker_end");

        //readかsearchという引数
        if(!sessionStorage.getItem("function")){
            sessionStorage.setItem("function","read");
        }

        $("#st-status").html(st);//クリックされた受講者の実名
        $("#course_id").html("<img src='images/course_icon.png' alt= 'course_icon'>"　+ '  ' +　course);//スライド名

        //BeginとEndが設定されていない場合は、そのまま初期の期間を使用
        if(begin && end){
            $("#time_zone").html("<img src='images/time_icon.png' alt= 'course_icon'>"　+ '  ' +　begin + '～'　+ end);
        }else {
            begin = sessionStorage.getItem("course_begin");
            end = sessionStorage.getItem("course_end");

            $("#time_zone").html("<img src='images/time_icon.png' alt= 'course_icon'>"　+ '  ' +　begin + '～'　+ end);
        }

        //選択された期間に関するBeginとEnd,Function（searchかread）,スライドの名を送信
        $.ajax({
            method:'POST',
            url:'behavior_json.php',
            data:{
                begin: begin,
                end: end,
                function: sessionStorage.getItem("function"),
                course_pick:sessionStorage.getItem("course_pick")?sessionStorage.getItem("course_pick"):sessionStorage.getItem("cqchat_courseid")
            },
            dataType:'text',
            //FIXME：ブックエンドにログイン状態以外の情報があれば、ログイン不可能
            success: function (StuStatus) {
                if(StuStatus){
                    draw();

                }else if (StuStatus==="false"){
                    alert("未登録");
                    $("#password").val('')
                }
            }
        })


    }

    //ページを閉じる際のログ記録
    window.onbeforeunload = function exit_page () {
        $.ajax({
            method:'POST',
            url:'exit_log.php',
            data:{
                course_pick:sessionStorage.getItem("course_pick")?sessionStorage.getItem("course_pick"):sessionStorage.getItem("cqchat_courseid"),
                function: sessionStorage.getItem("function"),
                begin:sessionStorage.getItem("datepicker_begin")?sessionStorage.getItem("datepicker_begin"):sessionStorage.getItem("course_begin"),
                end:sessionStorage.getItem("datepicker_end")?sessionStorage.getItem("datepicker_end"):sessionStorage.getItem("course_end")
            },
            dataType:'text',
            success: function (StuStatus) {
                sessionStorage.setItem("function","read");
            }
        })
    };


    function select_time(){
        //カレンダーの日付を選択し、ローカルストレージ
        var course_pick = $("#course_pick").val();
        var begin = $("#datepicker_begin").val();
        var end = $("#datepicker_end").val();
        sessionStorage.setItem("datepicker_begin",begin);
        sessionStorage.setItem("datepicker_end",end);
        sessionStorage.setItem("course_pick",course_pick);
        //function log
        sessionStorage.setItem("function","search");

        load_page();
        location.reload();
    }

    //コースにあるすべてのスライドの選択リストを作成
    function getTxt(URL) {
        return new Promise(function (resolve) {
            req = new XMLHttpRequest();
            req.open("get", URL,true);

            req.onload = function () {
                resolve(req.responseText)
            };
            req.onerror = function () {
                console.log("error");
            };

            req.send(null);
        })

    }

    var select = document.getElementById("course_pick");

    getTxt('data/'+sessionStorage.getItem("context_label_full_name")+'.txt').then(function (result) {

        course_l = result.split(",")
        console.log(course_l);

        for (i = 0; i<course_l.length-1;i++){
            var option = document.createElement("option");
            option.text = course_l[i];
            option.value = course_l[i];
            select.appendChild(option);
        }
    });



    $( "#datepicker_begin" ).datepicker();
    $( "#datepicker_end" ).datepicker();

    //コース選択、カレンダーの属性の設定
    $("#course_pick").attr("value",sessionStorage.getItem("course_pick")?sessionStorage.getItem("course_pick"):sessionStorage.getItem("cqchat_courseid"));
    $("#datepicker_begin").attr("value",sessionStorage.getItem("datepicker_begin")?sessionStorage.getItem("datepicker_begin"):sessionStorage.getItem("course_begin"));
    $("#datepicker_end").attr("value",sessionStorage.getItem("datepicker_end")?sessionStorage.getItem("datepicker_end"):sessionStorage.getItem("course_end"));




    //D3で可視化する関数
    function draw(){
        var st = sessionStorage.getItem("logined_lms_userid");

        var Width = $('#sunburst').width(),
            Height = $('#sunburst').height(),
            Radius = Math.min(Width, Height) / 2,
            Color = d3.scaleOrdinal(d3.schemeCategory10);


        var g = d3.select('svg')
            .append('g')
            .attr('transform', 'translate('+ Width / 2 + ',' + Height / 2 + ')');

        var Layout = d3.partition()
            .size([2 * Math.PI, Radius]);

        var Arc = d3.arc()
            .startAngle(function (d) {return d.x0})
            .endAngle(function(d){return d.x1})
            .innerRadius(function(d){return d.y0})
            .outerRadius(function(d){return d.y1});

        //ブックエンドで集計済みのJson形式のファイルを読み込み
        d3.json('data/'+st+'_behavior.json', function (error, Data) {
            if (error) throw error;
            drawSunburst(Data);
        });

        function drawSunburst(data){

            var Root = d3.hierarchy(data)
                .sum(function (d) {
                    return d.size
                });

            var Nodes = Root.descendants();

            Layout(Root);

            var Slices = g.selectAll('g')
                .data(Nodes)
                .enter()
                .append('g');

            var tooltip = d3.select("#group")
                .append("div")
                .style("opacity", 0)
                .attr("class", "tooltip")
                .style("border", "solid")
                .style("border-width", "2px")
                .style("border-radius", "5px")
                .style("padding", "5px")
                .style("width", "100px")
                .style("background-color","white")
                .style("text-align", "center");

            // ツールキット（マウスホバー）を作成
            var mouseover = function(d) {
                tooltip.style("opacity", 1)
            };
            var mousemove = function(d) {
                tooltip
                    .html(d.data.id+ ":" + d.value)
                    .style("left", (d3.mouse(this)[0]) +300+ "px")
                    .style("top", (d3.mouse(this)[1]) +600 +"px")

            };
            var mouseleave = function(d) {
                tooltip.style("opacity", 0)
            };

            Slices.append('path')
                .attr('display', function (d) {
                    return d.depth? null: 'none';
                })
                .attr('d', Arc)
                .style('stroke', '#fff')
                .style('fill',function (d) {
                    return Color((d.children? d : d.parent).data.id)
                })
                .on('mouseover', mouseover)
                .on('mousemove', mousemove)
                .on('mouseleave', mouseleave);

            if (Root.value){
                Slices.append('text')
                    .attr('transform', function(d) {
                        if(d.parent){return 'translate(' + Arc.centroid(d) + ')rotate(' + computeTextRotation(d) + ')';}
                        else{return 'translate('+ 0+ ',' + 0 + ')'}
                    })
                    .attr('dx', '-20')
                    .attr('dy', '.5em')
                    .text(function(d) { if (d.value){return d.data.id }});
            }else{
                alert("データなし、頑張ってください");
            }

            //ラベルの描画
            var rect_size = {top: 30, right: 30, bottom: 30, left: 30};
            var label = d3.select('#label')
                .append('g')
                .attr('transform', 'translate('+ Width / 20 + ',' + Height / 20 + ')');

            var parents = new Array();
            for (i = 1; i<Nodes.length;i++){
                if(Nodes[i].depth == 1){
                    parents[i-1] = Nodes[i]
                }
            }

            var rect = label.selectAll('g')
                .data(parents)
                .enter();

            rect.append('rect')
                .attr('transform', function (d, i) {
                    return 'translate('+ Width / 20 + ',' + (Height / 20 + (i-1)*50) +')';
                })
                .attr('x',rect_size.left)
                .attr('width',rect_size.right)
                .attr('y',rect_size.top)
                .attr('height',rect_size.bottom)
                .style('fill',function (d) {
                    return Color(d.data.id)
                });

            rect.append('text')
                .attr('dx', '-20')
                .attr('dy', '0')
                .attr('transform', function (d, i) {
                    return 'translate('+ (Width / 20 + 100) + ',' + (Height / 20 + i *50) +')';
                })
                .text(function(d) { return d.data.id });


        }

        function computeTextRotation(d) {

            var angle = (d.x0 + d.x1) / Math.PI * 90;

            // Avoid upside-down labels; labels as rims
            return (angle < 100 || angle > 270) ? angle : angle + 180;
            //return (angle < 180) ? angle - 90 : angle + 90;  // labels as spokes
        }


    }

</script>