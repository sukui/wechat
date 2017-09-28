<?php

namespace Thenbsp\Wechat\Payment\Qrcode;

use Thenbsp\Wechat\Bridge\Util;
use Thenbsp\Wechat\Bridge\Serializer;
use Thenbsp\Wechat\Payment\Unifiedorder;
use Doctrine\Common\Collections\ArrayCollection;

class ForeverCallback extends ArrayCollection
{
    /**
     * 成功
     */
    const SUCCESS = 'SUCCESS';

    /**
     * 失败
     */
    const FAIL = 'FAIL';

    /**
     * 构造方法
     * @param $content
     */
    public function __construct($content)
    {
        try {
            $options = Serializer::parse($content);
        } catch (\InvalidArgumentException $e) {
            $options = array();
        }

        parent::__construct($options);
    }

    /**
     * 错误响应
     * @param null $message
     * @return \Generator
     */
    public function fail($message = null)
    {
        $options = array('return_code' => static::FAIL);

        if( !is_null($message) ) {
            $options['return_msg'] = $message;
        }

        yield Serializer::xmlEncode($options);

    }

    /**
     * 成功响应
     * @param Unifiedorder $unifiedorder
     * @return \Generator
     */
    public function success(Unifiedorder $unifiedorder)
    {
        $unifiedorder->set('trade_type', 'NATIVE');

        $response = (yield $unifiedorder->getResponse());

        $options = array(
            'appid'         => $unifiedorder['appid'],
            'mch_id'        => $unifiedorder['mch_id'],
            'prepay_id'     => $response['prepay_id'],
            'nonce_str'     => Util::getRandomString(),
            'return_code'   => static::SUCCESS,
            'result_code'   => static::SUCCESS
        );

        // 按 ASCII 码排序
        ksort($options);

        $signature = urldecode(http_build_query($options));
        $signature = strtoupper(md5($signature.'&key='.$unifiedorder->getKey()));

        $options['sign'] = $signature;

        yield Serializer::xmlEncode($options);
    }

}
