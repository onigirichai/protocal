<!--
仮のmdl_cqchat_social_presence_pointのテーブルにコラムであるpointを集計し、
タイムスタンプで期間を選択し、
条件に当てはまる情報を
D3ライブラリで可視化する
-->
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>参加度</title>

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
        <li role="presentation" class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="true">授業前の個人学習<span class="caret"></span></a>

            <ul class="dropdown-menu">
                <li ><a href="behavior.php">BookRoll学習活動</a></li>
                <li role="separator" class="divider"></li>
                <li><a href="heatmap.php">教材の閲覧時間</a></li>
            </ul>
        </li>
        <li role="presentation" class="active">
            <a href="#">授業中の発言分類結果</a>
        </li>

        <li role="presentation" style="margin-left: 150px; margin-top: 10px">
            <img src='images/user_s_icon.png' alt= 'user_icon'>
        </li>
        <li id="st-status" role="presentation" style="margin-left: 20px; margin-top: 10px"></li>
    </ul>

</div>

<h3 style="margin-left: 30px; margin-top: 10px"><strong>グループメンバーの参加度と比較しましょう！</strong></h3>
<!--<form style="margin-left: 30px">-->
<!--    <label for="datepicker_begin">始まり</label>-->
<!--    <input type="text" id="datepicker_begin" style="color: red" autocomplete="off">-->
<!--    <label for="datepicker_end">終わり</label>-->
<!--    <input type="text" id="datepicker_end" style="color: red" autocomplete="off">-->
<!--    <button type="button" class="btn btn-light" onclick="select_time()">検索</button>-->
<!--</form>-->

<h5 style="margin-left: 30px; margin-top: 10px"><strong>*参加度</strong>とはディスカッションによる発言分類の結果となります（詳細は扇形にカーソルを当てることにて）。</h5>

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
    //ユーザーID、カレンダーで選択した始まり時間と終わり時間をPOSTで送信
    function load_page() {
        var st = sessionStorage.getItem("name_st");
        // var course = sessionStorage.getItem("cqchat_courseid");
        var begin = sessionStorage.getItem("course_begin");
        var end = sessionStorage.getItem("course_end");
        var cqchat_name = sessionStorage.getItem("cqchat_name");

        <?php session_start(); ?>
        sessionStorage.setItem("dis_begin", "<?php echo $_SESSION["dis_begin"]; ?>");
        sessionStorage.setItem("dis_end", "<?php echo $_SESSION["dis_end"]; ?>");

        var dis_begin = sessionStorage.getItem("dis_begin");
        var dis_end = sessionStorage.getItem("dis_end");


        $("#st-status").html(st);
        $("#course_id").html("<img src='images/chat_icon.png' alt= 'chat_icon'>"　+ '  ' +　cqchat_name);
        if(dis_begin && dis_end){
            $("#time_zone").html("<img src='images/time_icon.png' alt= 'course_icon'>"　+ '  ' +　dis_begin + '～'　+ dis_end);
        }

        $.ajax({
            method:'POST',
            url:'discussion_json.php',
            data:{
                begin: begin,
                end: end
            },
            dataType:'text',

            //FIXME：ブックエンドにログイン状態以外の情報があれば、ログイン不可能
            success: function (StuStatus) {
                if(StuStatus){
                    console.log(StuStatus);
                    //ログイン検証、可視化を実行
                    // if (begin && end){
                    //     draw();
                    // }else{
                    //     alert("期間を選択してください")
                    // }
                    draw();


                }else if (StuStatus==="false"){
                    alert("未登録");
                    $("#password").val('')
                }
            },
            error : function (XMLHttpRequest, textStatus, errorThrown) {
                console.log(XMLHttpRequest); // XMLHttpRequestオブジェクト
                console.log(textStatus); // status は、リクエスト結果を表す文字列
                console.log(errorThrown); // errorThrown は、例外オブジェクト
            }
        })


    }

    //ページを閉じる際のログ記録
    window.onbeforeunload = function () {
        $.ajax({
            method:'POST',
            url:'exit_log.php',
            data:{
                begin:sessionStorage.getItem("course_begin"),
                end:sessionStorage.getItem("course_end")
            },
            dataType:'text',
            success: function (StuStatus) {}
        })
    };

    function select_time(){
        //カレンダーの日付を選択し、ローカルストレージ
        var begin = $("#datepicker_begin").val();
        var end = $("#datepicker_end").val();
        sessionStorage.setItem("datepicker_begin",begin);
        sessionStorage.setItem("datepicker_end",end);
        load_page();
        location.reload();
    }

    // $( "#datepicker_begin" ).datepicker();
    // $( "#datepicker_end" ).datepicker();
    //
    // $("#datepicker_begin").attr("placeholder",sessionStorage.getItem("course_begin"));
    // $("#datepicker_end").attr("placeholder",sessionStorage.getItem("course_end"));

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
        d3.json('data/'+st+'_discussion.json', function (error, Data) {
            if (error) throw error;
            drawSunburst(Data);
        });



        function drawSunburst(data){
            const sp = ["感情","ユーモア","自己開示","パラ言語","価値","スレッド", "参照", "引用", "質問", "感謝", "同意", "不同意", "提案", "呼格", "集団言葉", "挨拶", "情報共有", "省察"];
            const sp_com = [
                "感情（喜怒哀楽）を表現する",
                "ユーモアを表現する",
                "自己開示（好き嫌い）をする",
                "周辺言語（絵文字など）を使う",
                "自分の価値観に関する発言をする",
                "話題を続ける",
                "他者の発言に参照する",
                "他者の発言を引用する",
                "他者に質問する",
                "他者に感謝する",
                "他者の発言に賛成する",
                "他者の発言に不賛成する",
                "他者に提案する",
                "呼格（他者の名前）を使う",
                "集団言葉（我々、私たちなど）を使う",
                "挨拶する",
                "他者と情報を共有する",
                "課題に省察する"];

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
                .style("width", "200px")
                .style("background-color","white")
                .style("text-align","center");

            var mouseover = function(d) {
                tooltip.style("opacity", 1)
            };

            var mousemove = function(d) {
                tooltip
                    .html(d.depth == 2? sp_com[sp.indexOf(d.data.id)]+ ":" + d.value:d.data.id+ ":" + d.value)
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


            //draw a label
            var rect_size = {top: 30, right: 30, bottom: 30, left: 30};
            var label = d3.select('#label')
                .append('g')
                .attr('transform', 'translate('+ Width / 20 + ',' + Height / 20 + ')');

            var parents = [];
            for (i = 1; i<Nodes.length;i++){
                if(Nodes[i].depth == 1){
                    parents[i-1] = Nodes[i]
                }
            }

            var rect = label.selectAll('g')
                .data(parents)
                .enter();

            console.log(Nodes);
            console.log(parents);

            rect.append('rect')
                .attr('transform', function (d, i) {
                    return 'translate('+ Width / 20 + ',' + (Height / 20 + (i-1)*50) +')';
                })
                .attr('x',rect_size.left)
                .attr('width',rect_size.right)
                .attr('y',rect_size.top)
                .attr('height',rect_size.bottom)
                .style('fill',function (d) {
                    return Color(d.data.id);
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
