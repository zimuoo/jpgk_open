<?php


namespace Zimuoo\Jpgkopen\Http;


class Response
{
    public $statusCode;
    /**
     * deprecated because of field names case-sensitive.
     * use $normalizedHeaders instead which field names are case-insensitive.
     * but be careful not to use $normalizedHeaders with `array_*` functions,
     * such as `array_key_exists`, `array_keys`, `array_values`.
     *
     * use `isset` instead of `array_key_exists`,
     * and should never use `array_key_exists` at http header.
     *
     * use `foreach` instead of `array_keys`, `array_values`.
     *
     * @deprecated
     */
    public $headers;
    public $normalizedHeaders;
    public $body;
    public $error;
    private $jsonData;
    public $duration;

    /** @var array Mapping of status codes to reason phrases */
    private static $statusTexts = array(
        1001=>"接口正常返回数据",
        1000=>"服务异常",
        1002=>"缺少token或者token异常",
        1003=>"秘钥key异常",
        1004=>"该秘钥无可用接口" ,
        1005=>"无权调用此接口",
        1006=>"点数不足需充值",
        1007=>"API不存在",
        1008=>"token过期",
        1009=>"token在黑名单中",
        1010=>"appid、key不匹配",
        1011=>"缺少参数" ,
        1012=>"请求方式不正确" ,
        4003=>"请求频率过快" ,
        8888=>"系统维护"
    );

    /**
     * @param int $code 状态码
     * @param double $duration 请求时长
     * @param array $headers 响应头部
     * @param string $body 响应内容
     * @param string $error 错误描述
     */
    public function __construct($code, $duration, array $headers = array(), $body = null, $error = null)
    {
        $this->statusCode = $code;
        $this->duration = $duration;
        $this->headers = array();
        $this->body = $body;
        $this->error = $error;
        $this->jsonData = null;

        if ($error !== null) {
            return;
        }

        foreach ($headers as $k => $vs) {
            if (is_array($vs)) {
                $this->headers[$k] = $vs[count($vs) - 1];
            } else {
                $this->headers[$k] = $vs;
            }
        }
        $this->normalizedHeaders = new Header($headers);

        if ($body === null) {
            if ($code >= 400) {
                $this->error = self::$statusTexts[$code];
            }
            return;
        }
        if (self::isJson($this->normalizedHeaders)) {
            try {
                $jsonData = self::bodyJson($body);
                if ($code >= 400) {
                    $this->error = $body;
                    if ($jsonData['error'] !== null) {
                        $this->error = $jsonData['error'];
                    }
                }
                $this->jsonData = $jsonData;
            } catch (\InvalidArgumentException $e) {
                $this->error = $body;
                if ($code >= 200 && $code < 300) {
                    $this->error = $e->getMessage();
                }
            }
        } elseif ($code >= 400) {
            $this->error = $body;
        }
        return;
    }

    public function json()
    {
        return $this->jsonData;
    }

    public function headers($normalized = false)
    {
        if ($normalized) {
            return $this->normalizedHeaders;
        }
        return $this->headers;
    }

    public function body()
    {
        return $this->body;
    }

    private static function bodyJson($body)
    {
        return json_decode((string) $body, true, 512);
    }

    public function xVia()
    {
        $via = $this->normalizedHeaders['X-Via'];
        if ($via === null) {
            $via = $this->normalizedHeaders['X-Px'];
        }
        if ($via === null) {
            $via = $this->normalizedHeaders['Fw-Via'];
        }
        return $via;
    }

    public function xLog()
    {
        return $this->normalizedHeaders['X-Log'];
    }

    public function xReqId()
    {
        return $this->normalizedHeaders['X-Reqid'];
    }

    public function ok()
    {
        return $this->statusCode >= 200 && $this->statusCode < 300 && $this->error === null;
    }

    public function needRetry()
    {
        $code = $this->statusCode;
        if ($code < 0 || ($code / 100 === 5 and $code !== 579) || $code === 996) {
            return true;
        }
    }

    private static function isJson($headers)
    {
        return isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'application/json') === 0;
    }
}
