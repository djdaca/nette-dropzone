<?php

namespace DJDaca\Dropzone\Uploader;

use Nette;

/**
 * Class Uploader
 * @package DJDaca\Dropzone\Uploader
 */
class Uploader extends Nette\Object implements IUploader
{
    protected $wwwDir;

    /**
     * Uploader constructor.
     * @param $wwwDir
     */
    public function __construct($wwwDir)
    {
        $this->wwwDir = $wwwDir;
    }

    /**
     * @param Nette\Http\FileUpload $file
     * @param $path
     */
    public function upload(Nette\Http\FileUpload $file, $path)
    {
        try {
            
            $uploadPath = $this->wwwDir . $path;
            $mainFile = new \SplFileInfo($uploadPath .'/'. uniqid() . '-' . $file->getName());
            $file->move($mainFile->__toString());
            return $mainFile;
        } catch (\Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit($e->getMessage());
        }
    }
}