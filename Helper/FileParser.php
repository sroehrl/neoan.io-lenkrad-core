<?php

namespace Neoan\Helper;

class FileParser
{
    private string $potentialFile;
    private string $fileExtension;

    function __construct(string $path)
    {
        $this->potentialFile = $path;
        if (file_exists($this->potentialFile) && !is_dir($this->potentialFile)) {
            $this->fileExtension = $this->parseFileExtension();
            $this->setHeader();
            $this->readFile();
        }
    }

    function parseFileExtension()
    {
        preg_match('/\.([a-z0-9]+)$/', $this->potentialFile, $hits);
        return $hits[1];
    }

    function setHeader(): void
    {
        switch ($this->fileExtension) {
            case 'js':
                header('Content-Type: text/javascript');
                break;
            case 'css':
                header('Content-Type: text/css');
                break;
            case 'svg':
                header('Content-Type: image/svg+xml');
                break;
            case 'png':
            case 'jpg':
            case 'gif':
            case 'tiff':
            case 'jpeg':
                header('Content-Type: image/' . $this->fileExtension);
                break;
            default:
                header('Content-Type: text/html');

        }
    }
    function readFile(): void
    {
        echo file_get_contents($this->potentialFile);
        Terminate::exit();
    }
}