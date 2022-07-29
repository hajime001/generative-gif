<?php

abstract class BaseImage
{
    /**
     * @var Imagick
     */
    protected $_imagick = null;

    public function __destruct()
    {
        if (!is_null($this->_imagick)) {
            $this->_imagick->destroy();
        }
    }

    public function getImagick(): Imagick
    {
        return $this->_imagick;
    }
}
