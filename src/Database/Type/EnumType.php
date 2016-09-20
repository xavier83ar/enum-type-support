<?php

namespace EnumTypeSupport\Database\Type;

use Cake\Database\Driver;
use Cake\Database\Type;
use EnumTypeSupport\Model\Enum\EnumInterface;
use RuntimeException;

/**
 * Class EnumType
 * @package App\Database\Type
 */
class EnumType extends Type
{
    /**
     * @var string
     */
    private $_separator = '::';

    /**
     * @var array
     */
    private $_enumClassMap = [];
    
    /**
     * @var EnumInterface[]
     */
    private $_instances = [];

    /**
     * Casts given value from a PHP type to one acceptable by database
     *
     * @param mixed $value                  value to be converted to database equivalent
     * @param \Cake\Database\Driver $driver object from which database preferences and configuration will be extracted
     * @return mixed
     */
    public function toDatabase($value, Driver $driver)
    {
        if (!($value instanceof EnumInterface)) {
            throw new RuntimeException(__('Value ({0}) must be instance of EnumInterface or a string.', $value));
        }
        $reverseMap = array_flip($this->_enumClassMap);
        $className = get_class($value);

        if (empty($reverseMap[$className])) {
            throw new RuntimeException(__('No enum class map found for class name {0}.', $className));
        }
        return $reverseMap[$className] . $this->_separator . $value->value();
    }

    /**
     * Casts given value from a database type to PHP equivalent
     *
     * @param mixed $value                  value to be converted to PHP equivalent
     * @param \Cake\Database\Driver $driver object from which database preferences and configuration will be extracted
     * @return mixed
     */
    public function toPHP($value, Driver $driver)
    {
        if ($value === null) {
            return null;
        }

        list($enum, $val) = explode($this->_separator, $value);
        $className = $this->_getClassNameForEnum($enum);

        return new $className($val);
    }

    /**
     * Marshalls flat data into PHP objects.
     *
     * Most useful for converting request data into PHP objects
     * that make sense for the rest of the ORM/Database layers.
     *
     * @param mixed $value The value to convert.
     * @return mixed Converted value.
     */
    public function marshal($value)
    {
        if ($value instanceof EnumInterface) {
            return $value;
        }
        
        if (empty($value)) {
            return null;
        }
        
        $value = (string)$value;
        list($enum, $val) = explode($this->_separator, $value);
        $className = $this->_getClassNameForEnum($enum);

        return new $className($val);
    }

    /**
     * @return string
     */
    public function getSeparator()
    {
        return $this->_separator;
    }

    /**
     * @param string $separator Separator chars beetween enum list name and value
     * @return void
     */
    public function setSeparator($separator)
    {
        $this->_separator = $separator;
    }

    /**
     * @param string $prefix Prefix name
     * @param EnumInterface|string $enum Enum type to add
     * @return void
     */
    public function addEnum($prefix, $enum)
    {
        if ($enum instanceof EnumInterface) {
            $enum = get_class($enum);
        }
        
        $this->_enumClassMap[$prefix] = $enum;
    }

    /**
     * @param array $map Array of pefixes and enum classes or instances
     * @return void
     */
    public function addEnums(array $map)
    {
        foreach ($map as $prefix => $enum) {
            $this->addEnum($prefix, $enum);
        }
    }

    /**
     * @param string $className
     * @return array
     */
    public function options($className)
    {
        if (!isset($this->_instances[$className]) || !$this->_instances[$className] instanceof EnumInterface) {
            $this->_instances[$className] = new $className();
        }
        $values = $this->_instances[$className]->values();
        $prefix = $this->_getEnumForClassName($className);

        $options = [];
        foreach ($values as $key => $val) {
            $options[$prefix . $this->_separator . $key] = $val;
        }

        return $options;
    }

    /**
     * Validator function. Bootstrap from plugin add this as a validation provider under "enum" key.
     * Then is possible to use this function (rule) to validate enum data.
     *
     * @param string $value Value to check, from validator
     * @return bool|mixed
     */
    public function isValid($value)
    {
        if (strpos($value, $this->_separator) === false) {
            return false;
        }
        
        list($enum, $val) = explode($this->_separator, (string)$value);
        if (!$enum || !$val) {
            return false;
        }
        
        try {
            $className = $this->_getClassNameForEnum($enum);
        } catch (\Exception $e) {
            return false;
        }

    /** @var EnumInterface $myEnum */
        $myEnum = new $className();
        return $myEnum->isValid($val);
    }

    /**
     * @param string $enum
     * @return string full class name
     */
    protected function _getClassNameForEnum($enum)
    {
        if (!array_key_exists($enum, $this->_enumClassMap) || !class_exists($this->_enumClassMap[$enum])) {
            throw new RuntimeException(__('Enum Class for {0} enum map not found', $enum));
        }
        return $this->_enumClassMap[$enum];
    }

    /**
     * @param string $className
     * @return string
     */
    protected function _getEnumForClassName($className)
    {
        $reverseMap = array_flip($this->_enumClassMap);
        if (array_key_exists($className, $reverseMap) && !empty($reverseMap[$className])) {
            return $reverseMap[$className];
        }
        throw new RuntimeException(__('{} class map not found'));
    }
}
