<?php

$config = require_once('./config.php');
require_once('./class/ImageMixer.php');

$imageMixer = new ImageMixer($config);
$imageMixer->execute();
echo "success!\n";
