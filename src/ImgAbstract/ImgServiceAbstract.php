<?php 

namespace ImgService\ImgAbstract;

use ImgService\ImgInterface\ImgServiceInterface;

abstract class ImgServiceAbstract implements ImgServiceInterface
{
    /**
     * 图片高
     * @var integer
     */
    private $height;

    /**
     * 图片宽
     * @var integer
     */
    private $width;

    /**
     * 图片大小
     * @var integer
     */
    private $bytes;

    /**
     * 图片格式 png|jpg|gif|...
     * @var string
     */
    private $format;

    /**
     * 相对路径处理成oss识别的样式，前缀不带斜杠
     * @param  $relativePath 待处理相对路径
     * @return string
     */
    function prefixOptimize($relativePath, $prefix = '')
    {
        return preg_replace('/^\//', $prefix, $relativePath);
    }
}