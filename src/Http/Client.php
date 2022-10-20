<?php


namespace Zimuoo\Jpgkopen\Http;
use Zimuoo\Jpgkopen\Http\Request;

class Client
{
    protected $needHeader;
    protected $redisClient;
    protected $redis;
    protected $appid;
    protected $key;
    
    public function __construct(array $config = [])
    {
        $redisConfig=$config['REDIS'];
        $this->redis=$this->connect($redisConfig);
        $this->appid=$config['APPID'];
        $this->key=$config['KEY'];
        $this->getToekn();
    }
    public function connect(array $redisConfig)
    {
        $connection = new \Redis();
        $ret = $connection->connect($redisConfig['host'], $redisConfig['port']);
        if ($ret === false) {
            throw new \RuntimeException(sprintf('Failed to connect Redis server: [%s] %s', $connection->errCode, $connection->errMsg));
        }
        if (isset($redisConfig['password'])) {
            $redisConfig['password'] = (string)$redisConfig['password'];
            if ($redisConfig['password'] !== '') {
                $connection->auth($redisConfig['password']);
            }
        }
        if (isset($redisConfig['select'])) {
            $connection->select($redisConfig['select']);
        }
        return $connection;
    }

    public static function get($url, array $headers = array())
    {
        $request = new Request('GET', $url, $headers);
        return self::sendRequest($request);
    }

    public static function delete($url, array $headers = array())
    {
        $request = new Request('DELETE', $url, $headers);
        return self::sendRequest($request);
    }

    public static function post($url, $body, array $headers = array())
    {
        $request = new Request('POST', $url, $headers, $body);
        return self::sendRequest($request);
    }

    public static function PUT($url, $body, array $headers = array())
    {
        $request = new Request('PUT', $url, $headers, $body);
        return self::sendRequest($request);
    }
    private static function userAgent()
    {

        $systemInfo = php_uname("s");
        $machineInfo = php_uname("m");
        $envInfo = "($systemInfo/$machineInfo)";
        $phpVer = phpversion();

        $ua = "$envInfo PHP/$phpVer";
        return $ua;
    }
    protected function getToekn()
    {
        $url='http://'.Config::API_HOST.'/api/getToken.jpgk';

        $request = new Request('GET', $url, [],['appid'=>Config::APPID,'key'=>Config::KEY]);

        $tokenResponse=self::sendRequest($request);

       // $this->needHeader=[]; //设置请求header
        var_dump($tokenResponse);
    }
    public static function sendRequest($request)
    {
        $t1 = microtime(true);
        $ch = curl_init();
        $options = array(
            CURLOPT_USERAGENT => self::userAgent(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => false,
            CURLOPT_CUSTOMREQUEST => $request->method,
            CURLOPT_URL => $request->url,
        );
        // Handle open_basedir & safe mode
        if (!ini_get('safe_mode') && !ini_get('open_basedir')) {
            $options[CURLOPT_FOLLOWLOCATION] = true;
        }
        if (!empty($request->headers)) {
            $headers = array();
            foreach ($request->headers as $key => $val) {
                array_push($headers, "$key: $val");
            }
            $options[CURLOPT_HTTPHEADER] = $headers;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        if (!empty($request->body)) {
            $options[CURLOPT_POSTFIELDS] = $request->body;
        }
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $t2 = microtime(true);
        $duration = round($t2 - $t1, 3);
        $ret = curl_errno($ch);
        if ($ret !== 0) {
            $r = new Response(-1, $duration, array(), null, curl_error($ch));
            curl_close($ch);
            return $r;
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = Header::parseRawText(substr($result, 0, $header_size));
        $body = substr($result, $header_size);
        curl_close($ch);
        return new Response($code, $duration, $headers, $body, null);
    }
}