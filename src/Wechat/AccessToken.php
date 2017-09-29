<?php

namespace Thenbsp\Wechat\Wechat;

use Thenbsp\Wechat\Bridge\Http;
use Doctrine\Common\Collections\ArrayCollection;
use ZanPHP\Support\Singleton;

class AccessToken extends ArrayCollection
{
    use Singleton;

    public static $_appid;
    public static $_appsecret;

    /**
     * http://mp.weixin.qq.com/wiki/14/9f9c82c1af308e3b14ba9b973f99a8ba.html
     */
    const ACCESS_TOKEN = 'https://api.weixin.qq.com/cgi-bin/token';

    public function init($appid, $appsecret){
        if(empty($appid)||empty($appsecret)){
            throw new \InvalidArgumentException("invalid wechat appid or appsecret");
        }
        self::$_appid = $appid;
        self::$_appsecret = $appsecret;
    }


    /**
     * 获取 AccessToken（不缓存，返回原始数据）
     */
    public function getTokenResponse()
    {
        if(empty(self::$_appid)||empty(self::$_appsecret)){
            throw new \InvalidArgumentException("invalid wechat appid or appsecret");
        }

        $query = array(
            'grant_type'    => 'client_credential',
            'appid'         => self::$_appid,
            'secret'        => self::$_appsecret
        );

        $response = (yield Http::request('GET', static::ACCESS_TOKEN)
            ->withQuery($query)
            ->send());

        if( $response->containsKey('errcode') ) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }

        yield $response->toArray();
    }

}
