<?php namespace mp; // vim: se fdm=marker:

use http\request;
use tmp\cache;

class ticket{

  /**
   * 公众号内嵌网页需要调用JSSDK，首先需要使用token获取ticket，进而计算得到signature
   * ticket应该在服务端缓存一份，7200秒(两小时)有效期
   */
  function __construct(){
    if($ticket = (string)new cache($this->appid.__CLASS__,$this->secret,7200))
      return $ticket;
    else{
      $result = request::url($this->host.'/cgi-bin/ticket/getticket')
        ->fecth(['access_token'=>$this->token()])
        ->json();
      if(isset($result->ticket)){
        return (new cache($this->appid.__FUNCTION__,$this->secret))($result->ticket)[0];
      }else
        throw new \Exception($result->errmsg, $result->errcode);
    }
  }

}
