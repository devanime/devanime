<?php
/**
 * Class AdminNotification
 * @package DevAnime\View
 * @author  DevAnime <devanimecards@gmail.com>
 * @version 1.0
 */

namespace DevAnime\View;

use DevAnime\Support\Flash;

class AdminNotification implements View
{
    const SUCCESS = 'success';
    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'error';

    const FLASH_PREFIX = 'devanime_admin_notification';

    protected $message;
    protected $status;
    protected $dismissible;

    protected static $notifications = [];
    protected static $hooked = false;

    /**
     * AdminNotification constructor.
     *
     * @param mixed $message     Message to display
     * @param string $status      AdminNotification::SUCCESS
     *                            AdminNotification::INFO
     *                            AdminNotification::WARNING
     *                            AdminNotification::ERROR
     * @param bool   $dismissible Add dismissible X button
     */
    public function __construct($message, string $status = self::SUCCESS, bool $dismissible = true)
    {
        $this->message = $message;
        $this->status = $status ?: static::SUCCESS;
        $this->dismissible = $dismissible;
    }

    function getName(): string
    {
        return 'admin-notification';
    }

    function getScope(): array
    {
        return get_object_vars($this);
    }


    public function __toString(): string
    {
        return sprintf(
            '<div class="notice notice-%s%s"><p>%s</p></div>',
            $this->status,
            ($this->dismissible ? ' is-dismissible' : ''),
            $this->message
        );
    }

    /**
     * @param mixed $message     Message to display
     * @param string                   $status      AdminNotification::SUCCESS
     *                                              AdminNotification::INFO
     *                                              AdminNotification::WARNING
     *                                              AdminNotification::ERROR
     * @param bool                     $dismissible Add dismissible X button
     *
     * @return int The current number of notifications dispatched
     */
    public static function dispatch($message, string $status = self::SUCCESS, bool $dismissible = true): int
    {
        static::$notifications[] = $message instanceof AdminNotification ?
            $message :
            new static($message, $status, $dismissible);
        return count(static::$notifications);
    }

    /**
     * Outputs any registered admin notifications.
     */
    public static function render(): array
    {
        $notifications = (array) (Flash::get(static::FLASH_PREFIX) ?: static::$notifications);
        echo implode("\n", $notifications);
        static::$notifications = [];
        return $notifications;
    }

    public static function shutdown(): bool
    {
        if (empty(static::$notifications)) {
            return false;
        }
        Flash::create(static::$notifications, static::FLASH_PREFIX);
        static::$notifications = [];
        return true;
    }

    public static function init(): bool
    {
        if (!static::$hooked) {
            add_action('admin_notices', [__CLASS__, 'render']);
            add_action('shutdown', [__CLASS__, 'shutdown']);
            static::$hooked = true;
        }
        return static::$hooked;
    }
}
