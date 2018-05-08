<?php namespace wx; // vim: se fdm=marker:

class config{

  public $timestamp, $nonceStr, $signature;

  function __construct(token $token, string &$url){
    $arr = [
      'timestamp' => $time=time(),
      'noncestr' => $nonceStr=md5($time+$_SERVER['REQUEST_TIME_FLOAT']),
      'jsapi_ticket' => new ticket($token,'jsapi'),
      'url' => $url,
    ];
    sort($arr,SORT_STRING);
    $this->timestamp = $time;
    $this->nonceStr = $nonceStr;
    $this->signature = sha1(http_build_query($arr));
  }

}
