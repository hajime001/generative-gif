<?php

$buildDir = './build';

return [
    'src_dir' => 'layers/sample/',
    'build_dir' => $buildDir,
    'image_dir' => $buildDir . '/images',
    'metadata_dir' => $buildDir . '/metadata',
    'name_prefix' => 'Your Collection',
    'name_spacer' => ' #',
    'description' => 'Remember to replace this description',
    'base_uri' => 'ipfs://NewUriToReplace',
    'image' => [
        'width' => 200,
        'height' => 200,
    ],
    'generate_num' => 5, // 生成数
    'file_name_delimiter' => '#',
    'image_delay' => 50, // 画像切り替え(msec)
    'no_motion_layers' => ['background'],
    'layersOrder' => [
        'background',
        'kouyou',
        'duck',
    ],
];
