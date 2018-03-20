<?php

namespace ImgService;

use ImgService\Drivers\OssDriver;
use ImgService\Drivers\QiniuDriver;
use ImgService\Drivers\LocalDriver;
use ImgService\ImgAbstract\ImgServiceAbstract;
use Tmtpost\TmtException;


class ImgService
{
    /**
     * 构造函数
     * @param ImgServiceAbstract|null $serverInstance
     */
    function __construct(ImgServiceAbstract $serverInstance = null)
    {
        $this->serv = $serverInstance;
    }

    /**
     * 设置图片处理服务实例
     * @param ImgServiceAbstract|null $serverInstance
     */
    function setService(ImgServiceAbstract $serverInstance = null)
    {
        $this->serv = $serverInstance;
        return $this;
    }

    /**
     * 获取图片处理的服务
     * @return ImgServiceAbstract object
     */
    function getService()
    {
        return $this->serv;
    }


    function putObjectStream($filename, $content)
    {
        return $this->serv->putObjectStream($filename, $content);
    }


    /**
     * 裁剪图片,多个尺寸
     * @param  图片路径 $path  string
     * @param  目标尺寸 $sizes array
     * @return array|false
     */
    function reszieArray($relativePath, array $sizes)
    {
        $ret = [];
        foreach ($sizes as $k=>$size) {
            $thumb = $this->serv->resize($relativePath, $size);
            $ret[$size] = $thumb ? $thumb : [];
        }
        return $ret;
    }

    /**
     * 获取图片基本信息
     * @param  string $relativePath
     * @return array
     */
    function getInfo($relativePath)
    {
        $fileObj = $this->serv->getInfo($relativePath);
        $ret = array(
            'height' => $fileObj->height,
            'width'  => $fileObj->width,
            'ext'    => $fileObj->format,
            'bytes'  => $fileObj->bytes
        );
        return $ret;
    }
}
