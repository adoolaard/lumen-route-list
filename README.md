# Lumen Route List Display

<!-- [![Total Downloads](https://poser.pugx.org/appzcoder/lumen-routes-list/d/total.svg)](https://packagist.org/packages/appzcoder/lumen-routes-list) -->
<!-- [![Latest Stable Version](https://poser.pugx.org/appzcoder/lumen-routes-list/v/stable.svg)](https://packagist.org/packages/appzcoder/lumen-routes-list) -->
<!-- [![Latest Unstable Version](https://poser.pugx.org/appzcoder/lumen-routes-list/v/unstable.svg)](https://packagist.org/packages/appzcoder/lumen-routes-list) -->
<!-- [![License](https://poser.pugx.org/appzcoder/lumen-routes-list/license.svg)](https://packagist.org/packages/appzcoder/lumen-routes-list) -->

## About
This package is a fork of [appzcoder/lumen-routes-list]. So all credits go to [appzcoder].

## Installation

1. Run
    ```
    composer require adoolaard/lumen-routes-list
    ```

2. Add service provider into **/bootstrap/app.php** file.
    ```php
    $app->register(adoolaard\LumenRoutesList\RoutesCommandServiceProvider::class);
    ```
3. Run ```composer dump-autoload```

## Commands

```
php artisan route:list
```
