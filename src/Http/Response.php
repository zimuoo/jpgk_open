<?php


namespace Zimuoo\Jpgkopen\Http;


class Response
{
    private static $statusTexts = array(
        400=>"Bad Request",
        401=>'Unauthorized',
        403=>'forbidden',
        404=>"not found",
        405=>'method not allowed',
        406=>'Not Acceptable',
        408=>"Request Time-out",
        500=>"Internal Server Error",
        501=>"Not Implemented",
        502=>"Bad Gateway",
        503=>"Service Unavailable",
        504=>"Gateway Time-out",
        505=>"HTTP Version not supported",

    );
    private $res;

    public function __construct($code,$headers,$body=null)
    {
        $this->res=$body;
        if ($body === null) {
            if ($code >= 400) {
                $this->error = isset(self::$statusTexts[$code])??"未知错误~：$code";
            }
            return ['code'=>$code,'message'=>$this->error];
        }

    }
    public function parse()
    {
        $jsonData = self::bodyJson($this->res);
        return $jsonData;
    }


    private static function bodyJson($body)
    {
        return json_decode((string) $body, true);
    }

}