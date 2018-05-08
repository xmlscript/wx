<?php namespace wx; // vim: se fdm=marker:

use http\request;
use tmp\cache;

/**
 * 1. 拼接一个特殊url，引导用户跳转到官方授权页面
 * 2. 如果用户同意授权，则又跳转到redirect_uri/?code=XXX&state=XXX
 * 3. 通过code换取网页授权的access_token，每次授权后code不同，而且只能用一次，而且5分钟后失效！
 * 4. 得到token的同时，也得到了openid，scope和refresh_token
 * 5. 如果scope是base则到此为止
 *
 * 6. 优先使用现成的token，其次考虑用refresh_token重刷新token，最后不得已，再次征求用户授权
 * 7. 继续使用新鲜token获取userinfo
 *
 * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140842
 */
final class token implements \ArrayAccess{

  function offsetExists($offset){
    return isset($this->$offset);
  }

  function offsetSet($offset, $value){
    $this->$offset = $value;
  }

  function offsetGet($offset){
    return $this->$offset;
  }

  function offsetUnset($offset){
    unset($this->$offset);
  }

  public const HOST = 'https://api.weixin.qq.com';
  private $appid;
  public $access_token, $expires_in, $refresh_token, $openid, $scope;


  private function __construct(string $appid, \stdClass $json){
    $this->appid = $appid;
    if($json)
      foreach($json as $k=>$v)
        $this->$k = $v;
  }


  /**
   * 请求token之前，必然要现在让网页作一次跳转以便获得code
   * 时机通常在公众号菜单的link按钮里设置
   */
  static function code(string $appid, string $secret, string $code):self{
    return new self($appid, $this->access_token($appid, $secret, $code));
  }


  /**
   * 尝试在缓存里找到尚未失效的token或refresh_token，以避免打扰用户频繁授权
   * 如果缓存未命中，需要URL跳转
   */
  static function openid(string $appid, string $openid):self{
    if($cache=(new cache($appid.__CLASS__.$openid, $appid, 2592000))[0])
      if($this->auth($cache->access_token, $cache->openid))
        return new self($appid, $cache);
      else
        return new self($appid, $this->refresh($appid, $cache->refresh_token));
    else
      throw new \RuntimeException;
  }


  function __toString():string{
    return $this->access_token;
  }


  private function check(\stdClass $json):\stdClass{
    if(isset($json->errcode,$json->errmsg)&&$json->errcode)
      throw new \RuntimeException($json->errmsg,$json->errcode);
    return $json;
  }


  private function write(\stdClass $json):\stdClass{
    return (new cache($this->appid.__CLASS__.$json->openid, $this->appid, 2592000))($json)[0];
  }


  /**
   * @todo 刷新之后，refresh_token还是原来那个吗？如果还是一样，那30天失效的判断就无故延长加时了
   */
  private function refresh(string $appid, string $refresh_token):\stdClass{
    return $this->write($this->check(request::url(self::HOST.'/sns/oauth2/refresh_token')
      ->fetch(['appid'=>$appid, 'grant_type'=>'refresh_token', 'refresh_token'=>$refresh_token])
      ->json()));
  }


  private function access_token(string $appid, string $secret, string $code):\stdClass{
    return $this->write($this->check(request::url(self::HOST.'/sns/oauth2/access_token')
      ->fetch(['appid'=>$appid,'secret'=>$secret,'code'=>$code,'grant_type'=>'authorization_code'])
      ->json()));
  }


  private function auth(string $access_token, string $openid):bool{
    return !request::url(self::HOST.'/sns/auth')
      ->fetch(['access_token'=>$access_token,'openid'=>$openid])
      ->json()
      ->errcode;
  }


  /**
   * 因为和其他逻辑没有关联互动，所以static
   * 为了获取code
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
  static function url(string $appid, string $uri, string $state='', string $scope='snsapi_base'):string{
    return 'https://open.weixin.qq.com/connect/oauth2/authorize?'.http_build_query([
      'appid'=>$appid,
      'redirect_uri'=>request::normalize($uri),
      'response_type'=>'code',
      'scope'=>$scope,
      'state'=>$state
    ]).'#wechat_redirect';
  }

}
