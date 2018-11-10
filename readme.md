# Tenancy
### Installation

```sh
$ composer require "hyn/multi-tenant:5.3.*"
$ php artisan vendor:publish --tag=tenancy
$ php artisan migrate --database=system
```
### Configuration
Edit composer.json
```sh
$ {
    "extra": {
        "laravel": {
            "dont-discover": [
                "hyn/multi-tenant"
            ]
        }
    }
}
```
Register the service provider in your config/app.php:
```sh
 'providers' => [
        // [..]
        // Hyn multi tenancy.
        Hyn\Tenancy\Providers\TenancyProvider::class,
        // Hyn multi tenancy webserver integration.
        Hyn\Tenancy\Providers\WebserverProvider::class,
    ],
```
Deploy configuration
```sh
php artisan vendor:publish --tag tenancy
```

Enabled configuration webserver (Nginx) `config/webserver.php`
```sh
 'nginx' => [
        'enabled' => true,
        ]
```
Add conf to nginx config `/etc/nginx/nginx.conf`
```sh
include <path>/*.conf;
```
Example
```sh
include /home/heriyadi/Project/hosted/storage/app/tenancy/webserver/nginx/*.conf;
```
Dont forget restart Nginx
```sh
sudo systemctl restart nginx
```
Finnaly, create tenat
```sh
php artisan name email domain
```
Example
```sh
php artisan johndoe johndoe@gmail.com johndoe.net
```


