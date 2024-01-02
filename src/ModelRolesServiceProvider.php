<?php

namespace AscentCreative\ModelRoles;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Routing\Router;

use Illuminate\Contracts\Foundation\Application;

class ModelRolesServiceProvider extends ServiceProvider
{
  public function register()
  {
    //

    // Register the helpers php file which includes convenience functions:
    require_once (__DIR__.'/helpers.php');
   
    $this->mergeConfigFrom(
        __DIR__.'/../config/modelroles.php', 'modelroles'
    );

  }

  public function boot()
  {

    $this->loadViewsFrom(__DIR__.'/../resources/views', 'modelroles');

    $this->loadRoutesFrom(__DIR__.'/../routes/modelroles-web.php');

    $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

    $this->app->singleton(UserRoleCache::class, function (Application $app) {
        return new UserRoleCache();
    });

  }

  

  // register the components
  public function bootComponents() {

  }




  

    public function bootPublishes() {

      $this->publishes([
        __DIR__.'/../assets' => public_path('vendor/ascent/modelroles'),
    
      ], 'public');

      $this->publishes([
        __DIR__.'/../config/modelroles.php' => config_path('modelroles.php'),
      ]);

    }



}