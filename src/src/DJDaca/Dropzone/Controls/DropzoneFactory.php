<?php

namespace DJDaca\Dropzone\Controls;

use Nette;
use Nette\Http\Request;
use Nette\Http\Response;

/**
 * Class DropzoneFactory
 * @package DJDaca\Dropzone\Controls
 */
class DropzoneFactory extends Nette\Object implements IDropzone
{
    /** @var  Request */
    protected $request;

    /** @var  Response */
    protected $response;

    /** @var  string */
    protected $wwwDir;

    /**
     * Dropzone constructor.
     * @param Request $request
     * @param Response $response
     * @param $wwwDir
     */
    public function __construct(Request $request, Response $response, $wwwDir)
    {
        $this->request = $request;
        $this->response = $response;
        $this->wwwDir = $wwwDir;
    }

    /**
     * @param $path
     * @return Dropzone
     */
    public function create($path)
    {
        return new Dropzone($this->request, $this->response, $this->wwwDir, $path);
    }
}