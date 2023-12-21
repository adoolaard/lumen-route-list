# Lumen Route List Display
A convenient package for displaying all registered routes in a Lumen application. This tool provides a clear and concise table of routes directly in the console, making it easier to manage and review your application's routing.


## About
This package is a fork of `appzcoder/lumen-routes-list`, with additional functionality to enhance the route listing capabilities. All original credits for the base functionality go to `appzcoder`.

## Installation

To integrate this package into your Lumen application, follow these steps:

1. Install the package via Composer:
    ```
    composer require adoolaard/lumen-routes-list
    ```

2. Register the service provider in your Lumen application by adding the following line to your bootstrap/app.php file:
    ```php
    $app->register(adoolaard\LumenRoutesList\RoutesCommandServiceProvider::class);
    ```

3.  Refresh Composer's autoload files:
    ```
    composer dump-autoload
    ```

## Usage

With the package installed, you can now use the php artisan route:list command to display all registered routes in your application.

### Available commands

Display all routes:
```
php artisan route:list
```

Filter the output by specifying the columns you want to include:
```
php artisan route:list --columns=Verb --columns=Path --columns=NamedRoute
```

Show a more compact view with essential columns:
```
php artisan route:list --compact
```

Display only routes that match a specific HTTP method (e.g., GET, POST):
```
php artisan route:list --method=GET
```

*These options can be used individually or combined to customize the output to your specific needs.


## Contributing
Contributions to this package are welcome! Feel free to fork the repository, make your improvements, and submit a pull request.



