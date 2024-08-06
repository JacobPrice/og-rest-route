<?php

namespace Og\RestRoutes;

use Og\RestRoutes\PermissionInterface;

abstract class Route
{
    private $methods;
    private $route;
    private $route_prefix;
    private $permissions_needed;

    private function set_permissions_needed()
    {
        $permissions = '__return_true';
        if ($this instanceof PermissionInterface) {
           $permissions =  $this->has_permission();
        }

        $this->permissions_needed = $permissions;

    }
    public function set_route()
    {
        if ($this->route)
            return;
        $this->route = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', (new \ReflectionClass($this))->getShortName()));

    }
    public function set_route_prefix()
    {
        if ($this->route_prefix)
            return;

        $this->route_prefix = 'og/v1';
    }
    private function set_methods()
    {
        if ($this->methods)
            return;

        $potential_methods = ['get', 'post', 'put', 'delete', 'patch'];
        $methods = [];

        foreach ($potential_methods as $method) {

            if (method_exists($this, $method)) {
                $methods[] = strtoupper($method);
            }
        }
        $this->methods = $methods;
    }

    private function build_endpoint()
    {
        $this->set_permissions_needed();
        $this->set_methods();
        $this->set_route_prefix();
        $this->set_route();
    }
    public function register()
    {
        $this->build_endpoint();
        add_action('rest_api_init', function () {
            register_rest_route($this->route_prefix, $this->route, [
                'methods' => $this->methods,
                'callback' => function () {
                    $method = strtolower($_SERVER['REQUEST_METHOD']);
                    $this->$method();
                },
                'permission_callback' => function() {
                    return $this->permissions_needed;
                }
            ]);
        });
    }
}