<?php
/**
 * Class Flash
 * @package Backstage\Support
 * @author  Cyrus Collier <ccollier@situationinteractive.com>
 * @version 1.0
 */

namespace Backstage\Support;

class Flash
{
    const EXPIRES = HOUR_IN_SECONDS;

    protected $value;
    protected $transient_key;

    public function __construct(string $prefix)
    {
        $this->transient_key = sprintf('%s_%d', $prefix, get_current_user_id());
    }

    public function setValue($value)
    {
        return set_transient($this->transient_key, $value, static::EXPIRES);
    }

    public function getValue()
    {
        $value = get_transient($this->transient_key);
        delete_transient($this->transient_key);

        return $value;
    }

    public static function create($value, string $prefix)
    {
        $flash = new static($prefix);
        $flash->setValue($value);
        return $flash;
    }

    public static function get(string $prefix)
    {
        $flash = new static($prefix);
        return $flash->getValue();
    }
}
