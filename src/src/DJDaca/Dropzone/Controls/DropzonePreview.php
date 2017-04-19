<?php

namespace DJDaca\Dropzone\Controls;

use Nette;

/**
 * Class DropzonePreview
 * @package DJDaca\Dropzone\Controls
 * @author Daniel Čekan (djdaca@gmail.com)
 */
class DropzonePreview extends Nette\Application\UI\Control
{
    /** @var  string */
    protected $previewTemplate;

    /** @var  Nette\Localization\ITranslator */
    protected $translator;

    /**
     * DropzonePreview constructor.
     * @param Nette\Localization\ITranslator|NULL $translator
     */
    public function __construct(Nette\Localization\ITranslator $translator = NULL)
    {
        $this->translator = $translator;
    }

    public function render()
    {
        if (!empty($this->previewTemplate)) {
            $template = $this->createTemplate();
            $template->setFile($this->previewTemplate);
            if (!is_null($this->translator)) {
                $template->setTranslator($this->getTranslator());
            }
            $template->render();
        }
    }

    /**
     * @param mixed $previewTemplate
     * @return DropzonePreview
     */
    public function setPreviewTemplate($previewTemplate)
    {
        $this->previewTemplate = $previewTemplate;
        return $this;
    }

    /**
     * @return Nette\Localization\ITranslator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param Nette\Localization\ITranslator $translator
     * @return DropzonePreview
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
        return $this;
    }
}