<?php namespace wx; // vim: se fdm=marker:

class config{

  public $timestamp, $nonceStr, $signature;

  /**
   * @see https://mp.weixin.qq.com/debug/cgi-bin/sandbox?t=jsapisign
   */
  function __construct(\mp\token $token, string &$url){
    $arr = [
      'jsapi_ticket' => new ticket($token,'jsapi'),
      'noncestr' => md5($_SERVER['REQUEST_TIME_FLOAT']),
      'timestamp' => $_SERVER['REQUEST_TIME'],
      'url' => $url,
    ];
    ksort($arr);//手动按顺序整理好了，这一步多余
    $this->appid = $token->appid;
    $this->timestamp = $arr['timestamp'];
    $this->nonceStr = $arr['noncestr'];
    $this->signature = sha1(urldecode(http_build_query($arr)));//不能转义，所以urldecode中和一下
  }

  static function construct(string $url){
    $arr = [
      'jsapi_ticket' => 't',
      'noncestr' => md5($_SERVER['REQUEST_TIME_FLOAT']),
      'timestamp' => $_SERVER['REQUEST_TIME'],
      'url' => $url,
    ];
    ksort($arr);//手动按顺序整理好了，这一步多余
    return [
    'appid' => $token->appid,
    'timestamp' => $arr['timestamp'],
    'nonceStr' => $arr['noncestr'],
    'signature' => sha1(urldecode(http_build_query($arr)))
    ];
  }

}
