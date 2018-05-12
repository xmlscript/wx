<?php namespace wx; // vim: se fdm=marker:

final class config{

  function __construct(\mp\token $token, string &$url){
    $arr = [
      'jsapi_ticket' => (string)new ticket($token,'jsapi'),
      'noncestr' => md5($_SERVER['REQUEST_TIME_FLOAT']),
      'timestamp' => $_SERVER['REQUEST_TIME'],
      'url' => $url,
    ];
    //ksort($arr);
    $this->appId = $token->appid;
    $this->timestamp = $arr['timestamp'];
    $this->nonceStr = $arr['noncestr'];
    $this->signature = sha1(urldecode(http_build_query($arr)));
  }

  function __toString():string{
    return json_encode($this);
  }

}
