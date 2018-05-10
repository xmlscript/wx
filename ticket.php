<?php namespace wx; // vim: se fdm=marker:

use http\request;
use tmp\cache;

/**
 * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421141115
 */
class ticket{

  private const HOST = 'https://api.weixin.qq.com';
  public $ticket;
  
  final function __construct(\mp\token $token, string $type){
    $this->ticket = new cache($token->appid.$type, $token->appid, 7200, function() use ($token,$type){
      return request::url(self::HOST.'/cgi-bin/ticket/getticket')
        ->fetch(['access_token'=>"$token",'type'=>$type])
        ->json()->ticket??null;
    });
  }


  function __toString():string{
    return $this->ticket;
  }

}
