<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
      if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&  $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
          $this->app['request']->server->set('HTTPS', true);
      }

      $this->app->bind(
          \Backpack\PermissionManager\app\Http\Controllers\UserCrudController::class, //this is package controller
          \App\Http\Controllers\Admin\UserCrudController::class //this should be your own controller
      );        //
      $this->app->bind(
          \Backpack\PermissionManager\app\Http\Controllers\RoleCrudController::class,
          \App\Http\Controllers\Admin\RoleCrudController::class
      );
      $this->app->bind(
          \Backpack\PermissionManager\app\Http\Controllers\PermissionCrudController::class,
          \App\Http\Controllers\Admin\PermissionCrudController::class
      );
      $this->app->bind(
          \Backpack\CRUD\app\Http\Controllers\MyAccountController::class,
          \App\Http\Controllers\Admin\MyAccountController::class
      );
    }
}
