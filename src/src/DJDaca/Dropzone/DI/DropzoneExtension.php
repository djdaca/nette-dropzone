<?php

namespace Vojtys\Dropzone\DI;

use Nette;

/**
 * Class BootstrapDropzoneExtension
 * @package DJDaca\Dropzone\Controls
 */
class DropzoneExtension extends Nette\DI\CompilerExtension
{
    /** @var array  */
    public $defaults = [
        'wwwDir' => '%wwwDir%',
        'thumbnailWidth' => NULL,
        'thumbnailHeight' => NULL,
        'parallelUploads'=> NULL,
        'autoQueue' => NULL
    ];

    public function loadConfiguration()
    {
        // validate config
        $config = $this->validateConfig($this->defaults);

        // add bootstrap dropzone
        $builder = $this->getContainerBuilder();
        $builder->addDefinition($this->prefix('dropzone'))
            ->setFactory('DJDaca\Dropzone\Controls\DropzoneFactory')
            ->setArguments(['wwwDir' => $config['wwwDir']]);
    }
}