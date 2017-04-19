<?php

namespace DJDaca\Dropzone\Controls;

use Nette;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Utils\Random as NRandom;
use Nette\Localization\ITranslator;
use DJDaca\Dropzone\Translations;
use DJDaca\Dropzone\Uploader\IUploader;
use DJDaca\Dropzone\Uploader\Uploader;
use Tracy\Debugger;

/**
 * Class Dropzone
 * @package DJDaca\Dropzone\Controls
 * @author Daniel Čekan (djdaca@gmail.com)
 */
class Dropzone extends Nette\Application\UI\Control
{
    const DEFAULT_TEMPLATE = 'templates/default.dropzone.latte';
    const DEFAULT_PREVIEW_TEMPLATE = 'templates/bootstrap.dropzone-preview.latte';
    const DEFAULT_PANEL_TEMPLATE = 'templates/bootstrap.dropzone-panel.latte';
    const DEFAULT_PREVIEW_CONTAINER = 'nette-dropzone-previews';
    const DEFAULT_CLICKABLE = 'nette-dropzone-clickable';
    const DEFAULT_AUTO_PROCESS_QUEUE = TRUE;
    const DEFAULT_THUMBNAIL_WIDTH = 50;
    const DEFAULT_THUMBNAIL_HEIGHT = 50;
    const DEFAULT_PARALLEL_UPLOADS = 1;
    const DEFAULT_AUTO_QUEUE = FALSE;
    const DEFAULT_PREVIEW_DISABLED = FALSE;
    const DEFAULT_MAX_FILE_SIZE = 50;
    const DEFAULT_MAX_FILES = 100;

    /** @var string  */
    protected $template = self::DEFAULT_TEMPLATE;

    /** @var  string */
    protected $previewTemplate;

    /** @var bool  */
    protected $previewDisabled = self::DEFAULT_PREVIEW_DISABLED;

    /** @var  string */
    protected $panelTemplate;

    /** @var int  */
    protected $thumbnailWidth = self::DEFAULT_THUMBNAIL_WIDTH;

    /** @var int  */
    protected $thumbnailHeight = self::DEFAULT_THUMBNAIL_HEIGHT;

    /** @var int  */
    protected $parallelUploads = self::DEFAULT_PARALLEL_UPLOADS;

    /** @var bool  */
    protected $autoProcessQueue = self::DEFAULT_AUTO_PROCESS_QUEUE;

    /** @var int  */
    protected $maxFileSize = self::DEFAULT_MAX_FILE_SIZE;

    /** @var int  */
    protected $maxFiles = self::DEFAULT_MAX_FILES;

    /** @var  string */
    protected $acceptedFiles;

    /**
     * Define the container to display the previews
     * @var string
     */
    protected $previewsContainer = self::DEFAULT_PREVIEW_CONTAINER;

    /**
     * Define the element that should be used as click trigger to select files.
     * @var string
     */
    protected $clickable = self::DEFAULT_CLICKABLE;

    /**
     * Make sure the files aren't queued until manually added
     * @var bool
     */
    protected $autoQueue = self::DEFAULT_AUTO_QUEUE;

    /** @var  IUploader */
    protected $uploader;

    /** @var  Request */
    protected $request;

    /** @var  Response */
    protected $response;

    /** @var  string */
    protected $wwwDir;

    /** @var  string */
    protected $path;

    /** @var  int */
    protected $id;

    /** @var  Nette\Localization\ITranslator */
    protected $translator = NULL;

    /** @var callable */
    public $onUploadComplete = [];

    /** @var callable */
    public $onSuccess = [];

    /** @var array  */
    public $files = [];


    /**
     * BootstrapDropzone constructor.
     * @param Request $request
     * @param Response $response
     * @param $wwwDir
     * @param $path
     */
    public function __construct(Request $request, Response $response, $wwwDir, $path)
    {
        $this->request = $request;
        $this->response = $response;
        $this->wwwDir = $wwwDir;
        $this->path = $path;
        $this->id = NRandom::random(8, 'a-z');
    }

    public function createComponentUploadForm()
    {
        $Form = new Nette\Application\UI\Form();
        $Form->getElementPrototype()->addAttributes(["class" => "dropzone"]);
        $Form->addUpload("file", NULL)->setHtmlId("fileUpload");
        $Form->onSuccess[] = function(Nette\Application\UI\Form $Form, $Values){
            $this->process(array($Values->file));
        };
        
        return $Form;
    }

