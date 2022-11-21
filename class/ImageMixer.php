<?php

require_once('./class/SrcImage.php');
require_once('./class/CompositionImage.php');
require_once('./class/DstImage.php');
require_once('./class/MetaData.php');

class ImageMixer
{
    const FAIL_MAX = 10;

    /**
     * @var array
     */
    private $__config = [];

    /**
     * @var SrcImage[]
     */
    private $__srcImages = [];

    /**
     * @var array
     */
    private $__dnaTable = [];

    /**
     * @param array
     */
    private $__rates = [];

    /**
     * @var array
     */
    private $__attributeMap = [];

    /**
     * @var int
     */
    private $__startTime = 0;

    /**
     * @var int
     */
    private $__motionNum = null;

    /**
     * @var array
     */
    private $__rateIndexMap = [];

    /**
     * @var array
     */
    private $__imageNums = [];

    public function __construct($config)
    {
        $this->__config = $config;
        $this->__loadSrcImages();
        $this->__startTime = time();
    }

    public function __destruct()
    {
        echo 'total ' . $this->__formatTotalTime(time() - $this->__startTime);
    }

    private function __formatTotalTime(int $second): string
    {
        return gmdate('H:i:s', $second) . "\n";
    }

    public function execute($isRebuildImage = false, $start = 0, $end = -1)
    {
        if (!$isRebuildImage) {
            $this->__makeBuildDir();
        }
        $dstGifAnime = new DstImage();
        $metaData = new MetaData($this->__config['metadata_dir']);
        if ($isRebuildImage) {
            $metaData->loadJsonMetaData();
        }
        $failCount = 0;
        for ($i = $start; $i <= $end; ++$i) {
            while (true) {
                $indexes = [];
                $dnaIndexes = [];
                $item = $metaData->getItem($i - 1);
                foreach ($this->__config['layersOrder'] as $key => $layer) {
                    $index = $isRebuildImage ? $this->__calcIndex($item, $layer) : $this->__lotIndex($layer);
                    if ($layer != 'background') {
                        $dnaIndexes[$key] = $index;
                    }
                    $indexes[$key] = $index;
                    if ($layer == $this->__config['image_name_layer']) {
                        $imageName = $this->__attributeMap[$layer][$index];
                    }
                }

                $dna = $this->__dna($dnaIndexes);
                $isDuplicateColor = $this->__isDuplicateColor($indexes);
                if ($isDuplicateColor) {
                    $tmp = isset($this->__imageNums[$imageName]) ? ($this->__imageNums[$imageName] + 1) : 1;
                    $imgFileName = sprintf('%s-%s-%d.gif', $this->__config['chara_name'], $imageName, $tmp);
                    echo $imgFileName . "\r\n";
                }
                if (true) {
                    if (!in_array($dna, $this->__dnaTable)) {
                        $this->__dnaTable[] = $dna;

//                        $imageNum = $this->__imageNums[$imageName] = isset($this->__imageNums[$imageName]) ? ($this->__imageNums[$imageName] + 1) : 1;
//                        $imgFileName = sprintf('%s-%s-%d.gif', $this->__config['chara_name'], $imageName, $imageNum);
                        $imgFileName = basename($item['image']);
                        $imgFilePath = "{$this->__config['image_dir']}/{$imgFileName}";
                        if (!file_exists($imgFilePath)) {
                            $compositionImages = $this->__compositionImages($indexes);
                            $attributes = $this->__convertAttributes($indexes);
                            for ($motion = 0; $motion < $this->__motionNum(); ++$motion) {
                                $dstGifAnime->add($compositionImages[$motion]);
                            }
                            $dstGifAnime->output($imgFilePath);
                        }
                        if (!$isRebuildImage) {
                            $metaData->writeItemAndAdd($this->__buildItem($i, $dna, $imgFileName, $attributes));
                        }
                        break;
                    } else {
                        if (++$failCount >= self::FAIL_MAX) {
                            // throw new Exception('The number of failures has exceeded the specified value.');
                        }
                    }
                }
            }
        }
        echo "failCount: {$failCount}";
        $metaData->writeJsonMetaData();
        $metaData->writeCsvMetaData();
    }

    private function __calcIndex(array $item, $layer) {
        return array_search($item['attributes'][$layer], $this->__attributeMap[$layer]);
    }

    private function __buildItem(int $edition, string $dna, string $imgFileName, array $attributes): array
    {
        return [
            'name' => "{$this->__config['name_prefix']}{$this->__config['name_spacer']}{$edition}",
            'description' => $this->__config['description'],
            'image' => "{$this->__config['base_uri']}/{$imgFileName}",
            'dna' => sha1($dna),
            'edition' => $edition,
            'date' => time(),
            'attributes' => $attributes,
        ];
    }

