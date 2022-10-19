<?php
namespace Zimuoo\Jpgkopen;

final class Config
{


    const APPID = '';//你在开放平台的appid
    const API_HOST = 'open.jpgkcloud.com';
    const KEY = '';//你在开放平台配置的key
    const REDIS=[
        'host'=>'127.0.0.1',
        'port'=>'6379',
        'password'=>'',
        'select'=>'0'
    ];
    // 构造函数
    public function __construct()
    {
    }


}
