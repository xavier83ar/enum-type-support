<?php

namespace EnumTypeSupport\Model\Enum;

use Cake\Database\Type;
use EnumTypeSupport\Database\Type\EnumType;
use JsonSerializable;
use ReflectionClass;
use RuntimeException;

/**
 * Class Enum
 * @package EnumTypeSupport\Model\Enum
 */
abstract class Enum implements EnumInterface, JsonSerializable
{
    /**
     * @var string
     */
    protected $_currentValue;

    /**
     * @var array
     */
    protected $_values = [];

    /**
     * @var array
     */
    protected $_contants = [];

    /**
     * Enum constructor.
     * @param string $value Initial value
     */
    public function __construct($value = '')
    {
        $this->initialize();
        if (is_string($value) && !empty($value) && $this->isValid($value)) {
            $this->_currentValue = $value;
        }
    }

    /**
     * Resposible for fill up $_values
     * @return void
     */
    public function initialize()
    {
        if (empty($this->_values)) {
            $_keys = array_values($this->_getConstants());
            $this->_values = array_combine($_keys, $_keys);
        }
    }

    /**
     * @param string $value Value
     * @return bool
     */
    public function isValid($value)
    {
        return array_key_exists($value, $this->_values);
    }

    /**
     * @return array
     */
    public function values()
    {
        return $this->_values;
    }

    /**
     * @return string
     */
    public function value()
    {
        return $this->_currentValue;
    }

    /**
     * @return string
     */
    public function display()
    {
        return $this->_values[$this->_currentValue];
    }

    /**
     * @return array
     */
    public static function options()
    {
    /** @var EnumType $enumType */
        $enumType = Type::build('enum');

        return $enumType->options(get_called_class());
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->__toString();
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
    /** @var EnumType $enumType */
        $enumType = Type::build('enum');

        return $enumType->getEnumPrefix(get_called_class()) . $enumType->getSeparator() . $this->_currentValue;
    }

    /**
     * @param string $method   Method name
     * @param array $arguments array of arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (substr($method, 0, 2) === 'is') {
            $key = strtolower(substr($method, 2));
            if (!$this->isValid($key)) {
                throw new RuntimeException(__("Key {0} is not a valid value.", $key));
            }

            return $key === $this->_currentValue;
        }

        throw new RuntimeException(__('Unknown method "%s"', $method));
    }

    /**
     * @return array
     */
    protected function _getConstants()
    {
        if (empty($this->_contants)) {
            $this->_contants = (new ReflectionClass(get_class($this)))->getConstants();
        }

        return $this->_contants;
    }
}
