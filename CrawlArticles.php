<?php 
@header('Content-type: text/html;charset=UTF-8'); 
	 
/**********
	���ݿ����
*****/
$myconn=mysql_connect("localhost","root","");
mysql_query("set names 'utf8'"); //ָ��д�����
mysql_select_db("Article",$myconn);

$selectSql = "select * from articles order by postday desc limit 1";  //��ȡ�������¼�¼

$top_record=mysql_query($selectSql,$myconn);
$postday = "";
if( mysql_num_rows($top_record))
{ 
	while($row=mysql_fetch_array($top_record))//ͨ��ѭ����ȡ��������
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

	$s = str_replace("\\","",$content);  //ȥ��ת���

	$arr = explode("\",\"",$s);         //�ָ�xml��

	$m = 0;
	
	foreach($arr as $value){
		preg_match_all("/\<title\>\<!\[CDATA\[(.*)\]\]\>\<\/title\>/",$value,$titleArea);//ƥ�����
		$title = current($titleArea[1]);
		
		preg_match_all("/\<url\>\<!\[CDATA\[(.*)\]\]\>\<\/url\>/",$value,$urlArea);//ƥ������url
		$url = current($urlArea[1]);
		
		preg_match_all("/\<imglink\>\<!\[CDATA\[(.*)\]\]\>\<\/imglink\>/",$value,$imglinkArea);//ƥ��ͼƬurl
		$imglink = current($imglinkArea[1]);
		
		preg_match_all("/\<content168\>\<!\[CDATA\[(.*)\]\]\>\<\/content168\>/",$value,$contentArea);//ƥ����������
		$content = current($contentArea[1]);
		
		preg_match_all("/\<date\>\<!\[CDATA\[(.*)\]\]\>\<\/date\>/",$value,$dateArea);//ƥ����������
		$date = current($dateArea[1]);
		$result = strcasecmp($date,$postday);
		echo $date.'<br/>';
		if($result > 0){
			echo $title.'<br/>';
			$str_title = str_replace('%',"%%",$title); //%Ҫ�滻����������Ȼ����΢��ƽ̨json��ʽ��ͻ������
			$insertSql="insert into articles(title,url,imglink,content,postday) values('".$str_title."','".$url."','".$imglink."','".$content."','".$date."')";
			$result=mysql_query($insertSql,$myconn);
			echo $str_title.'--'.$date.'<br/>';
		}
		else{
			break;
		}
		
	}
}
 //�رն����ݿ������
  mysql_close($myconn);

?>
