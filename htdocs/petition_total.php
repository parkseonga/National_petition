<html>
   <head>
	<title>청원 동의 수 현황(유사도별)</title>

	<?php

		$db = mysqli_connect("localhost","root",password,"national_petition");
		if($db)
			echo "<br/>";
		else
			echo "connect failed<br>";
		$query = "SELECT * FROM national_petition.petition_similarity WHERE id = '" .$_GET['id']."' ORDER BY count DESC ";

		$result = mysqli_query($db,$query);
		$result2 = mysqli_query($db,$query);
      	?>
	  
		<script type="text/javascript" src = "https://www.gstatic.com/charts/loader.js"></script>
		<script type="text/javascript">
         
		 google.charts.load('current', {'packages':['corechart']});
         google.charts.setOnLoadCallback(drawChart);
		 
		 google.charts.load('current', {'packages':['table']});
         google.charts.setOnLoadCallback(drawTable);

         function addRow_chart(data_chart, row1, row2,row3,row4, row5) {
            data_chart.addRows([
               [row1, {v:Number(row2), f:row2}, {v:Number(row3), f:row3} ,row4, row5]
            ]);
         }
         function drawChart() {
			 
            var data_chart = new google.visualization.DataTable();
			
				data_chart.addColumn('string', 'code');
				data_chart.addColumn('number', 'count');
				data_chart.addColumn({type: 'number', role: 'annotation'});
				data_chart.addColumn({type: 'string', role: 'annotationText'});
				data_chart.addColumn({type: 'string', role: 'tooltip'});

				<?php while($row = mysqli_fetch_array($result)) { ?>

                var row1 = '<?php echo $row['code'] ?>';
			    var row2 = '<?php echo $row['count'] ?>';
				var row3 = '<?php echo $row['count'] ?>';
				var row4 = '<?php echo $row['link'] ?>';
				var row5 = '<?php 
				$temp = str_replace("'", "\\'", $row['summary_content']);
				$temp = str_replace("\n", "\\\n", $temp);
				echo $temp;
				?>';
				
               addRow_chart(data_chart, row1, row2, row3, row4, row5);
			   
            <?php } ?>

      var options = {
		  colors: ['#00008B'],
		  bars: 'vertical',
               legend: { position: "top" },
               isStacked: false,
               //tooltip:{textStyle : {fontSize:10}, showColorCode : true},
			   tooltip:{ isHtml: true },
               animation: { //차트가 뿌려질때 실행될 애니메이션 효과
               startup: true,
               duration: 1000,
               easing: 'linear' },
               annotations: {
                   textStyle: {
                     fontSize: 12,
                     bold: true,
                     italic: true,
                     color: '#00498C',
                     auraColor: '#d799ae',
                     opacity: 0.8
                   }
              }

        };
            var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
			chart.draw(data_chart, options);

			var selectHandler = function(e) {
         	window.location = data_chart.getValue(chart.getSelection()[0]['row'], 3);
        }

        // Add our selection handler.
        google.visualization.events.addListener(chart, 'select', selectHandler);
}

         function addRow(data, row0, row1, row2, row3, row4, row5,row6) {
            data.addRows([
               [row0, row1, row2, row3, row4, {v:Number(row5), f:row5}, row6]
            ]);
         }
		 
         function drawTable() {
			 
            var data = new google.visualization.DataTable();
			data.addColumn('string', 'D-DAY');
			data.addColumn('string', '코드번호');
            data.addColumn('string', '청원시작일');
            data.addColumn('string', '청원종료일');
            data.addColumn('string', '제목');
            data.addColumn('number', '청원동의 참여인원');
            data.addColumn('string', '청원링크');
			
            <?php while($row = mysqli_fetch_array($result2)) { ?>
				var row0 = '<?php echo $row['ddays'] ?>';
			   var row1 = '<?php echo $row['code'] ?>';
               var row2 = '<?php echo $row['sdays'] ?>';
               var row3 = '<?php echo $row['edays'] ?>';
               var row4 = '<?php 
			   $temp2 = str_replace("'","\\'", $row['title']);
			   $temp2 = str_replace("\n","\\\n",$temp2);
			   echo $temp2;

			   ?>';
               var row5 = '<?php echo $row['count'] ?>';
               var row6 = '<?php echo $row['link'] ?>';
			   
               addRow(data, row0, row1, row2, row3, row4, row5,row6);
			   
            <?php } ?>

            var table = new google.visualization.Table(document.getElementById('table_div'));
            table.draw(data);

       	 var selectHandler = function(e) {
         	window.location = data.getValue(table.getSelection()[0]['row'], 6 );
        }

        // Add our selection handler.
        google.visualization.events.addListener(table, 'select', selectHandler);

      }
	  
</script>
<style>
	p {
		text-align:center;
	}
	.google-visualization-tooltip {
		background-color: #FAFAFA;
		padding: 5px 15px 5px 15px;
		border: 1px solid #737373;
		opacity: 1;
		border-radius: 10%;
		font-size:  10px ;
		font-family: verdana;
		  
	}
	.google-visualization-table-td {
	text-align: center !important;
	}

</style>
   </head>
   <body>
		<h1 style = "margin:auto; text-align:center;"> 청원 동의 현황 </h1>
		<h5 style = "margin:auto; text-align:center; color: #868e96;">해당 그래프를 클릭하여 바로 청원하러 가세요.</h5>
		<p><a href= 'wordcloud_sample.php'>전체 목록 보기</a></p>
		<p><a href= 'mainpage.php'>메인 페이지로 가기</a></p>
		<div id ="chart_div" style= " width: 950px; height:500px; margin: auto; text-align:center;"></div>
		<br>
		<div id ="table_div" style="width: 100%; height: 70%; margin: auto; text-align:center;"></div>
   </body>
</html>
