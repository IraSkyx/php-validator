<?php

namespace Aubind97;

class Validator
{

    /**
     * @var array array of mime types
     */
    private const MIME_TYPES = [
        'jpg' => 'image/jpeg',
        'png' => 'image/png'
    ];

    /**
     * @var ValidationError[] errors throws during the validation
     */
    private $errors = [];

    /**
     * @var array params
     */
    private $params = [];

    /**
     * Validator Constructor
     *
     * @param array $params params ou want to validate
     * @param array $filter filter of params you need
     */
    public function __construct(array $params = [], array $filter = null)
    {
        if (is_null($filter)) {
            $this->params = $params;
        } else {
            $this->params = array_filter($params, function ($key) use ($filter) {
                return in_array($key, $filter);
            }, ARRAY_FILTER_USE_KEY);
        }
    }

    /**
     * Check if the requested param is a datetime to the specified format
     *
     * @param string $key param you want to validate
     * @param string $format date format (default : 'Y-m-d H:i:s')
     * @return self
     */
    public function dateTime(string $key, string $format = 'Y-m-d H:i:s') : self
    {
        $value = $this->getValue($key);
        $date = \DateTime::createFromFormat($format, $value);
        $error = \DateTime::getLastErrors();
        if ($error['error_count'] > 0 || $error['warning_count'] > 0 || $date === false) {
            $this->addError($key, 'datetime', [$format]);
        }
        return $this;
    }

    /**
     * Check if the requested param is an email
     *
     * @param string $key param you want to validate
     * @return self
     */
    public function email(string $key) : self
    {
        $value = $this->getValue($key);

        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->addError($key, 'email');
        }

        return $this;
    }

    /**
     * Check if the requested param exists in the requested table
     *
     * @param string $key param you want to validate
     * @param string $column column in database
     * @param string $table table where you check
     * @param \PDO $pdo databese connection
     * @return self
     */
    public function exists(string $key, string $column, string $table, \PDO $pdo): self
    {
        $value = $this->getValue($key);
        $statement = $pdo->prepare("SELECT id FROM $table WHERE $column = ?");
        $statement->execute([$value]);

        if ($statement->fetchColumn() === false)
            $this->addError($key, 'exists', [$table]);

        return $this;
    }

    /**
     * Check if the requested param has the correct file extension
     *
     * @param string $key param you want to validate
     * @param array $extensions required extensions
     * @return self
     */
    public function extension(string $key, array $extensions) : self
    {
        /** @var UploadedFileInterface $file */
        $file = $this->getValue($key);

        if ($file !== null && $file->getError() === UPLOAD_ERR_OK) {
            $type = $file->getClientMediaType();
            $extension = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
            $expectedType = self::MIME_TYPES[$extension] ?? null;

            if (!in_array($extension, $extensions) || $expectedType !== $type)
                $this->addError($key, 'filetype', [join(',', $extensions)]);
        }

        return $this;
    }

    /**
     * Check if the requested param has a good length
     *
     * @param string $key param you want to validate
     * @param int|null $min minimum length
     * @param int|null $max maximum length
     * @return self
     */
    public function length(string $key, ?int $min, ?int $max = null) : self
    {
        $value = $this->getValue($key);
        $length = mb_strlen($value);

        if (!is_null($min) &&
            !is_null($max) &&
            ($length < $min || $length > $max)
        ) {
            $this->addError($key, 'betweenLength', [$min, $max]);
            return $this;
        }

        if (!is_null($min) &&
            $length < $min
        ) {
            $this->addError($key, 'minLength', [$min]);
        }

        if (!is_null($max) &&
            $length > $max
        ) {
            $this->addError($key, 'maxLength', [$max]);
        }

        return $this;
    }

    /**
     * Check if the requested param is money format
     *
     * @param string $key param you want to validate
     * @return self
     */
    public function money(string $key) : self
    {
        $value = $this->getValue($key);
        $pattern = '/^[0-9]*((.|,)[0-9]{1,2})?$/';

        if (!is_null($value) && !preg_match($pattern, $value))
            $this->addError($key, 'money');

        return $this;
    }

    /**
     * Check if the requested param is not empty
     *
     * @param string ...$keys list of all required params
     * @return self
     */
    public function notEmpty(string ...$keys) : self
    {
        foreach ($keys as $key) {
            $value = $this->getValue($key);

            if (is_null($value) || empty($value))
                $this->addError($key, 'empty');
        }

        return $this;
    }

    /**
     * Check if the requested param is a numeric
     *
     * @param string $key param you want to validate
     * @return self
     */
    public function numeric(string $key) : self
    {
        $value = $this->getValue($key);

        if (!is_numeric($value)) {
            $this->addError($key, 'numeric');
        }

        return $this;
    }

    /**
     * Check if the requested param is required
     *
     * @param string ...$keys list of all required params
     * @return self
     */
    public function required(string ...$keys) : self
    {
        foreach ($keys as $key) {
            $value = $this->getValue($key);

            if (is_null($value))
                $this->addError($key, 'required');
        }

        return $this;
    }

    /**
     * Check if the requested param will be unique on the table requested
     *
     * @param string $key param you want to validate
     * @param string $table table where you check
     * @param \PDO $pdo database connexion
     * @param integer|null $exclude
     * @return self
     */
    public function unique(string $key, string $table, \PDO $pdo , ?int $exclude = null) : self
    {
        $value = $this->getValue($key);
        $query = "SELECT id FROM $table WHERE $key = ?";
        $params = [$value];

        if ($exclude !== null) {
            $query .= " AND id != ?";
            $params[] = $exclude;
        }

        $statement = $pdo->prepare($query);
        $statement->execute($params);

        if ($statement->fetchColumn() !== false)
            $this->addError($key, 'unique', [$value]);

        return $this;
    }

     /**
     * Check if the requested param is well upload
     *
     * @param string $key param you want to validate
     * @return self
     */
    public function uploaded(string $key) : self
    {
        /** @var UploadedFileInterface $file */
        $file = $this->getValue($key);

        if ($file === null || $file->getError() !== UPLOAD_ERR_OK)
            $this->addError($key, 'uploaded');

        return $this;
    }

    /**
     * Return if the validator has throw an error
     *
     * @return boolean
     */
    public function isValid() : bool
    {
        return empty($this->errors);
    }

    /**
     * Return all errors messages throws during the validation
     *
     * @return ValidationError[] errors messages
     */
    public function getErrors() : array
    {
        return $this->errors;
    }

    /**
     * Return all the filtered params
     *
     * @return array validated params
     */
    public function getParams() : array
    {
        return $this->params;
    }

    /**
     * Add params to the validator
     *
     * @param array $new_params added params
     * @return void
     */
    public function addParams(array $new_params) : void
    {
        $this->params = array_merge($this->params, $new_params);
    }

    /**
     * Return the value of the requested param contain in params
     *
     * @param string $key param name
     * @return mixed|null value of the requested param
     */
    private function getValue(string $key)
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        }
        return null;
    }

    /**
     * Add error in the errors array
     *
     * @param string $key param that cause the error
     * @param string $rule error message
     * @param array $attributes attribute to complete the error message
     * @return void
     */
    private function addError(string $key, string $rule, array $attributes = []) : void
    {
        $this->errors[$key] = new ValidatorError($key, $rule, $attributes);
    }
}
