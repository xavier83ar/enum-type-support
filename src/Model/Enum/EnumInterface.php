<?php

namespace EnumTypeSupport\Model\Enum;

/**
 * Interface EnumInterface
 * @package EnumTypeSupport\Model\Enum
 */
interface EnumInterface
{
    /**
     * @return mixed
     */
    public function initialize();

    /**
     * @param string $value Value to check
     * @return mixed
     */
    public function isValid($value);

    /**
     * @return array
     */
    public function values();

    /**
     * @return string
     */
    public function value();

    /**
     * @return string
     */
    public function display();

    /**
     * @return mixed
     */
    public function __toString();

    /**
     * @return array
     */
    public static function options();
}