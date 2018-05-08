<?php namespace wx; // vim: se fdm=marker:

use http\request;

class user{

  public $openid, $nickname, $sex, $province, $city, $country, $headimgurl, $privilege, $unionid;

  /**
   * new userinfo(new token()->openid(xxx)) 尝试从缓存里找一份现成可用的数据，但缓存未必命中
   * 如果缓存没有命中，前端应该引导URL跳转，之后再重新发起code请求
   * 三种情况需要用到openid方式获取缓存里的数据：
   *  1、cli批处理纯库延迟操作，尽可能补全或更新所有已授权且未过期用户的详细信息
   *  2、用户在公众号页面里，已经使用手机号用户名等其他方式登录，以前简单绑定了openid，现在需要充实详细信息
   *  3、网页里在特定的时间，需要不定期更新用户的详细信息
   *
   * new userinfo(new token()->code(xxx)) 直接获取一份新的数据，但不总是有code让你传入
   * 三种情况需要用到code方式直接抓取最新信息：
   *  1、以前是base静默授权，现在需要充实详细信息，所以跳转URL，从code抓取
   *  2、用户第一次登录页面
   *  3、用户授权已经过期，或缓存的refresh_token已经丢失或失效
   *
   *
   * @param string $lang zh_CN | zh_TW | en
   */
  function __construct(token $token, string $lang='zh_CN'){

      foreach($this->info($token,$lang) as $k=>$v)
        $this->$k = $v;

  }


  private function check(\stdClass $json):\stdClass{
    if(isset($json->errcode,$json->errmsg)&&$json->errcode)
      throw new \RuntimeException($json->errmsg,$json->errcode);
    return $json;
  }


  function info(token $token, string $lang='zh_CN'):\stdClass{
    return $this->check(request::url($token::HOST.'/sns/userinfo')
        ->fetch(['access_token'=>$token->access_token,'openid'=>$token->openid,'lang'=>$lang])
        ->json());

  }

}
