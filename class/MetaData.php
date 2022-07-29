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
    private $__rows = [];

    public function __construct(string $metaDataPath)
    {
        $this->__metaDataPath = $metaDataPath;
    }

    public function writeRowAndAdd(array $row)
    {
        file_put_contents($this->__metaDataPath . "/{$row['edition']}.json", $this->__jsonEncode($row));
        $this->__rows[] = $row;
    }

    public function writeJsonMetaData()
    {
        foreach ($this->__rows as $row) {
            file_put_contents($this->__metaDataPath . "/{$row['edition']}.json", $this->__jsonEncode($row));
        }
        file_put_contents($this->__metaDataPath . '/' . self::JSON_FILE_NAME, $this->__jsonEncode($this->__rows));
    }

    public function loadJsonMetaData()
    {
        $json = file_get_contents($this->__metaDataPath . '/' . self::JSON_FILE_NAME);
        $this->__rows = json_decode($json, true);
    }

    public function loadCsvMetaData()
    {
        $fp = fopen($this->__metaDataPath . '/' . self::CSV_FILE_NAME, 'r');
        if ($fp) {
            $headers = fgetcsv($fp);
            while ($csv = fgetcsv($fp)) {
                $row = [];
                foreach ($headers as $col => $header) {
                    if ($col <= 5) {
                        $row[$header] = $csv[$col];
                    } else {
                        // 7列(添え字が0～)目以降はattributeと想定
                        $row['attributes'][$header] = $csv[$col];
                    }
                }
                $this->__rows[] = $row;
            }
            fclose($fp);
        }
    }

    public function writeCsvMetaData()
    {
        if (!empty($this->__rows)) {
            $fp = fopen($this->__metaDataPath . '/' . self::CSV_FILE_NAME, 'w');
            if ($fp) {
                $header = $this->__getHeader($this->__rows[0]);
                fputcsv($fp, $header);
                foreach ($this->__rows as $row) {
                    fputcsv($fp, $this->__toRow($row));
                }
                fclose($fp);
            } else {
                throw new Exception('file cannot open');
            }
        }
    }

    private function __getHeader(array $row): array
    {
        $header = array_keys($row);
        array_pop($header);

        return array_merge($header, array_keys($row['attributes']));
    }

    private function __toRow(array $row): array
    {
        $attributes = $row['attributes'];
        unset($row['attributes']);

        return array_merge($row, $attributes);
    }

    private function __jsonEncode(array $row): string
    {
        return json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
