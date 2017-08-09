<?php

namespace Thenbsp\Wechat\Wechat;

use Thenbsp\Wechat\Bridge\Http;
use Doctrine\Common\Collections\ArrayCollection;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Store\Facade\Cache;
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

    public function init(){
        $config = Config::get('weixin');
        if(empty($config['appid'])||empty($config['appsecret'])){
            throw new \InvalidArgumentException("invalid wechat appid or appsecret");
        }
        self::$_appid = $config['appid'];
        self::$_appsecret = $config['appsecret'];
    }

    /**
     * 获取 AccessToken（调用缓存，返回 String）
     */
    public function getTokenString()
    {
        if(empty(self::$_appid)){
            $this->init();
        }
        $result = (yield Cache::get("weixin.token",$this->getCacheId()));
        if(!empty($result)&&$result=json_decode($result,true)){
            if(time()<$result['expires_time']){
                return $result['expires_time'];
            }
        }
        $token = (yield $this->getTokenResponse());
        $token['expires_time'] = time()+$token['expires_in']-10;
        $cache = json_encode($token);
        Cache::set('weixin.token',[self::$_appid],$cache);
        yield $token['access_token'];
    }


    /**
     * 获取 AccessToken（不缓存，返回原始数据）
     */
    public function getTokenResponse()
    {

        if(empty(self::$_appid) || empty(self::$_appsecret)){
            $this->init();
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

    /**
     * 从缓存中清除
     */
    public function clearFromCache()
    {
        yield Cache::del("weixin.token",$this->getCacheId());
    }
    /**
     * 获取缓存 ID
     */
    public function getCacheId()
    {
        return [self::$_appid];
    }
}
