<?php


namespace Zimuoo\Jpgkopen\Http;
use Zimuoo\Jpgkopen\Http\Request;
use Zimuoo\Jpgkopen\Http\Response;
class Client
{
    protected $needHeader;
    protected $redisClient;
    protected $RedisClient;
    protected $appid;
    protected $key;
    protected $config;
    protected $error;
    public function __construct(array $config = [])
    {
        $this->config=$config;
        $this->appid=$config['APPID'];
        $this->key=$config['KEY'];
        $redisConfig=($this->config)['REDIS'];
        $GLOBALS['redisConfig']=$redisConfig;
        $this->RedisClient=Redis::connect($redisConfig);
        $this->getToken();
    }
    public static function get($url, $body = null)
    {
        if(empty($url))return ['code'=>400,'message'=>'API地址不能为空'];
        $request = new Request('GET', $url,$body);
        return self::sendRequest($request);
    }
    public static function post($url, $body=null)
    {
        if(empty($url))return ['code'=>400,'message'=>'API地址不能为空'];
        $request = new Request('POST', $url,$body);
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
    protected function getToken()
    {
        try {
            $token=$this->RedisClient->get('requestToken');
            if(!$token){
                $url='/api/getToken.jpgk';
                $request = new Request('GET', $url,['appid'=>$this->appid,'key'=>$this->key]);
                $tokenResponse=self::sendRequest($request);
                if($tokenResponse['code']==1001 && isset($tokenResponse['data']['token'])){
                    $token=$tokenResponse['data']['token'];
                    $this->RedisClient->set('requestToken',$token,7200);
                }else{
                    return $tokenResponse;
                    die;
                }
            }
        }catch (\Exception $e){
            return ['code'=>'JP404','message'=>$e->getMessage()];
        }

    }
    protected static function sendRequest($request)
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
        if (!empty($request->body)) {
            $options[CURLOPT_POSTFIELDS] =is_array($request->body)? http_build_query($request->body):$request->body;
        }

        if($request->method=='GET'){
            $request->url .= '?'.$options[CURLOPT_POSTFIELDS];
            $options[CURLOPT_URL]=$request->url;
        }
        // Handle open_basedir & safe mode
        if (!ini_get('safe_mode') && !ini_get('open_basedir')) {
            $options[CURLOPT_FOLLOWLOCATION] = true;
        }
        $headers = array("Content-type:application/json;","Accept:application/json");
        if(!empty($GLOBALS['redisConfig'])){
            $redis=Redis::connect($GLOBALS['redisConfig']);
            $token=$redis->get('requestToken');
            if($token){
                $headerToken=array("Authorization:bearer {$token};");
                foreach ($headerToken as $key => $val) {
                    array_push($headers, "$val");
                }
            }
        }
        $options[CURLOPT_HTTPHEADER] = $headers;
        try{
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
            curl_setopt_array($ch, $options);
            $result = curl_exec($ch);
            $t2 = microtime(true);
            $duration = round($t2 - $t1, 3);
            $ret = curl_errno($ch);
            if ($ret !== 0) {
            }
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = Header::parseRawText(substr($result, 0, $header_size));
            $body = substr($result, $header_size);
            curl_close($ch);
            return (new Response($code,$headers,$body))->parse();
        }catch (\Exception $e){
            return ['code'=>'JP404','message'=>$e->getMessage()];
        }
    }
}