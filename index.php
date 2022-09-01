<?php

$configs = require_once('./config.php');
require_once('./class/ImageMixer.php');

foreach($configs as $config) {
    $imageMixer = new ImageMixer($config);
    $imageMixer->execute();
}
echo "success!\n";
