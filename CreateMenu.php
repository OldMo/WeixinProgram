<?php

    $appid = "wx******a7";
    $appsecret = "26**************1f5";
    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    $jsoninfo = json_decode($output, true);
    $access_token = $jsoninfo["access_token"];
    echo $access_token.'<br/>';


$jsonmenu = '{
     "button":[
     {    
          "type":"click",
          "name":"���ٲ���",
          "key":"TIPS"
      },
      {
           "name":"���ŵ���",
           "sub_button":[
		   {
			   "type":"click",
			   "name":"���¸绪��",
			   "key":"wgh"
		   },
		   {
			   "type":"click",
			   "name":"��ά��������",
			   "key":"wdly"
		   },
		   {
			   "type":"click",
			   "name":"����Ī��",
			   "key":"nnm"
		   }
		   ]
      },
      {
          
		   "type":"click",
		   "name":"��ϵС����",
		   "key":"http://dailynet.ca/weixin/"
         
       }]
 }';

    
    $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
    $result = https_request($url, $jsonmenu);
    var_dump($result);
    
    function https_request($url,$data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
?>