    public function render()
    {
        $template = $this->createTemplate();
        $template->setTranslator($this->getTranslator());
        $template->setFile($this->template);
        $template->settings = json_encode([
            'maxFilesize' => $this->getMaxFileSize(),
            'dictFileTooBig' => $this->translator->translate('Dropzone.FileTooBig'),
            'dictInvalidFileType' => $this->translator->translate('Dropzone.InvalidFileType'),
            'maxFiles' => $this->getMaxFiles(),
            'acceptedFiles' => $this->getAcceptedFiles(),
            'thumbnailWidth' => $this->getThumbnailWidth(),
            'thumbnailHeight' => $this->getThumbnailHeight(),
            'parallelUploads' => $this->getParallelUploads(),
            'previewsContainer' => $this->getPreviewsContainer(),
            'clickable' => $this->getClickable(),
            'autoQueue' => $this->getAutoQueue(),
            'url' => $this->getUrl(),
            'refreshUrl' => $this->getRefreshUrl(),
            'autoProcessQueue' => $this->isAutoProcessQueue(),
        ]);
        $template->id = $this->id;
        $template->render();
    }

    public function process($files)
    {
        $uploader = $this->getUploader();

        /** @var Nette\Http\FileUpload $fileUpload */
        foreach ($files as $fileUpload) {
            $file = $uploader->upload($fileUpload, $this->path);
            foreach ($this->onSuccess as $callback) {
                if (is_callable($callback)) {
                    call_user_func($callback, $file);
                }
            }
            $this->setFiles($file);
        }
    }

    public function handleUpload()
    {
        $files = $this->request->files;
        if ($files) {
            $this->process($files);
        }

        $response = new Nette\Application\Responses\JsonResponse($this->getFiles());
        $response->send($this->request, $this->response);
        die();
    }

    public function handleUploadSuccess()
    {
        foreach ($this->onUploadComplete as $callback) {
            if (is_callable($callback)) {
                call_user_func($callback, $this->request->getPost('files'));
            }
        }
    }

    /**
     * @return DropzonePreview
     */
    public function createComponentPreview()
    {
        $preview = new DropzonePreview($this->getTranslator());
        if (!$this->isPreviewDisabled()) {
            $preview->setPreviewTemplate($this->getPreviewTemplate());
        }
        return $preview;
    }

    /**
     * @return DropzonePanel
     */
    public function createComponentPanel()
    {
        $panel = new DropzonePanel($this->id, $this->getTranslator());
        return $panel->setPanelTemplate($this->getPanelTemplate());
    }

