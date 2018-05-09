<?php namespace wx; // vim: se fdm=marker:

class config{

  public $timestamp, $nonceStr, $signature;

  function __construct(token $token, string &$url){
    $arr = [
      'timestamp' => $_SERVER['REQUEST_TIME'],
      'noncestr' => md5($_SERVER['REQUEST_TIME_FLOAT']),
      'jsapi_ticket' => new ticket($token,'jsapi'),
      'url' => $url,
    ];
    sort($arr,SORT_STRING);
    $this->timestamp = $arr['timestamp'];
    $this->nonceStr = $arr['noncestr'];
    $this->signature = sha1(http_build_query($arr));
  }

}
