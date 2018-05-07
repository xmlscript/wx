<?php namespace mp; // vim: se fdm=marker:

class card{

  /**
   * jssdk的wx.chooseCard()需要用到的部分参数
   * 另外由于历史原因，卡券的JS接口先于JSSDK出现，当时的JSAPI并没有鉴权体系，所以在卡券的签名里也加上了appsecret/api_ticket这些身份信息，希望开发者理解。
   *
   * @see https://mp.weixin.qq.com/debug/cgi-bin/sandbox?t=cardsign
   * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421141115
   */
  function chooseCard(string $id=null, string $code=null, string $openid=null, string $balance=null):array{
    return [
      //'shopId' => '', #用于筛选出拉起带有指定location_list(shopID)的卡券列表
      //'cardType' => '', #用于拉起指定卡券类型的卡券列表。为空时，默认拉起所有卡券的列表
      'cardId' => $id, #用于拉起指定cardId的卡券列表，为空时，默认拉起所有卡券的列表
      'timestamp' => $timestamp=time(),
      'nonceStr' => $nonceStr=md5($time+$_SERVER['REQUEST_TIME_FLOAT']),
      'signType' => 'SHA1',
      'cardSign' => $this->signature(
        new ticket($this->token,'wx_card'),
        $timestamp,
        $nonceStr,
        $id,
        $code,
        $openid,
        $balance
      )
    ];
  }

  private function signature(string ...$arr):string{
    sort($arr);
    return sha1(join($arr));
  }

}
