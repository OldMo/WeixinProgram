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

   
		 
	//��Ӧ��Ϣ
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
					$content = "�ף�лл�������ǵĹ�ע��֧�֣�  ������о�����������⣬��ӭ�������������-С����˽��΢�ţ�dailynetca , ��С�������ԣ����ǻἰʱ�ظ���ͬʱ��ӭ��������������ҳwww.dailynet.ca ";
                    $result = $this->transmitText($postObj, $content);
                    break;  
            }
            echo $result;
        }else {
            echo "";
            exit;
        }
    }
	
	 //�����¼���Ϣ
    private function receiveEvent($object)
    {
        $content = array();
        switch ($object->Event)
        {
            case "subscribe":
            $content = "�ף�лл��Ĺ�ע�������Ļ���Ͻǣ��򿪡��鿴��ʷ��Ϣ�����Կ�����������ݣ�Ҳ��ӭ�׷��ʼ��ô��������Ĺ���www.dailynet.ca�Լ����ǵ�����΢����ҳ�����ǵ�΢��ƽ̨�᲻��Ŭ��Ϊ�����������������Լ��Ժ������е��ṩ���㣡mo-ʾ��";
                $content .= (!empty($object->EventKey))?("\n���Զ�ά�볡�� ".str_replace("qrscene_","",$object->EventKey)):"";
                break;
            case "unsubscribe":
                $content = "ȡ����ע";
                break;
            case "SCAN":
                $content = "ɨ�賡�� ".$object->EventKey;
                break;
            case "CLICK":
				$myconn=mysql_connect("localhost","root","root");
				mysql_query("set names 'utf8'"); //ָ��д�����
				mysql_select_db("dbname",$myconn);
			
                switch ($object->EventKey)
                {
					case "TIPS":
						$content = "������ݹؼ��ʲ������£����������������ʽ���룺S+���ݣ�����:S+���ô�,S+��Ȫ";
                        break;
                    case "wgh":
						$strSql = "select * from weixinarticles where locate('�¸绪',title)>0";
						$this->logger("click menu: ".$strSql);
						$content = $this->selectArticle($strSql,$myconn);
                        break;
					 case "wdly":
                        $strSql = "select * from weixinarticles where locate('ά������',title)>0";
						$content = $this->selectArticle($strSql,$myconn);
                        break;
					 case "nnm":
                        $strSql = "select * from weixinarticles where locate('����Ī',title)>0";
						$content = $this->selectArticle($strSql,$myconn);
                        break;
                    default:
                        $content = "����˵���".$object->EventKey;
                        break;
                }
				mysql_close($myconn);
                break;
            case "LOCATION":
                $content = "�ϴ�λ�ã�γ�� ".$object->Latitude.";���� ".$object->Longitude;
                break;
            case "VIEW":
                $content = "��ת���� ".$object->EventKey;
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
			while($row=mysql_fetch_array($result))//ͨ��ѭ����ȡ��������
			{
				echo $row["title"].'------'.$row["imglink"].'------'.$row["url"];
				$content[] = array("Title"=>$row["title"], "Description"=>"", "PicUrl"=>$row["imglink"], "Url" =>$row["url"]);
				if($count++ >= 10)
					break;
			}
			$content[] = array("Title"=>"�鿴����", "Description"=>"", "PicUrl"=>"", "Url" =>"http://mp.weixin.qq.com/mp/getmasssendmsg?__biz=MjM5MDIwNTYwNQ==#wechat_webview_type=1&wechat_redirect");
		}
		else{
			$content[] = array("Title"=>"δ��ѯ������˲鿴��������", "Description"=>"", "PicUrl"=>"", "Url" =>"http://mp.weixin.qq.com/mp/getmasssendmsg?__biz=MjM5MDIwNTYwNQ==#wechat_webview_type=1&wechat_redirect");
		}
		
		return $content;
	}
	
	 //�����ı���Ϣ
    private function receiveText($object)
    {
		
		$text = $object->Content;
		//$conLength = strlen($text);
		$this->logger("content:".$text."Length ".$conLength);
		if(strpos($text,'s+') !== false || strpos($text,'S+') !== false){
			$myconn=mysql_connect("localhost","root","root");
			mysql_query("set names 'utf8'"); //ָ��д�����
			mysql_select_db("dbname",$myconn);

			$content = array();
			$searchText = substr($text,2);
			$strSql = "select * from weixinarticles where locate('".$searchText."',title)>0";
			echo $strSql;
			$content = $this->selectArticle($strSql,$myconn);
			
			mysql_close($myconn);
		}
		else{
		
			$content = "�ף�лл�������ǵĹ�ע��֧�֣�  ������о�����������⣬��ӭ�������������-С����˽��΢�ţ�dailynetca , ��С�������ԣ����ǻἰʱ�ظ���ͬʱ��ӭ��������������ҳwww.dailynet.ca ";
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
	
	 //�ظ��ı���Ϣ
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
	
	 //�ظ�ͼ����Ϣ
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
	
	
	
	  //����ͼƬ��Ϣ
    private function receiveImage($object)
    {
        $content = array("MediaId"=>$object->MediaId);
        $result = $this->transmitImage($object, $content);
        return $result;
    }

    //����λ����Ϣ
    private function receiveLocation($object)
    {
        $content = "�㷢�͵���λ�ã�γ��Ϊ��".$object->Location_X."������Ϊ��".$object->Location_Y."�����ż���Ϊ��".$object->Scale."��λ��Ϊ��".$object->Label;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //����������Ϣ
    private function receiveVoice($object)
    {
        if (isset($object->Recognition) && !empty($object->Recognition)){
            $content = "��ղ�˵���ǣ�".$object->Recognition;
            $result = $this->transmitText($object, $content);
        }else{
            $content = array("MediaId"=>$object->MediaId);
            $result = $this->transmitVoice($object, $content);
        }

        return $result;
    }
	  //������Ƶ��Ϣ
    private function receiveVideo($object)
    {
        $content = array("MediaId"=>$object->MediaId, "ThumbMediaId"=>$object->ThumbMediaId, "Title"=>"", "Description"=>"");
        $result = $this->transmitVideo($object, $content);
        return $result;
    }

    //����������Ϣ
    private function receiveLink($object)
    {
        $content = "�㷢�͵������ӣ�����Ϊ��".$object->Title."������Ϊ��".$object->Description."�����ӵ�ַΪ��".$object->Url;
        $result = $this->transmitText($object, $content);
        return $result;
    }
	    //��־��¼
    private function logger($log_content)
    {
            $max_size = 10000;
            $log_filename = "log.xml";
            if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
            file_put_contents($log_filename, date('H:i:s')." ".$log_content."\r\n", FILE_APPEND);
    }
		 

}

?>