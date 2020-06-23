<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" 
        content="text/html; charset=UTF-8">
<title>PHP入門</title>
</head>

<body>
<p>今日は、<?php echo date("Y/m/d"); ?> です。</p>
</body>
<p>
  <?php
     define("banban", "ばんばん");
     print(banban);                // 定数banban の中身を表示する
  ?>
<p>
	<?php
  $fruit = array("Apple" => "りんご", "Orange" => "みかん", 
                 "Grape" => "ぶどう");
    while(list ($key, $val) = each($fruit)) {
      print ("インデックスの $key は、$val です<br>\n");
     }
?>
</p>
<p>
<?php
    $x = 15;
    echo "<p>変数\$x の代入された値は 15です。</p>\n";

    if($x == 15)  echo  "\$x の値は 15 に等しい<br>\n";
    if($x < 10)  echo  "\$x の値は 10 より大きい<br>\n";
    if($x > 20)  echo  "\$x の値は 20 より小さい<br>\n";
    if($x <= 10)  echo  "\$x の値は 10 より大きいか等しい<br>\n";
    if($x <= 20)  echo  "\$x の値は 20 より小さいか等しい<br>\n";
    if($x != 10)  echo  "\$x の値は 10 と等しくない<br>\n";
    if($x <> 10)  echo  "\$x の値は 10 と等しくない";
?>
</p>
</html>