<?php

namespace DJDaca\Dropzone\Controls;

use Nette;

/**
 * Class DropzonePanel
 * @package DJDaca\Dropzone\Controls
 * @author Daniel Čekan (djdaca@gmail.com)
 */
class DropzonePanel extends Nette\Application\UI\Control
{
    /** @var  string */
    protected $panelTemplate;

    /** @var  Nette\Localization\ITranslator */
    protected $translator;

    /** @var  int */
    protected $id;

    /**
     * DropzonePanel constructor.
     * @param $id
     * @param Nette\Localization\ITranslator|NULL $translator
     */
    public function __construct($id, Nette\Localization\ITranslator $translator = NULL)
    {
        $this->id = $id;
        $this->translator = $translator;
    }

    public function render()
    {
        $template = $this->createTemplate();
        $template->setTranslator($this->getTranslator());
        $template->setFile($this->getPanelTemplate());
        $template->id = $this->id;
        if (!is_null($this->translator)) {
            $template->setTranslator($this->getTranslator());
        }
        $template->render();
    }

    /**
     * @return mixed
     */
    public function getPanelTemplate()
    {
        return empty($this->panelTemplate) ? dirname(__FILE__) . '/templates/bootstrap-dropzone-panel.latte' :
            $this->panelTemplate;
    }

    /**
     * @param $panelTemplate
     * @return $this
     */
    public function setPanelTemplate($panelTemplate)
    {
        $this->panelTemplate = $panelTemplate;
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