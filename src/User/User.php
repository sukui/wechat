<?php

namespace Thenbsp\Wechat\User;

use Thenbsp\Wechat\Bridge\Http;
use Doctrine\Common\Collections\ArrayCollection;
use Thenbsp\Wechat\Wechat\AccessToken;

class User
{
    /**
     * 获取用户信息
     */
    const USERINFO = 'https://api.weixin.qq.com/cgi-bin/user/info';

    /**
     * 批量获取用户
     */
    const BETCH = 'https://api.weixin.qq.com/cgi-bin/user/info/batchget';

    /**
     * 获取用户列表
     */
    const LISTS = 'https://api.weixin.qq.com/cgi-bin/user/get';

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
     * 查询用户列表
     */
    public function lists($nextOpenid = null)
    {
        $query = is_null($nextOpenid)
            ? array()
            : array('next_openid'=>$nextOpenid);
        $response = (yield Http::request('GET', static::LISTS)
            ->withAccessToken($this->accessToken)
            ->withQuery($query)
            ->send());

        if( $response['errcode'] != 0 ) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }

        yield $response;
    }

    /**
     * 获取用户信息
     */
    public function get($openid, $lang = 'zh_CN')
    {
        $query = array(
            'openid'    => $openid,
            'lang'      => $lang
        );

        $response = (yield Http::request('GET', static::USERINFO)
            ->withAccessToken($this->accessToken)
            ->withQuery($query)
            ->send());

        if( $response['errcode'] != 0 ) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }

        yield $response;
    }

    /**
     * 批量获取用户信息
     */
    public function getBetch(array $openid, $lang = 'zh_CN')
    {
        $body = array();

        foreach($openid as $key=>$value) {
            $body['user_list'][$key]['openid'] = $value;
            $body['user_list'][$key]['lang']   = $lang;
        }

        $response = (yield Http::request('POST', static::BETCH)
            ->withAccessToken($this->accessToken)
            ->withBody($body)
            ->send());

        if( $response['errcode'] != 0 ) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }
        return new ArrayCollection($response['user_info_list']);
    }
}
