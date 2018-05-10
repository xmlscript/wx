<?php namespace wx; // vim: se fdm=marker:

use http\request;

class user{

  public $openid, $nickname, $sex, $province, $city, $country, $headimgurl, $privilege, $unionid;

  /**
   * @param string $lang zh_CN | zh_TW | en
   */
  function __construct(token $token, string $lang='zh_CN'){
    foreach($this->check(request::url($token::HOST.'/sns/userinfo')
      ->fetch(['access_token'=>$token->access_token,'openid'=>$token->openid,'lang'=>$lang])
      ->json()) as $k=>$v)
      $this->$k = $v;
  }


  private function check(\stdClass $json):\stdClass{
    if(isset($json->errcode,$json->errmsg)&&$json->errcode)
      throw new \RuntimeException($json->errmsg,$json->errcode);
    return $json;
  }

}
