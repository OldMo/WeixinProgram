<?php 
@header('Content-type: text/html;charset=UTF-8'); 
	 
/**********
	数据库操作
*****/
$myconn=mysql_connect("localhost","root","");
mysql_query("set names 'utf8'"); //指定写入编码
mysql_select_db("Article",$myconn);

$selectSql = "select * from articles order by postday desc limit 1";  //获取最新文章记录

$top_record=mysql_query($selectSql,$myconn);
$postday = "";
if( mysql_num_rows($top_record))
{ 
	while($row=mysql_fetch_array($top_record))//通过循环读取数据内容
	{
		$postday = $row["postday"];
		echo $row["title"].'------'.$row["postday"].'<br/>';
	}
}
for($page = 1; $page <= 4; $page++)
{
	$url = "http://weixin.sogou.com/gzhjs?cb=sogou.weixin.gzhcb&openid=oIWsFt-4lR2-450wfo60XXrtklqY&eqs=cls2o4dgqyYXowtDdJkJRuTSG9PcwNTSF%2B8KujiGLML7bPu3Nc9gcwQOZa6WL7Ob44OuT&ekv=7&page=".$page."&t=1435421383410"; 
	$ch = curl_init(); 
	curl_setopt ($ch, CURLOPT_URL, $url); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,100);
	 
	$content = curl_exec($ch); 

	$s = str_replace("\\","",$content);  //去掉转义符

	$arr = explode("\",\"",$s);         //分割xml段

	$m = 0;
	
	foreach($arr as $value){
		preg_match_all("/\<title\>\<!\[CDATA\[(.*)\]\]\>\<\/title\>/",$value,$titleArea);//匹配标题
		$title = current($titleArea[1]);
		
		preg_match_all("/\<url\>\<!\[CDATA\[(.*)\]\]\>\<\/url\>/",$value,$urlArea);//匹配文章url
		$url = current($urlArea[1]);
		
		preg_match_all("/\<imglink\>\<!\[CDATA\[(.*)\]\]\>\<\/imglink\>/",$value,$imglinkArea);//匹配图片url
		$imglink = current($imglinkArea[1]);
		
		preg_match_all("/\<content168\>\<!\[CDATA\[(.*)\]\]\>\<\/content168\>/",$value,$contentArea);//匹配文章内容
		$content = current($contentArea[1]);
		
		preg_match_all("/\<date\>\<!\[CDATA\[(.*)\]\]\>\<\/date\>/",$value,$dateArea);//匹配文章日期
		$date = current($dateArea[1]);
		$result = strcasecmp($date,$postday);
		echo $date.'<br/>';
		if($result > 0){
			echo $title.'<br/>';
			$str_title = str_replace('%',"%%",$title); //%要替换成两个，不然会与微信平台json格式冲突而出错
			$insertSql="insert into articles(title,url,imglink,content,postday) values('".$str_title."','".$url."','".$imglink."','".$content."','".$date."')";
			$result=mysql_query($insertSql,$myconn);
			echo $str_title.'--'.$date.'<br/>';
		}
		else{
			break;
		}
		
	}
}
 //关闭对数据库的连接
  mysql_close($myconn);

?>
