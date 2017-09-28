<?php

namespace Thenbsp\Wechat\Wechat;

use Thenbsp\Wechat\Bridge\Http;
use Thenbsp\Wechat\Wechat\AccessToken;

class ShortUrl
{
    /**
     * http://mp.weixin.qq.com/wiki/6/856aaeb492026466277ea39233dc23ee.html
     */
    const SHORT_URL = 'https://api.weixin.qq.com/cgi-bin/shorturl';

    /**
     * Thenbsp\Wechat\Wechat\AccessToken
     */
    protected $accessToken;

    /**
     * 构造方法
     */
    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * 获取短链接
     */
    public function toShort($longUrl)
    {

        $body = array(
            'action'    => 'long2short',
            'long_url'  =>  $longUrl
        );

        $response = (yield Http::request('POST', static::SHORT_URL)
            ->withAccessToken($this->accessToken)
            ->withBody($body)
            ->send());

        if( $response['errcode'] != 0 ) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }

        yield $response['short_url'];
    }
}
