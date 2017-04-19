<?php

namespace DJDaca\Dropzone\Uploader;

use Nette;

/**
 * Interface IUploader
 * @package DJDaca\Dropzone\Uploader
 */
interface IUploader
{
    /**
     * @param Nette\Http\FileUpload $file
     * @param $path
     * @return string
     */
    public function upload(Nette\Http\FileUpload $file, $path);
}