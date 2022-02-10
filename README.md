# laravel-geolocation

Lib para funções de Directions e Places de diversos provedores

## Requisitos
- 1º: Adicionar a categoria Geolocation no enum
```
- 2º: Middwares:
- 
```
auth.admin
auth.provider_api:api
auth.user_api:api
auth.corp_api
```
- 3º: Models:
```
Settings

## Instalação

Add in composer.json:

```php
"repositories": [
    {
        "type": "vcs",
        "url": "https://libs:ofImhksJ@git.codificar.com.br/laravel-libs/laravel-geolocation.git"
    }
]
```

```php
require:{
        "codificar/geolocation": "1.1.0",
}
```

```php
"autoload": {
    "psr-4": {
        "Codificar\\Geolocation\\": "vendor/codificar/geolocation/src/"
    }
}
```
Update project dependencies:

```shell
$ composer update
```

Register the service provider in `config/app.php`:

```php
'providers' => [
  /*
   * Package Service Providers...
   */
  Codificar\Geolocation\GeolocationServiceProvider::class,
],
```


Check if has the laravel publishes in composer.json with public_vuejs_libs tag:

```
    "scripts": {
        //...
		"post-autoload-dump": [
			"@php artisan vendor:publish --tag=public_vuejs_libs --force"
		]
	},
```

Or publish by yourself


Publish Js Libs and Tests:

```shell
$ php artisan vendor:publish --tag=public_vuejs_libs --force
```

- Migrate the database tables

```shell
php artisan migrate
```


## Langs

- pt-br
- pt
- es
- en
- ao
