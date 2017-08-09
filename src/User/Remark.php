<?php

namespace Thenbsp\Wechat\User;

use Thenbsp\Wechat\Bridge\Http;
use Thenbsp\Wechat\Wechat\AccessToken;

class Remark
{
    /**
     * 设置用户备注名
     */
    const REMARK = 'https://api.weixin.qq.com/cgi-bin/user/info/updateremark';

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
     * 设置/更新用户备注
     */
    public function update($openid, $remark)
    {
        $body = array(
            'openid'    => $openid,
            'remark'    => $remark
        );

        $token = (yield $this->accessToken->getTokenString());

        $response = (yield Http::request('POST', static::REMARK)
            ->withAccessToken($token)
            ->withBody($body)
            ->send());

        if( $response['errcode'] != 0 ) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }

        yield $response;
    }
}
