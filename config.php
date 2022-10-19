<?php

$layerOrders = [
    'janomeanime' => [
        'generate_num' => 2222,
        'img_name_layer' => 'face',
        'chara_name' => 'janome',
        'layer_order' => [
            'background',
            'snake',
            'body',
            'face',
            'hairpin',
            'accessory',
        ]
    ],
    'otoanime' => [
        'generate_num' => 2222,
        'img_name_layer' => 'face',
        'chara_name' => 'oto',
        'layer_order' => [
            'background',
            'tail',
            'cloth',
            'face',
            'leg',
            'arm',
            'hat',
            'rabbit',
        ]
    ],
    'ukaanime' => [
        'generate_num' => 2222,
        'img_name_layer' => 'body',
        'chara_name' => 'uka',
        'layer_order' => [
            'background',
            'tail',
            'body',
            'leg',
            'arm',
            'rod',
            'hat',
            'accessory',
        ]
    ],
    'xiaoanime' => [
        'generate_num' => 2223,
        'img_name_layer' => 'head',
        'chara_name' => 'xiao',
        'layer_order' => [
            'background',
            'leg',
            'cloth',
            'head',
            'panda',
            'hat',
        ]
    ],
    'yuianime' => [
        'generate_num' => 2222,
        'img_name_layer' => 'head',
        'chara_name' => 'yui',
        'layer_order' => [
            'background',
            'cloth',
            'ribbon',
            'accessory',
            'head',
            'makimono',
            'arm',
            'leg',
        ]
    ],
];

$configs = [];
foreach ($layerOrders as $target => $layerOrder) {
    $buildDir = 'build/' . $target;
    $configs[] = [
        'src_dir' => "layers/{$target}/",
        'build_dir' => $buildDir,
        'image_dir' => $buildDir . '/images',
        'metadata_dir' => $buildDir . '/metadata',
        'name_prefix' => 'Your Collection',
        'name_spacer' => ' #',
        'description' => 'Remember to replace this description',
        'base_uri' => 'ipfs://NewUriToReplace',
        'image' => [
            'width' => 1000,
            'height' => 1000,
        ],
        'generate_num' => $layerOrder['generate_num'], // 生成数
        'image_name_layer' => $layerOrder['img_name_layer'],
        'chara_name' => $layerOrder['chara_name'],
        'file_name_delimiter' => '#',
        'image_delay' => 9, // 表示間隔(100で1秒間表示。8だと「100/8」で約12.5FPS)
        'no_motion_layers' => ['background'],
        'layersOrder' => $layerOrder['layer_order'],
    ];
}

return $configs;
