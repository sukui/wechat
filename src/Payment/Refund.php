<?php

namespace Thenbsp\Wechat\Payment;

use Thenbsp\Wechat\Bridge\Util;
use Thenbsp\Wechat\Bridge\Http;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Refund extends ArrayCollection
{
    /**
     * https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_1
     */
    const REFUND = 'https://api.mch.weixin.qq.com/secapi/pay/refund';

    /**
     * 商户 KEY
     */
    protected $key;

    /**
     * 全部选项（不包括 sign）
     */
    protected $defined = array(
        'appid','mch_id','nonce_str','transaction_id','out_trade_no','out_refund_no',
        'total_fee','refund_fee','refund_fee_type','refund_desc','refund_account'
    );

    /**
     * 必填选项（不包括 sign）
     */
    protected $required = array(
        'appid', 'mch_id', 'nonce_str', 'body', 'out_trade_no','out_refund_no',
        'total_fee', 'refund_fee'
    );

    /**
     * 构造方法
     * @param $appid
     * @param $mchid
     * @param $key
     */
    public function __construct($appid, $mchid, $key)
    {
        $this->key = $key;

        $this->set('appid', $appid);
        $this->set('mch_id', $mchid);
    }

    /**
     * 获取商户 Key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * 调置 SSL 证书
     * @param $sslCert
     * @param $sslKey
     */
    public function setSSLCert($sslCert, $sslKey)
    {
        if( !file_exists($sslCert) ) {
            throw new \InvalidArgumentException(sprintf('File "%s" Not Found', $sslCert));
        }

        if( !file_exists($sslKey) ) {
            throw new \InvalidArgumentException(sprintf('File "%s" Not Found', $sslKey));
        }

        $this->sslCert = $sslCert;
        $this->sslKey  = $sslKey;
    }

    /**
     * 获取响应结果
     */
    public function getResponse()
    {
        $options = $this->resolveOptions();

        // 按 ASCII 码排序
        ksort($options);

        $signature = urldecode(http_build_query($options));
        $signature = strtoupper(md5($signature.'&key='.$this->key));

        $options['sign'] = $signature;

        $response = (yield Http::request('POST', static::REFUND)
            ->withSSLCert($this->sslCert, $this->sslKey)
            ->withXmlBody($options)
            ->send());

        if( $response['return_code'] === 'FAIL' ) {
            throw new \Exception($response['return_msg']);
        }

        if( $response['result_code'] === 'FAIL' ) {
            throw new \Exception($response['err_code_des']);
        }

        yield $response;
    }

    /**
     * 合并和校验参数
     */
    public function resolveOptions()
    {

        $defaults = array(
            'nonce_str'         => Util::getRandomString(),
        );

        $resolver = new OptionsResolver();
        $resolver
            ->setDefined($this->defined)
            ->setRequired($this->required)
            ->setDefaults($defaults);

        return $resolver->resolve($this->toArray());
    }
}
