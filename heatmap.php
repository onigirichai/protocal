<!--
BookRollのテーブルにコラムであるoperation_dateで変化した時間を集計し、
タイムスタンプで期間を選択し、
条件に当てはまる情報を
D3ライブラリで可視化する
-->
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>閲覧時間</title>

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
                <li ><a href="behavior.php">BookRoll学習活動</a></li>
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

<h3 style="margin-left: 30px; margin-top: 10px">グループメンバーのスライドの閲覧時間と比較し、<br>次のディスカッションを進ませるようにスライドを読みましょう！</h3>

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


<div id="heatmap" style="margin-left: 30px"></div>


</body>


<script src="js/jquery-ui.js"></script>
<script type="text/javascript">
    //ユーザーID、カレンダーで選択した始まり時間と終わり時間をPOSTで送信
    function load_page() {
        var st = sessionStorage.getItem("name_st");

        //select course
        var course = "";
        if (sessionStorage.getItem("course_pick")){
            course = sessionStorage.getItem("course_pick");
        }else{
            course = sessionStorage.getItem("cqchat_courseid");
        }
        sessionStorage.setItem("course",course);

        var begin = sessionStorage.getItem("datepicker_begin");
        var end = sessionStorage.getItem("datepicker_end");

        $("#st-status").html(st);
        $("#course_id").html("<img src='images/course_icon.png' alt= 'course_icon'>"　+ '  ' +　course);
        if(begin && end){
            $("#time_zone").html("<img src='images/time_icon.png' alt= 'course_icon'>"　+ '  ' +　begin + '～'　+ end);
        }else {
            begin = sessionStorage.getItem("course_begin");
            end = sessionStorage.getItem("course_end");

            $("#time_zone").html("<img src='images/time_icon.png' alt= 'course_icon'>"　+ '  ' +　begin + '～'　+ end);
        }

        $.ajax({
            method:'POST',
            url:'heatmap_json.php',
            data:{
                begin: begin,
                end: end,
                course_pick:sessionStorage.getItem("course_pick")?sessionStorage.getItem("course_pick"):sessionStorage.getItem("cqchat_courseid")
            },
            dataType:'text',
            success: function (StuStatus) {
                if(StuStatus){
                    //FIXME：ブックエンドにログイン状態以外の情報があれば、ログイン不可能
                    //ログイン検証、データを読み込み

                    load_data();


                }else if (StuStatus==="false"){
                    alert("未登録");
                    $("#password").val('')
                }
            }
        })


    }

    //ページを閉じる際のログ記録
    window.onbeforeunload = function () {
        $.ajax({
            method:'POST',
            url:'exit_log.php',
            data:{
                begin:sessionStorage.getItem("datepicker_begin")?sessionStorage.getItem("datepicker_begin"):sessionStorage.getItem("course_begin"),
                end:sessionStorage.getItem("datepicker_end")?sessionStorage.getItem("datepicker_end"):sessionStorage.getItem("course_end")
            },
            dataType:'text',
            success: function (StuStatus) {}
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

        load_page();
        location.reload();
    }

    //get array of course list and create selected list
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

        for (i = 0; i<course_l.length-2;i++){
            var option = document.createElement("option");
            option.text = course_l[i];
            option.value = course_l[i];
            select.appendChild(option);
        }
    });

    $( "#datepicker_begin" ).datepicker();
    $( "#datepicker_end" ).datepicker();

    $("#course_pick").attr("value",sessionStorage.getItem("course_pick")?sessionStorage.getItem("course_pick"):sessionStorage.getItem("cqchat_courseid"));
    $("#datepicker_begin").attr("value",sessionStorage.getItem("datepicker_begin")?sessionStorage.getItem("datepicker_begin"):sessionStorage.getItem("course_begin"));
    $("#datepicker_end").attr("value",sessionStorage.getItem("datepicker_end")?sessionStorage.getItem("datepicker_end"):sessionStorage.getItem("course_end"));

    function load_data() {
        var st = sessionStorage.getItem("logined_lms_userid");
        //ブックエンドで集計済みのJson形式のファイルを読み込み
        d3.json('data/'+st+'_heatmap.json', function (error, Data) {
            if (error) throw error;
            console.log(Data);
            draw_heatmap(Data);

        });
    }

    //教材の閲覧時間での可視化
    function draw_heatmap(Data){

        // set the dimensions and margins of the graph
        var margin = {top: 30, right: 30, bottom: 30, left: 30},
            width = 550 - margin.left - margin.right,
            height = 550 - margin.top - margin.bottom;

        // append the svg object
        var svg = d3.select("#heatmap")
            .append("svg")
            .attr("width", width + margin.left + margin.right)
            .attr("height", height + margin.top + margin.bottom)
            .append("g")
            .attr("transform",
                "translate(" + margin.left + "," + margin.top + ")");

        // Labels of row and columns
        var student_id = [];
        var pages = [];
        var page_length = [];

        for (var i=0; i<Data.children.length; i++){
            student_id.push(Data.children[i].id);
            var page_tmp = Data.children[i].children;
            page_length.push(Object.keys(page_tmp).length);
        }

        var max_len = Math.max(...page_length);

        for (var j=0; j<max_len; j++){
            pages.push(j+1);
        }

        // Build X scales and axis:
        var x = d3.scaleBand()
            .range([ 0, width ])
            .domain(student_id)
            .padding(0.01);


        svg.append("g")
            .attr("transform", "translate(0," + height + ")")
            .call(d3.axisBottom(x))
            .append("text")
            .text("学生 名前")
            .attr("stroke","#000")
            .attr("x",width)
            .attr("y",20);

        // Build y scales and axis:
        var y = d3.scaleBand()
            .range([ height, 0 ])
            .domain(pages)
            .padding(0.01);
        svg.append("g")
            .call(d3.axisLeft(y))
            .append("text")
            .text("ページ番号")
            .attr("stroke","#000")
            .attr("x",20)
            .attr("y",-10);

        d3.selectAll(".tick")
            .select("text")
            .style("font-size","16px");

        // Build color scale
        var myColor = d3.scaleLinear()
            .range(["#faffff", "#00aaff"])
            .domain([1,100]);


        //Read the data
        // d3.json("data/133_behavior.json", function(data) {

        // var student = [];
        // var page = [];
        // var value = [];
        var time_array = [];

        var time_label = [];



        for (var row = 0; row < student_id.length; row++){
            for (var col = 0; col < pages.length; col++){
                time_array[row*pages.length+col] = {};
                var values_obj = Data.children[row].children;

                // student.push(Data.children[row].id);
                // page.push(col+1);
                // value.push(Object.values(values_obj)[col].size);

                time_array[row*pages.length+col]["id"] = Data.children[row].id;
                time_array[row*pages.length+col]["page"] = col+1;
                time_array[row*pages.length+col]["time"] = Object.values(values_obj)[col].size;

                time_label[row*pages.length+col] = Object.values(values_obj)[col].size;
            }
        }


        function compareFunc(a, b){
            return a-b;
        }
        time_label.sort(compareFunc);


        function showtime(val) {
            if(val<60){
                return val + "秒";
            }else {
            var min_total = Math.floor(val/60);
            var sec = Math.floor((val%60));
            }
            if(min_total<60){
                return min_total + "分" + sec + "秒";
            }else {
                var hour_total = Math.floor(min_total/60);
                var min = Math.floor(min_total % 60);
            }
            if (hour_total<24){
                return hour_total + "時間" + min + "分" + sec + "秒";
            }else{
                var date_total = Math.floor(hour_total/24);
                var hour = Math.floor(hour_total % 24);
                return  date_total + "日" + hour + "時間" + min + "分" + sec + "秒";
            }

        }

        var time_label_uni = [];

        $.each(time_label, function(i, el){
            if($.inArray(el, time_label_uni) === -1) time_label_uni.push(el);
        });

        var rect_size = {top: 0, right: 100, bottom: 500/time_label_uni.length, left: 60};

        var max_l = time_label_uni.length;
        var per_l = parseInt(max_l*0.90);

        var label = d3.select("#heatmap")
            .append("svg")
            .attr("id", "label")
            .attr("width", width + margin.left + margin.right)
            .attr("height", height + margin.top + margin.bottom + 30)
            .append("g")
            .attr("transform",
                "translate(" + (margin.left) + ",20)");

        d3.select('#label')
            .append('text')
            .attr("font-family","Verdana")
            .attr("stroke","#000")
            .attr('transform', 'translate(50,15)')
            .text("最大閲覧時間");

        d3.select('#label')
            .append('text')
            .attr("font-family","Verdana")
            .attr("stroke","#000")
            .attr('transform', 'translate(50,30)')
            .text(function() {return showtime(time_label_uni[max_l-1])});

        d3.select('#label')
            .append('text')
            .attr("font-family","Verdana")
            .attr("stroke","#000")
            .attr('transform', 'translate(50,550)')
            .text(function() {return showtime(time_label_uni[0])});


        label.selectAll('g')
            .data(time_label_uni)
            .enter()
            .append('rect')
            .attr('transform', function (d, i) {
                return 'translate('+ 0 + ',' + (time_label_uni.length-i)*500/time_label_uni.length +')';
            })
            .attr('x',rect_size.left)
            .attr('width',rect_size.right)
            .attr('y',rect_size.top)
            .attr('height',rect_size.bottom)
            .style('fill',function (d) {
                return myColor(d)
            });

        // label.selectAll('g')
        //     .

        function getCSV(URL) {
            return new Promise(function (resolve) {
                req = new XMLHttpRequest();
                req.open("get", URL,true);

                req.onload = function () {
                    lti_dict = convertCSVtoDict(req.responseText);
                    resolve(lti_dict)
                };
                req.onerror = function () {
                    console.log("error");
                };

                req.send(null);
            })

        }

        function convertCSVtoDict(str) {
            result = {};
            console.log(str);
            tmp = str.split("\n");
            for(i=0;i<tmp.length;++i){
                tmp_list = tmp[i].split(',');
                result[tmp_list[0]] = tmp_list[1]
            }

            return result;

        }

        getCSV("setting_csv/"+sessionStorage.getItem("course")+"_"+sessionStorage.getItem("context_label")+".csv").then(function (img_url) {
            // ツールキットを作成
            var tooltip = d3.select("#heatmap")
                .append("div")
                .style("opacity", 0)
                .attr("class", "tooltip")
                .style("background-color", "white")
                .style("border", "solid")
                .style("border-width", "2px")
                .style("border-radius", "5px")
                .style("padding", "5px")
                .style("width", "400px");

            // Three function that change the tooltip when user hover / move / leave a cell
            var mouseover = function (d) {
                tooltip.style("opacity", 1)
            };
            var mousemove = function (d) {
                tooltip
                    .html(function () {
                        if(d.time>=time_label_uni[per_l] && d.time>=600){
                            return "<h4>" + d.page + "ページ目を閲覧した時間は: " + showtime(d.time) + "<br><strong></h4>"+
                                "<h5 style='color: red'>このページでブラウザを閉じた可能性があります。<br>その場合は実際の閲覧時間と違う可能性があります。</h5>"+
                                "<img  src = \"" + img_url[d.page] + "\" width=\"384\" height=\"272\">"
                        }
                        return "<h4>" + d.page + "ページ目を閲覧した時間は: " + showtime(d.time) + "<br><strong></h4>"+
                            "<img  src = \"" + img_url[d.page] + "\" width=\"384\" height=\"272\">";
                    })
                    .style("left", (d3.mouse(this)[0] + 70) + "px")
                    .style("top", (d3.mouse(this)[1]) + "px")
            };
            var mouseleave = function (d) {
                tooltip.style("opacity", 0)
            };

            var max_time = time_label_uni[per_l];

            // add the squares
            svg.selectAll()
                .data(time_array, function (d) {
                    return d.id + ':' + d.page;
                })
                .enter()
                .append("rect")
                .attr("x", function (d) {
                    return x(d.id)
                })
                .attr("y", function (d) {
                    return y(d.page)
                })
                .attr("width", x.bandwidth())
                .attr("height", y.bandwidth())
                .style("fill", function (d) {
                    if (d.time<time_label_uni[per_l]){
                        return myColor(d.time/max_time*100)
                    }
                    else return "#00aaff"
                })
                .on("mouseover", mouseover)
                .on("mousemove", mousemove)
                .on("mouseleave", mouseleave)
        });
        //     svg.selectAll()
        //         .data(time_array, function (d) {
        //             return d.id + ':' + d.page;
        //         })
        //         .enter()
        //         .append("text")
        //         .attr("x", function (d) {
        //             return x(d.id)
        //         })
        //         .attr("y", function (d) {
        //             return y(d.page)
        //         })
        //         .attr("dx", x.bandwidth()/3)
        //         .attr("dy", y.bandwidth()/1.15)
        //         .text(function (d) {
        //             if (d.time>=time_label_uni[per_l]){return "外れ値";}})
        // });


    }




</script>



