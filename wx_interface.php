<?php
/**
  * wechat php test
  */

//define your tokensdsadasd
include_once("wx_tpl.php");
//阿里云搜索sdk
require_once("php_v2.0.6/CloudsearchClient.php");
require_once("php_v2.0.6/CloudsearchIndex.php");
require_once("php_v2.0.6/CloudsearchDoc.php");
require_once("php_v2.0.6/CloudsearchSearch.php");

define("TOKEN", "782879714");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->responseMsg();

class wechatCallbackapiTest
{
    public $fromUsername;
    public $toUsername;
    public $content;
    public $fromMsgType;
    public $fromMsgEvent;
    public $postStr;
    public $time;
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }
	private function readMainParam()
    {
        //get post data, May be due to the different environments
        $this->postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($this->postStr))
        {
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                   the best way is to check the validity of xml by yourself */
            libxml_disable_entity_loader(true);
            $this->postObj = simplexml_load_string($this->postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $this->fromUsername = $this->postObj->FromUserName;
            $this->toUsername = $this->postObj->ToUserName;
            $this->fromMsgType = $this->postObj->MsgType;
            $this->content = trim($this->postObj->Content);
            $this->fromMsgEvent = $this->postObj->Event;
            $this->time = time();
        }
    }
    public function sendMsg($Tpl,$msgType,$contentStr)
    {
        $resultStr = sprintf($Tpl, $this->fromUsername, $this->toUsername, $this->time, $msgType, $contentStr);
        echo $resultStr;
    }
    public function sendSubscribeMsg()
    {
        $msgType = "text";
        $contentStr = "欢迎来到笑话搜索器 by fzt!";
        global $textTpl;
        $this->sendMsg($textTpl,$msgType,$contentStr);
    }
    public function sendTextMsg()
    {
        $msgType = "text";
        $contentStr = $this->aliyun();
        if($contentStr == "")
        {
            $contentStr = "sorry ,没有搜到相关笑话";
        }
        global $textTpl;
        $this->sendMsg($textTpl,$msgType,$contentStr);
    }
    public function aliyun()
    {
        $access_key = "NELsu0y0FB2U6s5R";
		$secret = "aMgITmz2kC069KqrbqVRX0daWb8Nzx";
        $host = "http://opensearch-cn-hangzhou.aliyuncs.com";
        $key_type = "aliyun";  //固定值，不必修改
        $opts = array('host'=>$host);
        // 实例化一个client 使用自己的accesskey和Secret替换相关变量
		$client = new CloudsearchClient($access_key,$secret,$opts,$key_type);
        $app_name = "joke";
		// 实例化一个搜索类 search_obj
		$search_obj = new CloudsearchSearch($client);
        // 指定一个应用用于搜索
		$search_obj->addIndex($app_name);
        // 指定搜索关键词
		$search_obj->setQueryString("default:".$this->content);
		// 指定返回的搜索结果的格式为json
		$search_obj->setFormat("json");
		// 执行搜索，获取搜索结果
		$json = $search_obj->search();
		// 将json类型字符串解码
		$result = json_decode($json,true);
        return $result["result"]["items"][0]["body"];
    }
    public function responseMsg()
    {
      	//extract post data
		$this->readMainParam();
                          
        if($this->fromMsgType == "event")
        {
            switch($this->fromMsgEvent)
            {
                case "subscribe":
                $this->sendSubscribeMsg();
                break;
            }
        }
        else if($this->fromMsgType=="text")
        {
            $this->sendTextMsg();
        }
    }
		
	private function checkSignature()
	{
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
}
?>