<?php

namespace Thenbsp\Wechat\Bridge;


use Thenbsp\Wechat\Bridge\Serializer;
use Doctrine\Common\Collections\ArrayCollection;
use Zan\Framework\Network\Common\HttpClient;

class Http
{
    /**
     * Request Url
     */
    protected $uri;

    /**
     * Request Method
     */
    protected $method;

    /**
     * Request Body
     */
    protected $body;

    /**
     * Request Query
     */
    protected $query = array();

    /**
     * Query With AccessToken
     */
    protected $accessToken;

    /**
     * SSL 证书
     */
    protected $sslCert;
    protected $sslKey;

    /**
     * initialize
     */
    public function __construct($method, $uri)
    {
        $this->uri      = $uri;
        $this->method   = strtoupper($method);
    }

    /**
     * Create Client Factory
     */
    public static function request($method, $uri)
    {
        return new static($method, $uri);
    }

    /**
     * Request Query
     */
    public function withQuery(array $query)
    {
        $this->query = array_merge($this->query, $query);

        return $this;
    }

    /**
     * Request Json Body
     */
    public function withBody(array $body)
    {
        $this->body = Serializer::jsonEncode($body);

        return $this;
    }

    /**
     * Request Xml Body
     */
    public function withXmlBody(array $body)
    {
        $this->body = Serializer::xmlEncode($body);

        return $this;
    }

    /**
     * Query With AccessToken
     */
    public function withAccessToken($accessToken)
    {
        $this->query['access_token'] = $accessToken;
        return $this;
    }

    /**
     * Request SSL Cert
     */
    public function withSSLCert($sslCert, $sslKey)
    {
        $this->sslCert = $sslCert;
        $this->sslKey  = $sslKey;

        return $this;
    }

    /**
     * Send Request
     */
    public function send($asArray = true,$timeout=3000)
    {
        $options = array();

        // body
        if( !empty($this->body) ) {
            $options['body'] = $this->body;
        }

        $client = HttpClient::newInstance();

        // ssl cert
        if( $this->sslCert && $this->sslKey ) {
            $options['ssl_cert_file']    = $this->sslCert;
            $options['ssl_key_file'] = $this->sslKey;
            $client->set($options);
        }


        if($this->method == 'GET'){
            $response = (yield $client->getByURL($this->uri,$this->query,$timeout));
        }else{
            $response = (yield $client->postByURL($this->uri,$this->body,$timeout));
        }

        $contents = $response->getBody();

        if( !$asArray ) {
            yield $contents;
            return;
        }

        $array = Serializer::parse($contents);

        yield new ArrayCollection($array);
    }
}
