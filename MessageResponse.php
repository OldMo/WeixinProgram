<?php
define("TOKEN", "tiantianyouni");

$wechatObj = new wechatCallbackapiTest();
if (!isset($_GET['echostr'])) {
    $wechatObj->responseMsg();
}else{
    $wechatObj->valid();
}



class wechatCallbackapiTest
{
	
	public function valid()
    {
	echo "test";
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }
	
	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}

   
		 
	//响应消息
    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            $this->logger("R ".$postStr);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
		   echo $RX_TYPE;
            switch ($RX_TYPE)
           {
               case "event":
                    $result = $this->receiveEvent($postObj);
                    break;
               case "text":
                   $result = $this->receiveText($postObj);
                   break;
                // case "image":
                    // $result = $this->receiveImage($postObj);
                    // break;
                // case "location":
                    // $result = $this->receiveLocation($postObj);
                    // break;
                // case "voice":
                    // $result = $this->receiveVoice($postObj);
                    // break;
                // case "video":
                    // $result = $this->receiveVideo($postObj);
                    // break;
                // case "link":
                    // $result = $this->receiveLink($postObj);
                    // break;
               default:
					$content = "亲，谢谢您对我们的关注与支持！  如果您有具体需求和问题，欢迎添加天天网版主-小天天私人微信：dailynetca , 给小天天留言，我们会及时回复。同时欢迎您访问天天网网页www.dailynet.ca ";
                    $result = $this->transmitText($postObj, $content);
                    break;  
            }
            echo $result;
        }else {
            echo "";
            exit;
        }
    }
	
	 //接收事件消息
    private function receiveEvent($object)
    {
        $content = array();
        switch ($object->Event)
        {
            case "subscribe":
            $content = "亲，谢谢你的关注！点击屏幕右上角，打开“查看历史消息“可以看到更多的内容！也欢迎亲访问加拿大天天网的官网www.dailynet.ca以及我们的新浪微博主页，我们的微信平台会不断努力为你的生活，工作，交友以及吃喝玩乐行等提供方便！mo-示爱";
                $content .= (!empty($object->EventKey))?("\n来自二维码场景 ".str_replace("qrscene_","",$object->EventKey)):"";
                break;
            case "unsubscribe":
                $content = "取消关注";
                break;
            case "SCAN":
                $content = "扫描场景 ".$object->EventKey;
                break;
            case "CLICK":
				$myconn=mysql_connect("localhost","root","root");
				mysql_query("set names 'utf8'"); //指定写入编码
				mysql_select_db("dbname",$myconn);
			
                switch ($object->EventKey)
                {
					case "TIPS":
						$content = "如需根据关键词查找文章，请在输入框按以下形式输入：S+内容，例如:S+加拿大,S+温泉";
                        break;
                    case "wgh":
						$strSql = "select * from weixinarticles where locate('温哥华',title)>0";
						$this->logger("click menu: ".$strSql);
						$content = $this->selectArticle($strSql,$myconn);
                        break;
					 case "wdly":
                        $strSql = "select * from weixinarticles where locate('维多利亚',title)>0";
						$content = $this->selectArticle($strSql,$myconn);
                        break;
					 case "nnm":
                        $strSql = "select * from weixinarticles where locate('纳奈莫',title)>0";
						$content = $this->selectArticle($strSql,$myconn);
                        break;
                    default:
                        $content = "点击菜单：".$object->EventKey;
                        break;
                }
				mysql_close($myconn);
                break;
            case "LOCATION":
                $content = "上传位置：纬度 ".$object->Latitude.";经度 ".$object->Longitude;
                break;
            case "VIEW":
                $content = "跳转链接 ".$object->EventKey;
                break;
            default:
                $content = "receive a new event: ".$object->Event;
                break;
        }
		if(is_array($content)){
            if (isset($content[0]['PicUrl'])){
				$this->logger("send1:pic ");
                $result = $this->transmitNews($object, $content);
            }
        }else{
            $result = $this->transmitText($object, $content);
			$this->logger("send2: text");
        }
        return $result;
    }
	

	
	public function selectArticle($strSql,$myconn){
		
		$result=mysql_query($strSql,$myconn);
		$content = array();				
		$count = 1;
		if(mysql_num_rows($result))
		{ 
			while($row=mysql_fetch_array($result))//通过循环读取数据内容
			{
				echo $row["title"].'------'.$row["imglink"].'------'.$row["url"];
				$content[] = array("Title"=>$row["title"], "Description"=>"", "PicUrl"=>$row["imglink"], "Url" =>$row["url"]);
				if($count++ >= 10)
					break;
			}
			$content[] = array("Title"=>"查看更多", "Description"=>"", "PicUrl"=>"", "Url" =>"http://mp.weixin.qq.com/mp/getmasssendmsg?__biz=MjM5MDIwNTYwNQ==#wechat_webview_type=1&wechat_redirect");
		}
		else{
			$content[] = array("Title"=>"未查询到，点此查看其他文章", "Description"=>"", "PicUrl"=>"", "Url" =>"http://mp.weixin.qq.com/mp/getmasssendmsg?__biz=MjM5MDIwNTYwNQ==#wechat_webview_type=1&wechat_redirect");
		}
		
		return $content;
	}
	
	 //接收文本消息
    private function receiveText($object)
    {
		
		$text = $object->Content;
		//$conLength = strlen($text);
		$this->logger("content:".$text."Length ".$conLength);
		if(strpos($text,'s+') !== false || strpos($text,'S+') !== false){
			$myconn=mysql_connect("localhost","root","root");
			mysql_query("set names 'utf8'"); //指定写入编码
			mysql_select_db("dbname",$myconn);

			$content = array();
			$searchText = substr($text,2);
			$strSql = "select * from weixinarticles where locate('".$searchText."',title)>0";
			echo $strSql;
			$content = $this->selectArticle($strSql,$myconn);
			
			mysql_close($myconn);
		}
		else{
		
			$content = "亲，谢谢您对我们的关注与支持！  如果您有具体需求和问题，欢迎添加天天网版主-小天天私人微信：dailynetca , 给小天天留言，我们会及时回复。同时欢迎您访问天天网网页www.dailynet.ca ";
		}
			
        if(is_array($content)){
            if (isset($content[0]['PicUrl'])){
                $result = $this->transmitNews($object, $content);
            }
        }else{
            $result = $this->transmitText($object, $content);
        }
		
		
        return $result;
    }  
	
	 //回复文本消息
    private function transmitText($object, $content)
    {
        $textTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            </xml>";
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }
	
	 //回复图文消息
    private function transmitNews($object, $newsArray)
    {
        if(!is_array($newsArray)){
            return;
        }
        $itemTpl = "    
		<item>
			<Title><![CDATA[%s]]></Title>
			<Description><![CDATA[%s]]></Description>
			<PicUrl><![CDATA[%s]]></PicUrl>
			<Url><![CDATA[%s]]></Url>
		</item>";
        $item_str = "";
        foreach ($newsArray as $item){
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $newsTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[news]]></MsgType>
            <Content><![CDATA[]]></Content>
            <ArticleCount>%s</ArticleCount>
            <Articles>
            $item_str</Articles>
            </xml>";

        $result = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
        return $result;
    }
	
	
	
	  //接收图片消息
    private function receiveImage($object)
    {
        $content = array("MediaId"=>$object->MediaId);
        $result = $this->transmitImage($object, $content);
        return $result;
    }

    //接收位置消息
    private function receiveLocation($object)
    {
        $content = "你发送的是位置，纬度为：".$object->Location_X."；经度为：".$object->Location_Y."；缩放级别为：".$object->Scale."；位置为：".$object->Label;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //接收语音消息
    private function receiveVoice($object)
    {
        if (isset($object->Recognition) && !empty($object->Recognition)){
            $content = "你刚才说的是：".$object->Recognition;
            $result = $this->transmitText($object, $content);
        }else{
            $content = array("MediaId"=>$object->MediaId);
            $result = $this->transmitVoice($object, $content);
        }

        return $result;
    }
	  //接收视频消息
    private function receiveVideo($object)
    {
        $content = array("MediaId"=>$object->MediaId, "ThumbMediaId"=>$object->ThumbMediaId, "Title"=>"", "Description"=>"");
        $result = $this->transmitVideo($object, $content);
        return $result;
    }

    //接收链接消息
    private function receiveLink($object)
    {
        $content = "你发送的是链接，标题为：".$object->Title."；内容为：".$object->Description."；链接地址为：".$object->Url;
        $result = $this->transmitText($object, $content);
        return $result;
    }
	    //日志记录
    private function logger($log_content)
    {
            $max_size = 10000;
            $log_filename = "log.xml";
            if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
            file_put_contents($log_filename, date('H:i:s')." ".$log_content."\r\n", FILE_APPEND);
    }
		 

}

?>