<?php

require_once('./class/BaseImage.php');
require_once('./class/CompositionImage.php');

class DstImage extends BaseImage
{
    public function __construct()
    {
        $this->_imagick = new Imagick();
        $this->__clear();
    }

    public function add(CompositionImage $compositionImage)
    {
        $this->_imagick->addImage($compositionImage->getImagick());
    }

    public function output(string $outputPath)
    {
        $this->_imagick->optimizeImageLayers();
        $this->_imagick->writeImages($outputPath, true);
        $this->__clear();
    }

    private function __clear()
    {
        $this->_imagick->clear();
        $this->_imagick->setFormat('gif');
    }
}
