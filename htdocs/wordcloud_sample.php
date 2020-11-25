<html>
   <head>
	<title>wordcloud</title>

	<?php
		$db = mysqli_connect("localhost","root",password,"national_petition");
		if($db)
			echo "<br/>";
		else
			echo "connect failed<br>";
		$id = 1;
		$query = "SELECT * FROM national_petition.petition_wordcloud WHERE id>=".$id." AND id<=".($id+8)." ORDER BY id ASC" ;
		#$query = "SELECT * FROM national_petition.petition_wordcloud WHERE id>=".$id." ORDER BY id ASC" ;

		$result = mysqli_query($db,$query);
		$chart_datas = array();
		$cur_id = 1;
		$chart_data = '';
		while($row = mysqli_fetch_array($result)) {
			if ($cur_id != $row['id']) {
				array_push($chart_datas, $chart_data);
				$chart_data = '';
				$cur_id = $row['id'];
			}
			$chart_data = $chart_data."{ x: '".$row["word"]."', value:".$row["count"]."}, ";
		}
		array_push($chart_datas, $chart_data);
					# print_r($chart_datas);
      	?>


<script src="https://cdn.anychart.com/releases/v8/js/anychart-base.min.js"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-tag-cloud.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script type="text/javascript" src = "https://www.gstatic.com/charts/loader.js"></script>
	<style>
	
  p{
	  text-align:center;
	 
  }
  table {
    width: 80%;
	height: 80%;
    border: 1px solid #444444;
	margin: auto;
	text-align: center;
  }
  th, td {
    border: 1px solid #444444;
	height: 80%;
	text-align: center;
	
  }
  
  html, body, div {
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
	text-align: center;
}
</style>
	
	</head>

 <body>
	<h1 style = "margin:auto; text-align:center;">진행중인 청원 한눈에 모아보기</h1>
	<h5 style = "margin:auto; text-align:center; color: #868e96;">시각화를 클릭하여 관련 청원을 확인하세요.<br>(청원동의 참여인원 높은 순으로 배치)</h5>
	<p><a href= 'mainpage.php'>main page로 돌아가기</a></p>

	<br>

    <div>
		<table>
			<tr><td><div id="container1"/></td><td><div id="container2"/></td></tr>
			<tr><td><div id="container3"/></td><td><div id="container4"/></td></tr>
			<tr><td><div id="container5"/></td><td><div id="container6"/></td></tr>
			<tr><td><div id="container7"/></td><td><div id="container8"/></td></tr>

		</table>
	</div>

<script>
 
anychart.onDocumentReady(function() {
	<?php 
		$count = 0;
		foreach ($chart_datas as $chart_data) { 
			$count+=1;
	?>
			var data =  [<?php echo $chart_data; ?>]
			// create a chart and set the data
			var chart = anychart.tagCloud(data);

			// create and configure a color scale.
			//var customColorScale = anychart.scales.ordinalColor();
			//customColorScale.ranges([
				//{less: 20},
				//{from: 40, to: 60},
				//{greater: 60}
			//]);

			//customColorScale.colors(["lightgray", "#ffcc00", "#00ccff"]);

			// set the color scale as the color scale of the chart
			// chart.colorScale(customColorScale);

			// add a color range
			//chart.colorRange().enabled(true);

			// set the chart title
			// chart.title("<?php echo 'wordcloud'.$count; ?>");

			// set the container id
			chart.container("<?php echo 'container'.$count; ?>");
			// chart.container("<?php echo 'container1' ?>");

			// initiate drawing the chart
			chart.draw();
			
			chart.normal().fontWeight(1000);
			
			chart.fromAngle(10);
			chart.toAngle(100);
			chart.anglesCount(5);

			// add an event listener
			chart.listen("Click", function(e){

			  var url = "<?php echo 'petition_total.php?id='.$count; ?>";
			  window.open(url, "_blank");
			});
						
			
	<?php } ?>
});

</script>

</body>
</html>
