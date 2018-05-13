<?php namespace wx; // vim: se fdm=marker:

use http\request;

class user{

  public $openid, $nickname, $sex, $province, $city, $country, $headimgurl, $privilege, $unionid;

  /**
   * @param string $lang='en' zh_CN | zh_TW 胡乱填写还是默认en，中国显示China
   */
  function __construct(token $token, string $lang='en'){
    foreach($this->check(request::url(token::HOST.'/sns/userinfo')
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
