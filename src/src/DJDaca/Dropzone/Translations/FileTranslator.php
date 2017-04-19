<?php

namespace DJDaca\Dropzone\Translations;

use DJDaca\Dropzone\Exception;

/**
 * Simple file translator.
 *
 * @package     Grido
 * @subpackage  Translations
 * @author      Petr Bugyík
 */
class FileTranslator extends \Nette\Object implements \Nette\Localization\ITranslator
{
    /** @var array */
    protected $translations = [];

    /**
     * @param string $lang
     * @param array $translations
     */
    public function __construct($lang = 'en', array $translations = [])
    {
        $translations = $translations + $this->getTranslationsFromFile($lang);
        $this->translations = $translations;
    }

    /**
     * Sets language of translation.
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->translations = $this->getTranslationsFromFile($lang);
    }

    /**
     * @param string $lang
     * @throws Exception
     * @return array
     */
    protected function getTranslationsFromFile($lang)
    {
        $filename = __DIR__ . "/$lang.php";
        if (!file_exists($filename)) {
            throw new Exception("Translations for language '$lang' not found.");
        }

        return include ($filename);
    }

    /************************* interface \Nette\Localization\ITranslator **************************/

    /**
     * @param string $message
     * @param int $count plural
     * @return string
     */
    public function translate($message, $count = NULL)
    {
        return isset($this->translations[$message])
            ? $this->translations[$message]
            : $message;
    }
}