    /**
     * @return string
     */
    public function getRefreshUrl()
    {
        return $this->link('refresh!');
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     * @return Dropzone
     */
    public function setTemplate($template)
    {
        if (!is_file($template)) {
            if (is_file(__DIR__ . DIRECTORY_SEPARATOR .'template'. DIRECTORY_SEPARATOR . $template)) {
                $template = __DIR__ . DIRECTORY_SEPARATOR .'template'. DIRECTORY_SEPARATOR . $template;

            } else { 
                throw new Nette\Application\BadRequestException('Template file "'. $template .'" was not found.');
            }
        }
        $this->template = $template;
        return $this;
    }

    /**
     * @return int
     */
    public function getThumbnailWidth()
    {
        return $this->thumbnailWidth;
    }

    /**
     * @param int $thumbnailWidth
     * @return Dropzone
     */
    public function setThumbnailWidth($thumbnailWidth)
    {
        $this->thumbnailWidth = $thumbnailWidth;
        return $this;
    }

    /**
     * @return int
     */
    public function getThumbnailHeight()
    {
        return $this->thumbnailHeight;
    }

    /**
     * @param int $thumbnailHeight
     * @return Dropzone
     */
    public function setThumbnailHeight($thumbnailHeight)
    {
        $this->thumbnailHeight = $thumbnailHeight;
        return $this;
    }

    /**
     * @return int
     */
    public function getParallelUploads()
    {
        return $this->parallelUploads;
    }

    /**
     * @param int $parallelUploads
     * @return Dropzone
     */
    public function setParallelUploads($parallelUploads)
    {
        $this->parallelUploads = $parallelUploads;
        return $this;
    }

    /**
     * Returns selector for previews
     *
     * @return string
     */
    public function getPreviewsContainer()
    {
        return '#' . $this->id . '-' . $this->previewsContainer;
    }

    /**
     * Define the container to display the previews
     *
     * @param string $previewsContainer
     * @return Dropzone
     */
    public function setPreviewsContainer($previewsContainer)
    {
        $this->previewsContainer = $previewsContainer;
        return $this;
    }

    /**
     * Returns selector for clickable action
     *
     * @return string
     */
    public function getClickable()
    {
        return '.' . $this->id . '-' . $this->clickable;
    }

    /**
     * Define the element that should be used as click trigger to select files.
     *
     * @param string $clickable
     * @return Dropzone
     */
    public function setClickable($clickable)
    {
        $this->clickable = $clickable;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAutoQueue()
    {
        return $this->autoQueue;
    }

    /**
     * @return bool
     */
    public function getAutoQueue()
    {
        return $this->autoQueue;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->link('upload!');
    }

    /**
     * @return IUploader|Uploader
     */
    public function getUploader()
    {
        if (!$this->uploader instanceof IUploader) {
            return new Uploader($this->wwwDir);
        } else {
            return $this->uploader;
        }
    }

    /**
     * @param IUploader $uploader
     * @return Dropzone
     */
    public function setUploader($uploader)
    {
        $this->uploader = $uploader;
        return $this;
    }

    /**
     * @return string
     */
    public function getPreviewTemplate()
    {
        return empty($this->previewTemplate) ?
            dirname(__FILE__) . '/' . self::DEFAULT_PREVIEW_TEMPLATE : $this->previewTemplate;
    }

    /**
     * @param string $previewTemplate
     * @return Dropzone
     */
    public function setPreviewTemplate($previewTemplate)
    {
        $this->previewTemplate = $previewTemplate;
        return $this;
    }

    /**
     * @return string
     */
    public function getPanelTemplate()
    {
        return empty($this->panelTemplate) ?
            dirname(__FILE__) . '/' . self::DEFAULT_PANEL_TEMPLATE : $this->panelTemplate;
    }

    /**
     * @param string $panelTemplate
     * @return Dropzone
     */
    public function setPanelTemplate($panelTemplate)
    {
        $this->panelTemplate = $panelTemplate;
        return $this;
    }

    /**
     * @return $this
     */
    public function disablePreviewTemplate()
    {
        $this->previewDisabled = TRUE;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPreviewDisabled()
    {
        return ($this->previewDisabled == TRUE) ? TRUE : FALSE;
    }

    /**
     * @return Nette\Localization\ITranslator
     */
    public function getTranslator()
    {
        if ($this->translator === NULL) {
            $this->setTranslator(new Translations\FileTranslator);
        }
        return $this->translator;
    }

    /**
     * @param Nette\Localization\ITranslator $translator
     * @return Dropzone
     */
    public function setTranslator(ITranslator $translator)
    {
        $this->translator = $translator;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAutoProcessQueue()
    {
        return $this->autoProcessQueue;
    }

    /**
     * @return $this
     */
    public function setAutoUpload()
    {
        $this->autoProcessQueue = TRUE;
        $this->autoQueue = TRUE;
        return $this;
    }

    /**
     * @return array
     */
    public function getFiles() {
        return $this->files;
    }

    /**
     * @param $file
     */
    public function setFiles($file) {
        array_push($this->files, $file);
    }

    /**
     * @return int
     */
    public function getMaxFileSize()
    {
        return $this->maxFileSize;
    }

    /**
     * @param int $maxFileSize
     */
    public function setMaxFileSize($maxFileSize)
    {
        $this->maxFileSize = $maxFileSize;
    }

    /**
     * @return int
     */
    public function getMaxFiles()
    {
        return $this->maxFiles;
    }

    /**
     * @param int $maxFiles
     */
    public function setMaxFiles($maxFiles)
    {
        $this->maxFiles = $maxFiles;
    }

    /**
     * @return string
     */
    public function getAcceptedFiles()
    {
        return $this->acceptedFiles;
    }

    /**
     * @param string $acceptedFiles
     */
    public function setAcceptedFiles($acceptedFiles)
    {
        $this->acceptedFiles = $acceptedFiles;
    }

    public function handleRefresh()
    {
        $this->redrawControl();
    }
}