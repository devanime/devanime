<?php

namespace Backstage\Controller;

use Backstage\Util;
use WP_REST_Server, WP_REST_Request, WP_Error;

abstract class RestController
{
    protected $namespace;
    protected $post_type_for_cap = 'post';
    protected $read_cap = false;
    protected $edit_cap = false;

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    abstract public function registerRoutes();


    public function addRoutes($path, array $actions)
    {
        return register_rest_route($this->namespace, $path, $actions);
    }

    public function addRoute($path, $methods, $callback, $permission = false)
    {
        return $this->addRoutes($path, $this->addAction($methods, $callback, $permission));
    }

    public function addReadRoute($path, $callback, $permission = false)
    {
        return $this->addRoute($path, WP_REST_Server::READABLE, $callback, $permission ?: $this->read_cap);
    }

    public function addCreateRoute($path, $callback, $permission = false)
    {
        return $this->addRoute($path, WP_REST_Server::CREATABLE, $callback, $permission ?: $this->edit_cap);
    }

    /**
     * @deprecated
     */
    public function addEditRoute($path, $callback, $permission = false)
    {
        return $this->addCreateRoute($path, $callback, $permission);
    }

    /**
     * @deprecated
     */
    public function addDeleteRoute($path, $callback, $permission = false)
    {
        return $this->addCreateRoute($path, $callback, $permission);
    }

    public function addAction($methods, $callback, $permission = false)
    {
        if (!is_callable($callback)) {
            $callback = [$this, $callback];
        }
        $callback = $this->route($callback);
        $permission_callback = $this->permission($permission);
        return compact('methods', 'callback', 'permission_callback');
    }

    public function addReadAction($callback, $permission = false)
    {
        return $this->addAction(WP_REST_Server::READABLE, $callback, $permission ?: $this->read_cap);
    }

    public function addCreateAction($callback, $permission = false)
    {
        return $this->addAction(WP_REST_Server::CREATABLE, $callback, $permission ?: $this->edit_cap);
    }

    public function addEditAction($callback, $permission = false)
    {
        return $this->addAction(WP_REST_Server::EDITABLE, $callback, $permission ?: $this->edit_cap);
    }

    public function addDeleteAction($callback, $permission = false)
    {
        return $this->addAction(WP_REST_Server::DELETABLE, $callback, $permission ?: $this->delete_cap);
    }

    protected function route(callable $callback)
    {
        return function(WP_REST_Request $request) use ($callback) {
            try {
                $response = call_user_func($callback, $request);
            } catch (\Throwable $e) {
                $response = $this->handleException($e);
            }
            return rest_ensure_response($response);
        };
    }

    protected function permission($cap)
    {
        return $cap ? function() use ($cap) {
            $post_type = get_post_type_object($this->post_type_for_cap);
            return isset($post_type->cap->{$cap}) && current_user_can($post_type->cap->{$cap}) ?:
                new WP_Error(
                    'incorrect_permissions',
                    'Incorrect permissions for requested route',
                    ['status' => rest_authorization_required_code()]
                );
        } : '__return_true';
    }

    protected function handleException(\Throwable $e)
    {
        $type = Util::toSnakeCase((new \ReflectionClass($e))->getShortName());
        $response = new WP_Error('rest_' . $type, $e->getMessage(), ['status' => $e->getCode() ?: 500]);
        if (!(defined('DISABLE_REST_ERROR_LOGGING') && DISABLE_REST_ERROR_LOGGING)) {
            error_log($e->getMessage());
        }
        return $response;
    }
}