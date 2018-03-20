<?php 

namespace ImgService\Drivers;

use OSS\OssClient;
use OSS\Core\OssException;
use ImgService\ImgAbstract\ImgServiceAbstract;

class OssDriver extends ImgServiceAbstract
{
    /**
     * 密钥key
     * @var
     */
    private $accessKeyId;

    /**
     * 密钥secret
     * @var string
     */
    private $accessKeySecret;

    /**
     * OSS数据中心访问域名
     * @var string
     */
    private $endpoint;

    /**
     * 存储bucket名
     * @var string
     */
    private $bucketName;

    /**
     * bucket对应的外网访问域
     * @var string
     */
    private $webhost;

    /**
     * 构造函数
     */
    function __construct($accessKeyId, $accessKeySecret, $endpoint)
    {
        $this->accessKeyId     = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
        $this->endpoint        = $endpoint;
        $this->ossClient       = $this->getOssClient();
    }

    /**
     * 根据Config配置，得到一个OssClient实例
     * @return OssClient 一个OssClient实例
     */
    public function getOssClient()
    {
        try {
            $ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint, false);
        } catch (OssException $e) {
            printf(__FUNCTION__ . "creating OssClient instance: FAILED\n");
            printf($e->getMessage() . "\n");
            return null;
        }
        return $ossClient;
    }

    /**
     * 切换bucket
     * @param string $bucket
     */
    function setBucket($bucket) 
    {
        $this->bucketName = $bucket;
        return $this;
    }

    /**
     * 获取bucket
     * @return string
     */
    function getBucket()
    {
        return $this->bucketName;
    }

    /**
     * 设置外网访问域
     * @param string $webhost
     */
    function setWebhost($webhost)
    {
        $this->webhost = $webhost;
        return $this;
    }

    /**
     * 获取外网访问域
     * @return string
     */
    function getWebhost()
    {
        return $this->webhost;
    }


    /**
     * 获取图片基本信息
     * @param  string $relativePath
     * @return object
     */
    function getInfo($relativePath)
    {
        $relativePath = $this->prefixOptimize($relativePath);
        // 获取图片信息
        $options = array(
            OssClient::OSS_PROCESS => "image/info", );
        $fileinfo  = $this->ossClient->getObject($this->bucketName, $relativePath, $options);
        $infoArray = json_decode($fileinfo, true);
        $this->height = $infoArray['ImageHeight']['value'];
        $this->width  = $infoArray['ImageWidth']['value'];
        $this->format = $infoArray['Format']['value'];
        $this->bytes  = $infoArray['FileSize']['value'];
        return $this;
    }

    /**
     * 判断图片是否存在
     * @param  string $imgpath
     * @return 
     */
    function isExist($relativePath)
    {
        $relativePath = $this->prefixOptimize($relativePath);
        return $this->ossClient->doesObjectExist($this->bucketName, $relativePath);
    }


    /**
     * 保存字符内容到文件
     * @param  string $filename 待保存的文件相对路径名
     * @param  stream $content  字符串流
     * @return
     */
    function putObjectStream($filename, $content)
    {
        $filename = $this->prefixOptimize($filename);
        try{
            $this->ossClient->putObject($this->bucketName, $filename, $content);
            return true;
        } catch(OssException $e) {
            // printf(__FUNCTION__ . ": FAILED\n");
            // printf($e->getMessage() . "\n");
            return false;
        }
    }


    /**
     * 上传文件到oss
     * @param  string $filename
     * @param  file   $file 
     * @return bool
     */
    function putFile($filename, $file)
    {
        $filename = $this->prefixOptimize($filename);
        try {
            $this->ossClient->uploadFile($this->bucketName, $filename, $file);
            return true;
        } catch (Exception $e) {
            // printf($e->getMessage() . "\n");
            return false;
        }
    }



    /**
     * 使用oss处理裁图请求
     * @param  string $relativePath
     * @param  string $size          
     * @return object
     */
    public function resize($relativePath, $size)
    {
        //去除相对图片地址前面的反斜杠路径
        $relativePath = $this->prefixOptimize($relativePath);
        $raw_url = $this->webhost.'/'.$relativePath;
        //判断图片是否存在
        $doesObjectExist = $this->ossClient->doesObjectExist($this->bucketName, $relativePath);
        if (!$doesObjectExist) {
            return false;
        }
        // 获取图片信息
        $imginfo = $this->getInfo($relativePath);

        preg_match('/^_/', $size, $match);
        if (!empty($match)) {
            $height    = preg_replace('/^_/', '', $size);
            $height    = intval($height);
            $width     = intval($this->width*$height/$this->height);
            $setheight = $height;
            $setwidth  = NULL;
        }else {
            $wh_arr    = explode('_', $size);
            $width     = intval($wh_arr[0]);
            $height    = isset($wh_arr[1]) && $wh_arr[1] ? intval($wh_arr[1]) : intval($width*$this->height/$this->width);
            $setwidth  = $width;
            $setheight = isset($wh_arr[1]) && $wh_arr[1] ? intval($wh_arr[1]) : NULL;
        }
        //非法尺寸
        if (!$width || !$height) {
            return false;
        }
        //原始宽高比
        $processfunc = '?x-oss-process=image/resize';
        if ($setwidth && $setheight) {
            $processfunc .= ',m_fill';
        }else {
            $processfunc .= ',m_lfit';
        }
        if ($setwidth) {
            $processfunc .= ',w_'.$setwidth;
        }
        if ($setheight) {
            $processfunc .= ',h_'.$setheight;
        }
        $url = $raw_url.$processfunc;
        //返回图片尺寸对象
        $ret = array(
            'url'    => $url,
            'height' => (string) $height,
            'width'  => (string) $width,
        );
        return $ret;
    }
}