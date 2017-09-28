<?php

namespace Thenbsp\Wechat\Wechat\Jsapi;

use Thenbsp\Wechat\Bridge\Http;
use Thenbsp\Wechat\Wechat\AccessToken;
use Zan\Framework\Store\Facade\Cache;

class Ticket
{

    /**
     * http://mp.weixin.qq.com/wiki/11/74ad127cc054f6b80759c40f77ec03db.html（附录 1）
     */
    const JSAPI_TICKET = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';

    /**`
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
     * 获取 AccessToken
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * 获取 Jsapi 票据（调用缓存，返回 String）
     */
    public function getTicketString()
    {
        $response = (yield $this->getTicketResponse());
        yield $response['ticket'];
    }

    /**
     * 获取 Jsapi 票据（不缓存，返回原始数据）
     */
    public function getTicketResponse()
    {
        $response = (yield Http::request('GET', static::JSAPI_TICKET)
            ->withAccessToken($this->accessToken)
            ->withQuery(array('type'=>'jsapi'))
            ->send());

        if( $response['errcode'] != 0 ) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }

        yield $response;
    }
}
