<?php namespace wx; // vim: se fdm=marker:

final class card{

  /**
   * @see https://mp.weixin.qq.com/debug/cgi-bin/sandbox?t=cardsign
   * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421141115
   */
  function __construct(token $token, string ...$more){
    $this->timestamp = $_SERVER['REQUEST_TIME'];
    $this->nonceStr = md5($_SERVER['REQUEST_TIME_FLOAT']);
    $this->signType = 'SHA1';
    $this->cardSign = $this->signature(new ticket($token,'wx_card'), $this->timestamp, $this->nonceStr, ...$more);
  }

  private function signature(string ...$arr):string{
    sort($arr);
    return sha1(join($arr));
  }

  function __toString():string{
    return json_encode($this);
  }

}
