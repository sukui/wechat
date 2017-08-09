<?php

namespace Thenbsp\Wechat\Menu;

use Thenbsp\Wechat\Bridge\Http;
use Thenbsp\Wechat\Wechat\AccessToken;

class Delete
{
    /**
     * 接口地址
     */
    const DELETE_URL = 'https://api.weixin.qq.com/cgi-bin/menu/delete';

    /**
     * Thenbsp\Wechat\Wechat\AccessToken
     */
    protected $accessToken;

    /**
     * 构造方法
     */
    public function __construct(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * 获取响应
     */
    public function doDelete()
    {
        $token = (yield $this->accessToken->getTokenString());
        $response = (yield Http::request('GET', static::DELETE_URL)
            ->withAccessToken($token)
            ->send());

        if( $response['errcode'] != 0 ) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }

        yield true;
    }
}
