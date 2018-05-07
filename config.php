<?php namespace wx; // vim: se fdm=marker:

use http\request;

class config extends wx{

  /**
   * 在业务代码中才能决定使用哪个appid的token和ticket
   * 所以这组配置是特定于具体公众号的
   * 如果要做成通用的样子，必须额外添加判断url来源的逻辑，然后路由到各自适用的appid上
   * 如果还不行，则需要以appid来hash所有支持的token
   */
  function GET(string $appid, string $url):array{
    return (new config(new token($_ENV['APPID'],$_ENV['SECRET'])))->config($url);
  }


  final function config(string &$url):array{
    $arr = [
      'timestamp' => $time=time(),
      'noncestr' => $nonceStr=md5($time+$_SERVER['REQUEST_TIME_FLOAT']),
      'jsapi_ticket' => new ticket($this->token,'jsapi'),
      'url' => $url,
    ];
    sort($arr,SORT_STRING);
    return [
      'timestamp' => $time,
      'nonceStr' => $nonceStr,
      'signature' => sha1(http_build_query($arr)),
    ];
  }


  /**
   * 公众号跳转URL，这是微信菜单按钮类型view的网址，通过微信授权之后可以获取粉丝信息
   * @see https://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html
   *
   * 经过微信跳转，现在的请求页面变成了 redirect_uri?code=xxx&state=xxx
   * 此时需要拿code换取网页专用的access_token
   * 注意，每次跳转之后code都不同，而且code五分钟就失效，所以赶紧去换access_token
   *
   * @param string $scope 应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且， 即使在未关注的情况下，只要用户授权，也能获取其信息 ）
   * @param string $state 重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节
   */
  final static function url(string $appid, string $uri, string $state='', string $scope='snsapi_base'):string{
    return request::url('https://open.weixin.qq.com/connect/oauth2/authorize')->query([
        'appid'=>$appid,
        'redirect_uri'=>$uri,
        'scope'=>$scope,
        'state'=>$state
      ]).'#wechat_redirect';
  }

}
