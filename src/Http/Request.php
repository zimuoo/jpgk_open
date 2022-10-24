<?php


namespace Zimuoo\Jpgkopen\Http;
class Request
{
    public $url;
    public $headers;
    public $body;
    public $method;
    protected $apiHost='open.jpgkcloud.com';

    public function __construct($method, $url,  $body = null,array $headers = array())
    {
        $this->method = strtoupper($method);
        $this->url = 'http://'.$this->apiHost.$url;
        $this->headers = $headers;
        $this->body = $body;

    }
}