    private function __makeBuildDir()
    {
        if (file_exists($this->__config['build_dir'])) {
            $this->__rmdir($this->__config['build_dir']);
        }
        mkdir($this->__config['build_dir']);
        mkdir($this->__config['image_dir']);
        mkdir($this->__config['metadata_dir']);
    }

    private function __rmdir(string $dir)
    {
        $res = glob($dir . '/*');
        foreach ($res as $f) {
            is_file($f) ? unlink($f) : $this->__rmdir($f);
        }
        rmdir($dir);
    }

    private function __dna(array $indexes): string
    {
        return implode('', $indexes);
    }

    private function __isDuplicateColor(array $indexes): bool {
        $bgColor = preg_replace('/^bg/', '', $this->__attributeMap['background'][$indexes[0]]);
        foreach ($this->__config['layersOrder'] as $key => $layer) {
            if ($layer === 'background') {
                continue;
            }

            $partColor = $this->__attributeMap[$layer][$indexes[$key]];
            if ($bgColor === $partColor) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $indexes
     * @return CompositionImage[]
     */
    private function __compositionImages(array $indexes): array
    {
        $compositionImages = [];
        foreach ($this->__config['layersOrder'] as $key => $layer) {
            $index = $indexes[$key];
            for ($motion = 0; $motion < $this->__motionNum(); ++$motion) {
                if (empty($compositionImages[$motion])) {
                    $compositionImages[$motion] = new CompositionImage($this->__config['image']['width'], $this->__config['image']['height'], $this->__config['image_delay']);
                }
                $srcImage = $this->__isNoMotion($layer) ? $this->__srcImages[$layer][$index][0] : $this->__srcImages[$layer][$index][$motion];
                $compositionImages[$motion]->composite($srcImage);
            }
        }

        return $compositionImages;
    }

    private function __convertAttributes(array $indexes): array
    {
        $attributes = [];
        foreach ($this->__config['layersOrder'] as $key => $layer) {
            $index = $indexes[$key];
            $attributes[$layer] = $this->__attributeMap[$layer][$index];
        }

        return $attributes;
    }

    private
    function __motionNum(): int
    {
        if (is_null($this->__motionNum)) {
            foreach ($this->__config['layersOrder'] as $layer) {
                if (!$this->__isNoMotion($layer)) {
                    $this->__motionNum = count($this->__srcImages[$layer][0]);
                    break;
                }
            }
        }
        assert(!is_null($this->__motionNum));

        return $this->__motionNum;
    }

    private
    function __lotIndex(string $layer): int
    {
        if (empty($this->__rateIndexMap[$layer])) {
            $rateSum = 0;
            foreach ($this->__rates[$layer] as $index => $rate) {
                $lower = $rateSum + 1;
                $upper = $rateSum + $rate;
                $this->__rateIndexMap[$layer]['range'][$index] = ['lower' => $lower, 'upper' => $upper];
                $rateSum += $rate;
            }
            $this->__rateIndexMap[$layer]['max'] = $rateSum;
        }

        $lot = mt_rand(1, $this->__rateIndexMap[$layer]['max']);
        foreach ($this->__rateIndexMap[$layer]['range'] as $index => $range) {
            if ($range['lower'] <= $lot && $lot <= $range['upper']) {
                return $index;
            }
        }
        throw new Exception();
    }

    private
    function __isNoMotion(string $layer): bool
    {
        return in_array($layer, $this->__config['no_motion_layers']);
    }

    private
    function __loadSrcImages()
    {
        $baseLayerPath = __DIR__ . "/../{$this->__config['src_dir']}";
        foreach ($this->__config['layersOrder'] as $layer) {
            $layerPath = $baseLayerPath . "{$layer}";
            if ($this->__isNoMotion($layer)) {
                $files = glob("{$layerPath}/*");
                foreach ($files as $key => $file) {
                    $this->__srcImages[$layer][$key][] = new SrcImage($file);
                    $this->__rates[$layer][$key] = $this->__getRate(basename($file));
                    $this->__attributeMap[$layer][$key] = $this->__getAttribute(basename($file));
                }
            } else {
                $patterns = glob("{$layerPath}/*");
                foreach ($patterns as $key => $pattern) {
                    $files = glob("{$pattern}/*");
                    foreach ($files as $file) {
                        $this->__srcImages[$layer][$key][] = new SrcImage($file);
                        $this->__rates[$layer][$key] = $this->__getRate(basename($pattern));
                        $this->__attributeMap[$layer][$key] = $this->__getAttribute(basename($pattern));
                    }
                }
            }
        }
    }

    private function __getAttribute(string $fileName): string
    {
        return explode($this->__config['file_name_delimiter'], basename($fileName))[0];
    }

    private function __getRate(string $fileName): int
    {
        return intval(explode($this->__config['file_name_delimiter'], basename($fileName))[1]);
    }
}
