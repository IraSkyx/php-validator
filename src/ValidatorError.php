<?php

namespace Aubind97;

use Gettext\Translator;
use Gettext\Translations;

class ValidatorError
{
    /**
     * @var string param which causes the error
     */
    private $key;

    /**
     * @var string message name
     */
    private $rule;

    /**
     * @var array attibutes to complete the error message
     */
    private $attributes;

    /**
     * @var Translator translator for translations
     */
    private $translator;

    /**
     * ValidatorError constructor
     *
     * @param string $key param which causes the error
     * @param string $rule message name
     * @param array $attributes attibutes to complete the error message
     * @param string $locale Language in which the error messages should be translated
     */
    public function __construct(string $key, string $rule, array $attributes = [], string $locale = "en-US")
    {
        $this->key = $key;
        $this->rule = $rule;
        $this->attributes = $attributes;
        $this->locale = $locale;
        $this->setTranslator(new Translator());
    }

    /**
     * Translator setter
     *
     * @return ValidatorError
     */
    public function setTranslator($translator){
        $this->translator = $translator;
        $this->translator->loadTranslations(Translations::fromPoFile(__DIR__ . '/../locale/' . $this->locale . '/messages.po'));
        return $this;
    }

    /**
     * Key getter
     *
     * @return string
     */
    public function getKey() : string {
        return $this->key;
    }

    /**
     * ValidatorError clone but with key change
     *
     * @param string $key param which causes the error
     * @return ValidatorError
     */
    public function changeKey(string $key) {
        return (new ValidatorError($key, $this->rule, $this->attributes, $this->locale))->setTranslator($this->translator);
    }

    /**
     * Override __toString method
     *
     * @return string
     */
    public function __toString()
    {
        $params = array_merge(
            [$this->translator->gettext($this->rule), $this->key],
            $this->attributes
        );

        return (string)call_user_func_array('sprintf', $params);
    }
}
