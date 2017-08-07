<?php

namespace Thenbsp\Wechat\Wechat;

use Thenbsp\Wechat\Bridge\Http;
use Doctrine\Common\Collections\ArrayCollection;
use ZanPHP\Support\Singleton;

class AccessToken extends ArrayCollection
{
    /**
     * Cache Trait
     */
    use Singleton;

    private static $_appid;
    private static $_appsecret;

    private static $_token_info=[];

    /**
     * http://mp.weixin.qq.com/wiki/14/9f9c82c1af308e3b14ba9b973f99a8ba.html
     */
    const ACCESS_TOKEN = 'https://api.weixin.qq.com/cgi-bin/token';

    public static function init($appid,$appsecret){
        static::$_appid = $appid;
        static::$_appsecret = $appsecret;
    }

    /**
     * 获取 AccessToken（调用缓存，返回 String）
     */
    public function getTokenString()
    {
        if(empty(self::$_token_info)||(time()>=static::$_token_info['expires_time'])){
            $token = $this->getTokenResponse();
            $token['expires_time'] = time()+$token['expires_in'];
            self::$_token_info = $token;
        }
        return static::$_token_info['access_token'];
    }


    /**
     * 获取 AccessToken（不缓存，返回原始数据）
     */
    public function getTokenResponse()
    {
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

        return $response;
    }

    /**
     * 从缓存中清除
     */
    public function clearFromCache()
    {
        self::$_token_info = [];
        return true;
    }
}
