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
     * @var MetaData
     */
    private $__metaData = null;

    /**
     * @var int
     */
    private $__startTime = 0;

    public function __construct($config)
    {
        $this->__config = $config;
        $this->__metaData = new MetaData($config['metadata_dir']);
        $this->__loadSrcImages();
        $this->__startTime = time();
    }

    public function __destruct()
    {
        echo 'total ' . $this->__formatTotalTime(time() - $this->__startTime);
    }

    private function __formatTotalTime(int $second): string
    {
        return gmdate('H:i:s', $second);
    }

    public function execute()
    {
        $this->__makeBuildDir();
        $dstGifAnime = new DstImage();
        $failCount = 0;
        for ($i = 1; $i <= $this->__config['generate_num']; ++$i) {
            while (true) {
                $indexes = [];
                foreach ($this->__config['layersOrder'] as $key => $layer) {
                    $indexes[$key] = $this->__lotIndex($layer);
                }

                $dna = $this->__dna($indexes);
                if (!in_array($dna, $this->__dnaTable)) {
                    $this->__dnaTable[] = $dna;
                    $compositionImages = $this->__compositionImages($indexes);
                    $attributes = $this->__convertAttributes($indexes);
                    for ($motion = 0; $motion < $this->__motionNum(); ++$motion) {
                        $dstGifAnime->add($compositionImages[$motion]);
                    }
                    $imgFileName = "{$i}.gif";
                    $dstGifAnime->output("{$this->__config['image_dir']}/{$imgFileName}");
                    $this->__metaData->writeRowAndAdd($this->__buildJson($i, $dna, $imgFileName, $attributes));
                    break;
                } else {
                    if (++$failCount >= self::FAIL_MAX) {
                        throw new Exception('The number of failures has exceeded the specified value.');
                    }
                }
            }
        }
        $this->__metaData->writeJsonMetaData();
        $this->__metaData->writeCsvMetaData();;
    }

    private function __buildJson(int $edition, string $dna, string $imgFileName, array $attributes): array
    {
        return [
            'name' => "{$this->__config['name_prefix']}{$this->__config['name_spacer']}{$edition}",
            'description' => $this->__config['description'],
            'image' => $imgFileName,
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
        static $_motionNum = null;

        if (is_null($_motionNum)) {
            foreach ($this->__config['layersOrder'] as $layer) {
                if (!$this->__isNoMotion($layer)) {
                    $_motionNum = count($this->__srcImages[$layer][0]);
                    break;
                }
            }
        }
        assert(!is_null($_motionNum));

        return $_motionNum;
    }

    private
    function __lotIndex(string $layer): int
    {
        static $_rateMap = [];

        if (empty($_rateMap[$layer])) {
            $rateSum = 0;
            foreach ($this->__rates[$layer] as $index => $rate) {
                $lower = $rateSum + 1;
                $upper = $rateSum + $rate;
                $_rateMap[$layer]['range'][$index] = ['lower' => $lower, 'upper' => $upper];
                $rateSum += $rate;
            }
            $_rateMap[$layer]['max'] = $rateSum;
        }

        $lot = mt_rand(1, $_rateMap[$layer]['max']);
        foreach ($_rateMap[$layer]['range'] as $index => $range) {
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
