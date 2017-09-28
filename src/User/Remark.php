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
     * @param $accessToken
     */
    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * 设置/更新用户备注
     * @param $openid
     * @param $remark
     * @return \Generator
     * @throws \Exception
     */
    public function update($openid, $remark)
    {
        $body = array(
            'openid'    => $openid,
            'remark'    => $remark
        );

        $response = (yield Http::request('POST', static::REMARK)
            ->withAccessToken($this->accessToken)
            ->withBody($body)
            ->send());

        if( $response['errcode'] != 0 ) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }

        yield $response;
    }
}
