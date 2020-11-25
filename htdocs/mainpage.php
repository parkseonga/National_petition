<html>
   <head>
	<title>메인페이지</title>

	<?php
		$db = mysqli_connect("localhost","root",password,"national_petition");
		if($db)
			echo "<br/>";
		else
			echo "connect failed<br>";
		$id = 1;
		# $query = "SELECT * FROM national_petition.petition_wordcloud WHERE id>=".$id." AND id<=".($id+8)." ORDER BY id ASC" ;
		$query = "SELECT * FROM national_petition.petition_wordcloud WHERE id>=".$id." ORDER BY id ASC" ;

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
	
	</head>
<style>
	h1, p{
		text-align: center;
		margin: 25px;
	}
		.center {
			text-align: center;
			height: 150vh; 
			height: 150vh; 
			line-height: 50vh;
			
	
		} 
		.center span {
			width: 200px; height: 200px; 
			background-color: #fff; 
			border-radius: 50%; 
			text-align: center; 
			margin: 0px;
			line-height: 150px; 
			display: inline-block;  
			box-shadow: 0 2px 5px rgba(0,0,0,0.26); 
		}

</style>
 <body>
	<h1> 비슷한 국민 청원 게시글 분류하여 보기 </h1>
	<div class="center">
		<span><a href= 'wordcloud_sample.php'>청원 한눈에 모아 보기</a></span>
		<span>
		<form action = "petition_code.php?code=$_GET['code']">
		<legend>코드 번호로 유사 글 검색</legend>
		<input type='text' value ='코드번호를 입력해주세요.' name = 'code'/></form></span>
	<p><a href = 'https://www1.president.go.kr/petitions'><img src="코드번호.png"  style="
border: 2px solid black;" alt = '코드 번호'></a></p>
	</div>
</body>
</html>
