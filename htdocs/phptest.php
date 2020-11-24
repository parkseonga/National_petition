<?php
	echo "안녕하세요<br>";
	ECHO("괄호 사용 가능!<br>"); 
	echo "첫번째 인수,","두번째인수".
	" 세번째 인수";
	$var = 10;
	echo "$var";
	echo "{$var}";

# \뒤에는 php $ 기능이 아닌 $ 그 자체 값으로 받아들임 
	echo "<br>1. 변수 \$var에 저장된 값 $var";
	echo "2. 변수 \$var에 저장된 값 {$var}";
?>