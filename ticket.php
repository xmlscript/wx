<?php namespace wx; // vim: se fdm=marker:

use http\request;
use tmp\cache;

/**
 * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421141115
 */
class ticket{

  public $ticket;
  
  //FIXME 没有约束type可能导致被cache吞噬的异常
  //FIXME 别处刷ticket导致提前过期，尚未测试错误码，可能是9001003，则应该手动清除缓存
  final function __construct(\mp\token $token, string $type){
    $this->ticket = new cache($token->appid.$type, $token->appid, 7200, function() use ($token,$type){
      return request::url(token::HOST.'/cgi-bin/ticket/getticket')
        ->fetch(['access_token'=>"$token",'type'=>$type])
        ->json()->ticket??null;
    });
  }


  function __toString():string{
    return $this->ticket;
  }

}
