# generative gif animation and json generator

This tool generates gif animations and Json files for Opensea from image files.

## How to use

### Installation
- php 7.3.6
- ImageMagick 3.7

### Usage

After install, you can enter the following in terminal:

```
php index.php
```

## Setting

### 1. layers

Layers is a folder to place image materials.
For a folder with no motions, place "`name`#`number`.png" directly under the folder.
For a folder with motions, place a folder named "`name`#`number`" under the folder, and place sequentially numbered png files for the number of motions under the folder.
All motions assume the same number of pieces.

### 2. config.php

- layersOrder: The first Layer in the array is the backmost.
- generate_num: Set the number you want to generate
- image width/height: Number of pixels

## Build

Running `index.php` will create `images` and `metadata` in the folder specified in `build_dir` in the config, and generate gif animations and json files in them.

## Utils

### update_info.php

```
php update_info.php
```

The `name_prefix`, `description`, and `base_uri` contents of the config are reflected in the json file.

### json_to_csv.php

```
php json_to_csv.php
```

json→csv

### csv_to_json.php

```
php csv_to_json.php
```

csv→json

## Materials Provided

カツのGIFアニメ様

http://katus-gifani.sakura.ne.jp/
