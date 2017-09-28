<?php

namespace Thenbsp\Wechat\Wechat;

use Thenbsp\Wechat\Bridge\Http;
use Thenbsp\Wechat\Wechat\AccessToken;

class ServerIp
{

    /**
     * http://mp.weixin.qq.com/wiki/4/41ef0843d6e108cf6b5649480207561c.html
     */
    const SERVER_IP = 'https://api.weixin.qq.com/cgi-bin/getcallbackip';

    /**
     * Thenbsp\Wechat\Wechat\AccessToken
     */
    protected $accessToken;

    protected static $_ips=[];

    /**
     * 构造方法
     */
    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * 获取微信服务器 IP（默认缓存 1 天）
     */
    public function getIps($cacheLifeTime = 86400)
    {
        if(empty(self::$_ips) || (time()>=self::$_ips['expires_time'])){
            $response = (yield Http::request('GET', static::SERVER_IP)
                ->withAccessToken($this->accessToken)
                ->send());

            if( $response->containsKey('errcode') ) {
                throw new \Exception($response['errmsg'], $response['errcode']);
            }
            $data = $response->toArray();
            if($cacheLifeTime){
                $data['expires_time'] = time()+$cacheLifeTime;
            }
            self::$_ips = $data;
        }
        yield self::$_ips['ip_list'];
    }
}
