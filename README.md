#### 简介：

此SDK为京品高科开放平台接口对接SDK。

#### 安装
```angular2html
composer require zimuoo/jpgkopen:^1.1.3
```


##### 示例

```
<?php
use Zimuoo\Jpgkopen\Http\Client;
try {
    include 'vendor/autoload.php';
    $config=[
        'APPID'=>'你的appid',
        'KEY'=>'你的key',
        'REDIS'=>[
            'scheme' => 'tcp', //连接协议
            'host'       => '127.0.0.1',//连接地址
            'port'       =>6379,//端口
            'password'   => '',//密码，无则留空
            'database'     => 1,//选择库

        ]
    ];
    $request=new Client($config);
    $data=$request::get('/api/GetMachineListByOperatorId.jpgk',['pageNo'=>1,'pageSize'=>10]);
    var_dump($data);
}catch (\Exception $e){
   //异常处理
}
```
