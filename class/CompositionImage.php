<?php

require_once('./class/BaseImage.php');
require_once('./class/SrcImage.php');

class CompositionImage extends BaseImage
{
    public function __construct(int $columns, int $rows, int $imageDelay)
    {
        $this->_imagick = new Imagick();
        $this->_imagick->newImage($columns, $rows, new ImagickPixel('transparent'));
        $this->_imagick->setFormat('gif');
        $this->_imagick->setImageDelay($imageDelay);
        $this->_imagick->setImageIterations(0); // 無限ループ
    }

    public function composite(SrcImage $srcImage)
    {
        $imagick = $srcImage->getImagick();
        $this->_imagick->compositeImage($imagick, $imagick->getImageCompose(), 0, 0);
    }
}
