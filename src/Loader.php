<?php

namespace Og\RestRoutes;

class Loader {

    private static $instance = null;
    private function __construct() {
    }

    public static function instance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new Loader();
        }
        return $instance;
    }
}