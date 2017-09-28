<?php

namespace Thenbsp\Wechat\User;

use Thenbsp\Wechat\Bridge\Http;
use Thenbsp\Wechat\Wechat\AccessToken;
use Doctrine\Common\Collections\ArrayCollection;

class Group
{
    const SELECT            = 'https://api.weixin.qq.com/cgi-bin/groups/get';
    const CREATE            = 'https://api.weixin.qq.com/cgi-bin/groups/create';
    const UPDAET            = 'https://api.weixin.qq.com/cgi-bin/groups/update';
    const DELETE            = 'https://api.weixin.qq.com/cgi-bin/groups/delete';
    const QUERY_USER_GROUP  = 'https://api.weixin.qq.com/cgi-bin/groups/getid';
    const UPDATE_USER_GROUP = 'https://api.weixin.qq.com/cgi-bin/groups/members/update';
    const BETCH_UPDATE_USER_GROUP = 'https://api.weixin.qq.com/cgi-bin/groups/members/batchupdate';

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
     * 查询全部分组
     */
    public function query()
    {
        $response = (yield Http::request('GET', static::SELECT)
            ->withAccessToken($this->accessToken)
            ->send());

        if( $response['errcode'] != 0 ) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }

        yield new ArrayCollection($response['groups']);
    }

    /**
     * 创建新分组
     * @param $name
     * @return \Generator
     * @throws \Exception
     */
    public function create($name)
    {
        $body = array(
            'group' => array('name'=>$name)
        );

        $response = (yield Http::request('POST', static::CREATE)
            ->withAccessToken($this->accessToken)
            ->withBody($body)
            ->send());

        if( $response['errcode'] != 0 ) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }

        yield new ArrayCollection($response['group']);
    }

    /**
     * 修改分组名称
     * @param $id
     * @param $newName
     * @return \Generator
     * @throws \Exception
     */
    public function update($id, $newName)
    {
        $body = array(
            'group' => array(
                'id'    => $id,
                'name'  => $newName
            )
        );

        $response = (yield Http::request('POST', static::UPDAET)
            ->withAccessToken($this->accessToken)
            ->withBody($body)
            ->send());

        if( $response['errcode'] != 0 ) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }

        yield true;
    }

    /**
     * 删除分组
     * @param $id
     * @return \Generator
     * @throws \Exception
     */
    public function delete($id)
    {
        $body = array(
            'group' => array('id'=>$id)
        );

        $response = (yield Http::request('POST', static::DELETE)
            ->withAccessToken($this->accessToken)
            ->withBody($body)
            ->send());

        if( $response['errcode'] != 0 ) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }

        yield true;
    }

    /**
     * 查询指定用户所在分组
     * @param $openid
     * @return \Generator
     * @throws \Exception
     */
    public function queryUserGroup($openid)
    {
        $body = array('openid'=>$openid);

        $response = (yield Http::request('POST', static::QUERY_USER_GROUP)
            ->withAccessToken($this->accessToken)
            ->withBody($body)
            ->send());

        if( $response['errcode'] != 0 ) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }

        yield $response['groupid'];
    }

    /**
     * 移动用户分组
     * @param $openid
     * @param $newId
     * @return \Generator
     * @throws \Exception
     */
    public function updateUserGroup($openid, $newId)
    {
        $key = is_array($openid)
            ? 'openid_list'
            : 'openid';

        $api = is_array($openid)
            ? static::BETCH_UPDATE_USER_GROUP
            : static::UPDATE_USER_GROUP;

        $body = array($key=>$openid, 'to_groupid'=>$newId);

        $response = (yield Http::request('POST', $api)
            ->withAccessToken($this->accessToken)
            ->withBody($body)
            ->send());

        if( $response['errcode'] != 0 ) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }

        yield true;
    }
}
