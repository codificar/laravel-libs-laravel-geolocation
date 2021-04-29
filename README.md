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

## Instanação
- Adicione o pacote no composer.json:

```
"repositories": [
		{
			"type":"package",
			"package": {
			  "name": "codificar/laravel-geolocation",
			  "version":"master",
			  "source": {
				  "url": "https://libs:ofImhksJ@git.codificar.com.br/laravel-libs/laravel-geolocation.git",
				  "type": "git",
				  "reference":"master"
				}
			}
		}
	],

// ...

"require": {
    // ADD this
   "codificar/laravel-geolocation": "dev-master"
},

```
- Agora Adicione 
```
    "autoload": {
        //...
        "psr-4": {
            // Add your Lib here
           "Codificar\\Geolocation\\": "vendor/codificar/laravel-geolocation/src"
            //...
        }
    },
    //...
```
- Execute

```
composer dump-autoload -o
```

- Adicione a Classe no como Provider

```
'providers' => [
         ...,
            // The new package class
           Codificar\Geolocation\GeolocationServiceProvider::class
        ],
```
- Execute as migrations

```

php artisan migrate
```
- Documentação POSTMAN

```
URL POSTMAN
```
## Langs
-pt-br
- pt
- es
- en
- ao