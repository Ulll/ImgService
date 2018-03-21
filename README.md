# ImgService

## 说明

支持随时切换Qiniu、OSS、Local三种模式的图片读取、上传、裁剪服务

## 用法


```
composer require "papajo/imgservice"
```

```php
require __DIR__ . '/vendor/autoload.php';

use ImgService\ImgService;
use ImgService\Drivers\OssDriver;

$driver = new OssDriver('*************', '*****************', 'oss-cn-qingdao.aliyuncs.com');
$driver->setBucket('test')->setWebhost('http://test.oss-cn-qingdao.aliyuncs.com');

//$driver = new QiniuDriver('accessKey', 'accessSecret');
//$driver->setBucket('test');

//$driver = new LocalDriver('storePath');
//$driver->setBucket('test');

$serv = new ImgService($oss);
$filename = '/test/1.png';
$content  = file_get_contents('/Users/xy/Desktop/1.png');
$a = $serv->putObjectStream($filename, $content);
$b = $serv->reszieArray($filename, ["200","100"]);
$c = $serv->getInfo($filename);
var_dump($a,$b,$c);exit;
```


#### Response

```bash
bool(true)
array(2) {
  [200]=>
  array(3) {
    ["url"]=>
    string(97) "http://test.oss-cn-qingdao.aliyuncs.com/test/1.png?x-oss-process=image/resize,m_lfit,w_200"
    ["height"]=>
    string(3) "200"
    ["width"]=>
    string(3) "200"
  }
  [100]=>
  array(3) {
    ["url"]=>
    string(97) "http://test.oss-cn-qingdao.aliyuncs.com/test/1.png?x-oss-process=image/resize,m_lfit,w_100"
    ["height"]=>
    string(3) "100"
    ["width"]=>
    string(3) "100"
  }
}
array(4) {
  ["height"]=>
  string(3) "300"
  ["width"]=>
  string(3) "300"
  ["ext"]=>
  string(3) "png"
  ["bytes"]=>
  string(5) "79201"
}
```

