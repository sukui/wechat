<?php

namespace Thenbsp\Wechat\Wechat;

use Thenbsp\Wechat\Bridge\Util;
use Thenbsp\Wechat\Bridge\Serializer;
use Thenbsp\Wechat\Wechat\Jsapi\Ticket;
use Thenbsp\Wechat\Wechat\AccessToken;

class Jsapi
{

    /**
     * Thenbsp\Wechat\Wechat\AccessToken
     */
    protected $accessToken;

    /**
     * 是否开起调试
     */
    protected $debug = false;

    /**
     * 接口列表
     */
    protected $api = array();

    /**
     * 全部接口
     */
    protected $apiValids = array(
        'onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo',
        'onMenuShareQZone', 'startRecord', 'stopRecord', 'onVoiceRecordEnd', 'playVoice',
        'pauseVoice', 'stopVoice', 'onVoicePlayEnd', 'uploadVoice', 'downloadVoice', 'chooseImage',
        'previewImage', 'uploadImage', 'downloadImage', 'translateVoice', 'getNetworkType',
        'openLocation', 'getLocation', 'hideOptionMenu', 'showOptionMenu', 'hideMenuItems',
        'showMenuItems', 'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem', 'closeWindow',
        'scanQRCode', 'chooseWXPay', 'openProductSpecificView', 'addCard', 'chooseCard', 'openCard'
    );

    /**
     * 构造方法
     * @param $token
     */
    public function __construct($token)
    {
        $this->accessToken = $token;
    }

    /**
     * 注入接口
     */
    public function addApi($apis)
    {
        if( is_array($apis) ) {
            foreach( $apis AS $api ) {
                $this->addApi($api);
            }
        }

        $apiName = (string) $apis;

        if( !in_array($apiName, $this->apiValids, true) ) {
            throw new \InvalidArgumentException(sprintf('Invalid Api: %s', $apiName));
        }

        array_push($this->api, $apiName);

        return $this;
    }

    /**
     * 启用调试模式
     */
    public function enableDebug()
    {
        $this->debug = true;

        return $this;
    }

    /**
     * 获取配置文件
     */
    public function getConfig($asArray = false)
    {
        $ticket = new Ticket($this->accessToken);

        $ticketStr = (yield $ticket->getTicketString());

        $options = array(
            'jsapi_ticket'  => $ticketStr,
            'timestamp'     => Util::getTimestamp(),
            'url'           => Util::getCurrentUrl(),
            'noncestr'      => Util::getRandomString(),
        );

        ksort($options);

        $signature = sha1(urldecode(http_build_query($options)));
        $configure = array(
            'appId'     => $this->accessToken['appid'],
            'nonceStr'  => $options['noncestr'],
            'timestamp' => $options['timestamp'],
            'signature' => $signature,
            'jsApiList' => $this->api,
            'debug'     => (bool) $this->debug
        );

        yield $asArray ? $configure : Serializer::jsonEncode($configure);
    }

}
