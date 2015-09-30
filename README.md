## Registry Manager

Laravel 4/5 Registry Manager for storing application specific settings. A mashup of https://github.com/theelphie/registry and https://github.com/torann/laravel-4-registry. A big thanks to @Torann and @theelphie. 

Thanks also to @srlabs for his blog on [Laravel 5 Package Development](http://stagerightlabs.com/blog/laravel5-pacakge-development-service-provider)

## Installation for Laravel 5

Add the following into your `composer.json` file:

```json
{
	"require": {
		"twosuperior/registry": "2.0.x"
	}
}
```

## Installation for Laravel 4

Add the following into your `composer.json` file:

```json
{
	"require": {
		"twosuperior/registry": "1.0.x"
	}
}
```

## Post Install Setup for Laravel 5

Add the service provider and alias into your `app/config/app.php`

```php
'providers' => [
	Twosuperior\Registry\RegistryServiceProvider::class,
],

'Registry' => Twosuperior\Registry\Facades\Registry::class,
```

Run `php artisan vendor:publish`

## Post Install Setup for Laravel 4

Add the service provider and alias into your `app/config/app.php`

```php
'providers' => array(
	'Twosuperior\Registry\RegistryServiceProvider',
),

'Registry' => 'Twosuperior\Registry\Facades\Registry',
```

Run `php artisan config:publish "twosuperior\registry"`

Run `php artisan migrate --package="twosuperior\registry"` to install the registry table

## Usage

Retrieve item from registry
```php
Registry::get('foo'); \\will return null if key does not exists
Registry::get('foo.bar'); \\will return null if key does not exists

Registry::get('foo', 'undefine') \\will return undefine if key does not exists
```

Store item into registry
```php
Registry::set('foo', 'bar');
Registry::set('foo', array('bar' => 'foobar'));

Registry::get('foo'); \\bar
Registry::get('foo.bar'); \\foobar
```

Remove item from registry
```php
Registry::forget('foo');
Registry::forget('foo.bar');
```

Clear cache and reload registry
```php
Registry::clear();
```

Flush registry table
```php
Registry::flush();
```

Dump all values from an item
```php
Registry::dump('foo');
```

Retrieve all items from registry
```php
Registry::all();
```

Mass update

```php
$settings = Input::only('name', 'address', 'email');

Registry::store($settings);
```