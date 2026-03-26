<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ziggy Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Ziggy, which allows you to
    | use Laravel routes in your JavaScript.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Route Group
    |--------------------------------------------------------------------------
    |
    | Specify a route group to only include certain routes in Ziggy.
    |
    */
    'group' => null,

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | Specify which routes to include in Ziggy. By default, all routes are
    | included. You can specify specific route names or patterns.
    |
    */
    'routes' => null,

    /*
    |--------------------------------------------------------------------------
    | Skip Routes
    |--------------------------------------------------------------------------
    |
    | Specify routes to skip/include in Ziggy. By default, no routes are
    | skipped. You can specify specific route names or patterns.
    |
    */
    'skip' => [],
];
