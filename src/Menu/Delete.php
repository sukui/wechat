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
    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * 获取响应
     */
    public function doDelete()
    {
        $response = (yield Http::request('GET', static::DELETE_URL)
            ->withAccessToken($this->accessToken)
            ->send());

        if( $response['errcode'] != 0 ) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }

        yield true;
    }
}
