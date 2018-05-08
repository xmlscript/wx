<?php namespace mp; // vim: se fdm=marker:

class card{

  public $timestamp, $nonceStr, $signType, $cardSign;

  /**
   * jssdk的wx.chooseCard()需要用到的部分参数
   * 另外由于历史原因，卡券的JS接口先于JSSDK出现，当时的JSAPI并没有鉴权体系，所以在卡券的签名里也加上了appsecret/api_ticket这些身份信息，希望开发者理解。
   *
   * @see https://mp.weixin.qq.com/debug/cgi-bin/sandbox?t=cardsign
   * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421141115
   */
  function __construct(token $token, string $id=null, string $code=null, string $openid=null, string $balance=null){
    $this->timestamp = time();
    $this->nonceStr = md5($this->timestamp+$_SERVER['REQUEST_TIME_FLOAT']);
    $this->signType = 'SHA1';
    $this->cardSign = $this->signature(
      new ticket($token,'wx_card'),
      $this->timestamp,
      $this->nonceStr,
      $id,
      $code,
      $openid,
      $balance
    );

  }

  private function signature(string ...$arr):string{
    sort($arr);
    return sha1(join($arr));
  }

}
