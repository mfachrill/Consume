    <?php

    /** @var \Laravel\Lumen\Routing\Router $router */

    /*
    |--------------------------------------------------------------------------
    | Application Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register all of the routes for an application.
    | It is a breeze. Simply tell Lumen the URIs it should respond to
    | and give it the Closure to call when that URI is requested.
    |
    */

    $router->get('/', function () use ($router) {
        return $router->app->version();
    });


    $router->group(['middleware' => 'cors'], function ($router) {
        $router->group(['prefix' => 'lending'], function() use ($router) {
            $router->get('/', 'LendingController@index');
            $router->post('/store', 'LendingController@store');
            $router->get('/{id}', 'LendingController@show');
        });
        
        $router->group(['prefix' => 'restoration'], function() use ($router) {
            $router->get('/{lending_id}', 'RestorationController@index');
            $router->post('/store/{lending_id}', 'RestorationController@store');
        });
        
        
        $router->post('/login', 'AuthController@login');
        $router->get('/logout', 'AuthController@logout');
        $router->get('/profile', 'AuthController@me');
        
        $router->group(['prefix' => 'stuff'], function() use ($router) {
            $router->get('/', 'StuffController@index');
            $router->post('/create', 'StuffController@store');
            $router->get('/trash', 'StuffController@deleted');
            $router->get('/show/{id}', 'StuffController@show');
            $router->put('/update/{id}', 'StuffController@update');
            $router->delete('/destroy/{id}', 'StuffController@destroy');
            $router->put('/restore/{id}', 'StuffController@restore');
            $router->put('/restore', 'StuffController@restoreAll');
            $router->delete('/permanent/{id}', 'StuffController@permanentDelete');
            $router->delete('/permanent', 'StuffController@permanentDeleteAll');
            
        });
        
        $router->group(['prefix' => 'user'], function() use ($router) {
            $router->get('/', 'UserController@index');
            $router->post('/create', 'UserController@store');
            $router->get('/trash', 'UserController@deleted');
            $router->get('/show/{id}', 'UserController@show');
            $router->patch('/update/{id}', 'UserController@update');
            $router->delete('/destroy/{id}', 'UserController@destroy');
            $router->put('/restore/{id}', 'UserController@restore');
            $router->put('/restore', 'UserController@restoreAll');
            $router->delete('/permanent/{id}', 'UserController@permanentDelete');
            $router->delete('/permanent', 'UserController@permanentDeleteAll');
        });
        $router->group(['prefix' => 'inbound'], function() use ($router) {
        $router->get('/', 'InboundStuffController@index');
        $router->post('/Inbound/create', 'InboundStuffController@store');
        $router->get('/Inbound/show/{id}', 'InboundStuffController@show');
        $router->patch('/Inbound/patch/{id}', 'InboundStuffController@update');
        $router->delete('/Inbound/delete/{id}', 'InboundStuffController@destroy');
        });
        //stock
        $router->get('/StuffStock', 'StuffStockController@index');
        $router->post('/StuffStock/create', 'StuffStockController@store');
        $router->get('/StuffStock/{id}', 'StuffStockController@show');
        $router->patch('/StuffStock/{id}', 'StuffStockController@update');
        $router->delete('/StuffStock/{id}', 'StuffStockController@destroy');
        
        //user
        
        $router->get('/users', 'UserController@index');
        $router->post('/users/store', 'UserController@store');
        $router->get('/users/trash', 'UserController@trash');
        $router->get('/users/{id}', 'UserController@show');
        $router->patch('/users/update/{id}', 'UserController@update');
        $router->delete('/users/delete/{id}', 'UserController@destroy');
        $router->get('/users/trash/restore/{id}', 'UserController@restore');
        $router->get('users/trash/permanent-delete/{id}', 'UserController@permanentDelete');
        
        //inboundstuff
        
        $router->post('/inbound-stuffs/store', 'InboundStuffController@store');
        $router->delete('inbound-stuffs/delete/{id}', 'InboundStuffController@destroy');
        $router->delete('/inbound-stuffs/delete/{id}', 'InboundStuffController@destroy');
        $router->delete('/inbound-stuffs/permanent/{id}', 'InboundStuffController@deletePermanent');
        $router->get('/inbound-stuffs/trash', 'InboundStuffController@trash');
        $router->get('/restore/{id}', 'InboundStuffController@restore');

    });

