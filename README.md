# laravel5-instant-crud

Get ready CRUD for your schema/tables in seconds.


### Prerequisite
#### You need to have bootstrap files installed in your master theme.

### Installation
#### "doctrine/dbal": "^2.5" add this to your composer.json file.
#### Add this route group to your web.php or any other route file.
```php
	Route::group(['prefix' => 'manage-products'], function () {
        Route::get('/{product_slug}', 'Admin\Products\AdminProductCrudController@index')->name('manage-products.index');
        Route::get('/create/{product_slug}', 'CrudController@create')->name('manage-products.create');
        Route::post('/store/{product_slug}', 'CrudController@store')->name('manage-products.store');
        Route::get('/{id}/edit/{product_slug}', 'CrudController@edit')->name('manage-products.edit');
        Route::PATCH('/{id}/update/{product_slug}', 'CrudController@update')->name('manage-products.update');
        Route::DELETE('/{id}/destroy/{product_slug}', 'CrudController@destroy')->name('manage-products.destroy');
        Route::get('/{id}/show/{product_slug}', 'CrudController@show')->name('manage-products.show');
    });
```
#### Copy commom folder to your resources folder.
#### Copy CrudCtroller.php to your App\http\Controllers folder



