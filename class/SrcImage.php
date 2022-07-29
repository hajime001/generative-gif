<?php

require_once('./class/BaseImage.php');

class SrcImage extends BaseImage
{
    public function __construct(string $fileName)
    {
        $this->_imagick = new Imagick($fileName);
        $this->_imagick->setFormat('png');
    }
}
