<?php

namespace DJDaca\Dropzone\Controls;

/**
 * Interface IBootstrapDropzone
 * @package DJDaca\Dropzone\Controls
 */
interface IDropzone
{
    /**
     * @param $path
     * @return Dropzone
     */
    public function create($path);
}