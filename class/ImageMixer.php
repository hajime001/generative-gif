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

    public function execute($isRebuildImage = false)
    {
        $updateIds = [
            4216,
            10512,
            9687,
            10586,
            372,
            2517,
            5457,
            7965,
            10537,
            3634,
            4136,
            9170,
            4111,
            9390,
            883,
            2018,
            7620,
            8695,
            3383,
            3376,
            8031,
            4080,
            3787,
            9501,
            1417,
            10691,
            9150,
            1961,
            2380,
            9608,
            9503,
            5583,
            7005,
            5334,
            7177,
            6302,
            9356,
            8971,
            4162,
            2531,
            8698,
            7490,
            8091,
            6768,
            4642,
            6819,
            6387,
            6079,
            9559,
            3228,
            1557,
            10170,
            6320,
            934,
            1064,
        ];
        if (!$isRebuildImage) {
            $this->__makeBuildDir();
        }
        $dstGifAnime = new DstImage();
        $metaData = new MetaData($this->__config['metadata_dir']);
        if ($isRebuildImage) {
            $metaData->loadJsonMetaData();
        }
        $failCount = 0;
        foreach($updateIds as $i) {
            while (true) {
                $indexes = [];
                $dnaIndexes = [];
                $item = $metaData->getItem($i - 1);
                foreach ($this->__config['layersOrder'] as $key => $layer) {
                    if ($layer == 'hat') {
                        $index = array_search('kiyoshi', $this->__attributeMap['hat']);
                    } elseif($layer == 'face') {
                        $name = $item['name'];
                        $charaName = explode(' ', $name)[0];
                        $faceColor = strtolower(explode('-', $charaName)[1]);
                        $index = array_search($faceColor, $this->__attributeMap['face']);
                    } else {
                        $index = $this->__lotIndex($layer);;
                    }
                    $indexes[$key] = $index;
                    if ($layer != 'background') {
                        $dnaIndexes[$key] = $index;
                    }
                }

                $dna = $this->__dna($dnaIndexes);
                $isDuplicateColor = $this->__isDuplicateColor($indexes);
                if (!$isDuplicateColor) {
                    if (!in_array($dna, $this->__dnaTable)) {
                        $this->__dnaTable[] = $dna;

                        $imgFileName = sprintf('%d.gif', $i);
                        $imgFilePath = "{$this->__config['image_dir']}/{$imgFileName}";
                        if (!file_exists($imgFilePath)) {
                            $compositionImages = $this->__compositionImages($indexes);
                            $attributes = $this->__convertAttributes($indexes);
                            for ($motion = 0; $motion < $this->__motionNum(); ++$motion) {
                                $dstGifAnime->add($compositionImages[$motion]);
                            }
//                         $imgFileName = "{$i}.gif";
                            $dstGifAnime->output($imgFilePath);
                        }
                        if (!$isRebuildImage) {
                            $metaData->writeItemAndAdd($this->__buildItem($i, $dna, $imgFileName, $attributes));
                        } else {
                            $item['attributes'] = $attributes;
                            $item['dna'] = sha1($dna);
                            $item['date'] = time();
                            $metaData->setItem($i - 1, $item);
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
