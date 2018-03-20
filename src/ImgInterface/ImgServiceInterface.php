<?php 

namespace ImgService\ImgInterface;

interface ImgServiceInterface
{
    public function putObjectStream($filename, $content);

    public function putFile($filename, $file);

    public function isExist($relativePath);

    public function getInfo($relativePath);

    public function resize($relativePath, $thumbSizes);
}