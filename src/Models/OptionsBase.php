<?php

namespace DevAnime\Models;
use DevAnime\Support\Singleton;
use DevAnime\Util;

/**
 * class OptionsBase
 * @package DevAnime\Models
 *
 * Usage (ex. for options key 'event_date_format'):
 *
 * All method class are static proxies passed to singleton's magic __call()
 *
 * OptionsBase::get('event_date_format') // direct
 * or
 * OptionsBase::get('event_date_format', 'Y-m-d') // with default value
 * or
 * OptionsBase::eventDateFormat('Y-m-d'); //magic method
 *
 * Implement specific accessor methods for custom computed behavior, using the "get" prefix,
 * set as protected to continue passing through __get()'s internal cache, and add static call to PHPDoc
 * Ex:
 *
 * <code>
 * /**
 *  * {@}method static eventDateFormat()
 * {@*}
 * class EventOptions extends OptionsBase
 * {
 *     protected function getEventDateFormat($default_value = 'Y-m-d')
 *     {
 *         $value = $this->get('event_date_format', $default_value);
 *         //do something with $value
 *         return $value;
 *     }
 * }
 * </code>
 *
 */
class OptionsBase
{
    protected $options;

    /**
     * @var array Preset default values (option key => default value)
     */
    protected $default_values = [];

    use Singleton;

    protected function get($name)
    {
        $default_value = func_get_args()[1] ??
            $this->default_values[$name] ?? null;
        $value = get_field($name, 'option');
        $type = gettype($default_value);
        if (in_array($type, ['boolean', 'integer', 'double', 'string', 'array', 'object'])) {
            settype($value, $type);
        }
        if ($type == 'array') {
            $value = array_filter($value);
        }
        return !empty($value) ? $value : $default_value;
    }

    /**
     * Converts to underscored option key
     *
     * @param string $name
     * @param array $arguments
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getKeyFromCalledMethod($name, $arguments)
    {
        if ($name != 'get') { //normal accessor method called
            $key = Util::toSnakeCase($name);
        } else if (isset($arguments[0])) { //direct "get" call
            $key = array_shift($arguments);
        } else {
            throw new \InvalidArgumentException('Invalid option name specified');
        }
        return empty($arguments) ? $key : sprintf('%s-%s', $key, md5(serialize($arguments)));
    }

    protected function isValidMethodCall($name)
    {
        return method_exists($this, $name) && 0 === strpos($name, 'get');
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $key = $this->getKeyFromCalledMethod($name, $arguments);
        if (!isset($this->options[$key])) {
            //transform accessor method call into direct "get" call
            if ($name != 'get') {
                $getter_name = 'get' . ucfirst($name);
                if (method_exists($this, $getter_name)) {
                    $name = $getter_name;
                } else {
                    $name = 'get';
                    array_unshift($arguments, $key);
                }

            }
            $this->options[$key] = call_user_func_array([$this, $name], $arguments);
        }
        return $this->options[$key];
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::getInstance(), $name], $arguments);
    }
}
