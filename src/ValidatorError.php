<?php

namespace Aubind97;

class ValidatorError
{
    /**
     * @var string param wich cause the error
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
     * @var string[] $messages array of messages
     */
    private $messages = [
        'betweenLength' => "'%s' must have a length between %d and %d characters",
        'datetime' => "'%s' must be a date with the format '%s'",
        'email' => "'%s' must be a valide email",
        'empty' => "'%s' could not be empty",
        'filetype' => "'%s' must be a file with the folowing extensions %s",
        'maxLength' => "'%s' must be shorter than %d characters",
        'minLength' => "'%s' must be longer than %d characters",
        'money' => "'%s' must be money format",
        'numeric' => "'%s' must be a number",
        'required' => "'%s is required",
        'uploaded' => "'%s' must contain an file"
    ];

    /**
     * ValidatorError constuctor
     *
     * @param string $key param wich cause the error
     * @param string $rule message name
     * @param array $attributes attibutes to complete the error message
     */
    public function __construct(string $key, string $rule, array $attributes = [])
    {
        $this->key = $key;
        $this->rule = $rule;
        $this->attributes = $attributes;
    }

    /**
     * Override __toString method
     *
     * @return string
     */
    public function __toString()
    {
        $params = array_merge(
            [$this->messages[$this->rule], $this->key],
            $this->attributes
        );

        return (string)call_user_func_array('sprintf', $params);
    }
}
