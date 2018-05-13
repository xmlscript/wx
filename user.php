<?php namespace wx; // vim: se fdm=marker:

use http\request;

final class user{

  /**
   * @param string $lang='en' zh_CN | zh_TW 胡乱填写还是默认en，中国显示China
   */
  function __construct(token $token, string $lang='en'){
    foreach(token::check(request::url(token::HOST.'/sns/userinfo')
      ->fetch(['access_token'=>$token->access_token,'openid'=>$token->openid,'lang'=>$lang])
      ->json()) as $k=>$v)
      $this->$k = $v;
  }

}
