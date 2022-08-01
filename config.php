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
        'width' => 200, // px
        'height' => 200, // px
    ],
    'generate_num' => 5, // 生成数
    'file_name_delimiter' => '#',
    'image_delay' => 20, // 表示間隔(100で1秒間表示。8だと「100/8」で約12.5FPS)
    'no_motion_layers' => ['background'],
    'layersOrder' => [
        'background',
        'kouyou',
        'duck',
    ],
];
