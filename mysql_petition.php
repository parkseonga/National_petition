<?php
	echo "Mysql petition.petition_daily table<br/>";
	echo "20171490 Seonga Park<br/>";
	$db = mysqli_connect("localhost","root","seonga","national_petition");
	if($db)
		echo "connect success<br/>";
	else
		echo "connect failed<br>";
	$query = "SELECT * FROM national_petition.petition";
	$result = mysqli_query($db,$query);
	echo '<table border="1"><tr>'.
	'<th>code</th><th>sdays</th><th>edays</th><th>title</th><th>content</th><th>count</th><th>category</th><th>progress</th>'.
	'</tr>';

	while($row = mysqli_fetch_array($result)){
		echo '<tr><td>'.$row['code'].'</td>'.
		'<td>'.$row['sdays'].'</td>'.
		'<td>'.$row['edays'].'</td>'.
		'<td>'.mb_strimwidth($row['title'], 0, 40, '...').'</td>'.
		'<td>'.mb_strimwidth($row['content'], 0, 40, '...').'</td>'.
		'<td>'.$row['count'].'</td>'.
		'<td>'.$row['category'].'</td>'.
		'<td>'.$row['progress'].'</td></tr>';
	}	
	echo '</table>';
	mysqli_close($db);
	
?>