<?php

namespace Neoan\Helper;

class FileUpload
{
    public string $name;

    public string $type;

    public array $file;

    public string $size;
    public function __construct(array $file)
    {
        $this->file = $file;
        $this->name = $file['name'];
        $this->type = $file['type'];
        $this->size = $file['size'];
    }
    public function getExtension(): string
    {
        return preg_split('/\//',$this->type)[1];
    }
    public function getSize(string $unit = 'KB'): float
    {

        $kb = $this->size / 1024;
        $mb = $kb / 1024;
        $gb = $mb / 1024;
        $sizes = [
            'KB' => $kb,
            'MB' => $mb,
            'GB' => $gb
        ];
        return $sizes[$unit];
    }

    public function store(string $path): void
    {
        move_uploaded_file($this->file['tmp_name'], $path);
    }
}