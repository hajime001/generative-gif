<?php

class MetaData
{
    const JSON_FILE_NAME = '_metaData.json';
    const CSV_FILE_NAME = '_metaData.csv';

    /**
     * @var string
     */
    private $__metaDataPath = '';

    /**
     * @var array
     */
    private $__items = [];

    public function __construct(string $metaDataPath)
    {
        $this->__metaDataPath = $metaDataPath;
    }

    public function writeItemAndAdd(array $item)
    {
        file_put_contents($this->__metaDataPath . "/{$item['edition']}.json", $this->__jsonEncode($item));
        $this->__items[] = $item;
    }

    public function writeJsonMetaData()
    {
        foreach ($this->__items as $item) {
            file_put_contents($this->__metaDataPath . "/{$item['edition']}.json", $this->__jsonEncode($item));
        }
        file_put_contents($this->__metaDataPath . '/' . self::JSON_FILE_NAME, $this->__jsonEncode($this->__items));
    }

    public function loadJsonMetaData()
    {
        $json = file_get_contents($this->__metaDataPath . '/' . self::JSON_FILE_NAME);
        $this->__items = json_decode($json, true);
    }

    public function loadCsvMetaData()
    {
        $fp = fopen($this->__metaDataPath . '/' . self::CSV_FILE_NAME, 'r');
        if ($fp) {
            $headers = fgetcsv($fp);
            while ($csv = fgetcsv($fp)) {
                $item = [];
                foreach ($headers as $col => $header) {
                    if ($col <= 5) {
                        $item[$header] = $csv[$col];
                    } else {
                        // 7列(添え字が0～)目以降はattributeと想定
                        $item['attributes'][$header] = $csv[$col];
                    }
                }
                $this->__items[] = $item;
            }
            fclose($fp);
        }
    }

    public function writeCsvMetaData()
    {
        if (!empty($this->__items)) {
            $fp = fopen($this->__metaDataPath . '/' . self::CSV_FILE_NAME, 'w');
            if ($fp) {
                $header = $this->__getHeader($this->__items[0]);
                fputcsv($fp, $header);
                foreach ($this->__items as $item) {
                    fputcsv($fp, $this->__toRow($item));
                }
                fclose($fp);
            } else {
                throw new Exception('file cannot open');
            }
        }
    }

    public function getItem(int $i) {
        return $this->__items[$i];
    }

    private function __getHeader(array $item): array
    {
        $header = array_keys($item);
        array_pop($header);

        return array_merge($header, array_keys($item['attributes']));
    }

    private function __toRow(array $item): array
    {
        $attributes = $item['attributes'];
        unset($item['attributes']);

        return array_merge($item, $attributes);
    }

    private function __jsonEncode(array $item): string
    {
        return json_encode($item, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
