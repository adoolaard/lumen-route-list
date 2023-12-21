<?php

namespace adoolaard\LumenRoutesList;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;

class RoutesCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'route:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display all registered routes.';

    /**
     * The table headers for the command.
     *
     * @var array
     */
    protected $headers = ['Method', 'URI', 'Name', 'Action', 'Middleware'];

    /**
     * The columns to display when using the "compact" flag.
     *
     * @var array
     */
    protected $compactColumns = ['verb', 'path', 'controller', 'action'];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->displayRoutes($this->getRoutes());
    }

/**
     * Compile the routes into a displayable format.
     *
     * @return array
     */
    protected function getRoutes()
    {
        global $app;

        $routeCollection = property_exists($app, 'router') ? $app->router->getRoutes() : $app->getRoutes();
        $rows = array();
        foreach ($routeCollection as $route) {
            $controller = $this->getController($route['action']);
            // Show class name without namespace
            if ($this->option('compact') && $controller !== 'None')
                $controller = substr($controller, strrpos($controller, '\\') + 1);

            $rows[] = [
                'method'     => $route['method'],
                'uri'        => $route['uri'],
                'name'       => $this->getNamedRoute($route['action']),
                'action'     => $controller . '@' . $this->getAction($route['action']),
                'middleware' => $this->getMiddleware($route['action']),
            ];
        }

        // Filter the routes by the specified HTTP method if the 'method' option is provided
        if ($method = $this->option('method')) {
            $rows = array_filter($rows, function ($route) use ($method) {
                return strcasecmp($route['method'], $method) === 0;
            });
        }

        return $this->pluckColumns($rows);
    }
/**
 * Format a value to fit within the console window width.
 *
 * @param  string $value
 * @return string
 */
protected function formatValueForConsole($value)
{
    // Retrieve the console window width
    $consoleWidth = $this->getConsoleWidth();

    // If the value is longer than the width, we truncate it
    if (strlen($value) > $consoleWidth) {
        return substr($value, 0, $consoleWidth - 3) . '...';
    }

    return $value;
}

/**
 * Get the width of the console window.
 *
 * @return int
 */
protected function getConsoleWidth()
{
    // Symfony's Terminal class can be used to get the width of the console
    // Alternatively, you may use a default width or calculate it differently
    $terminal = new \Symfony\Component\Console\Terminal();
    return $terminal->getWidth();
}
    /**
     * @param  array $action
     * @return string
     */
    protected function getNamedRoute(array $action)
    {
        return (!isset($action['as'])) ? "" : $action['as'];
    }

    /**
     * @param  array $action
     * @return mixed|string
     */
    protected function getController(array $action)
    {
        if (empty($action['uses'])) {
            return 'None';
        }

        return current(explode("@", $action['uses']));
    }

    /**
     * @param  array $action
     * @return string
     */
    protected function getAction(array $action)
    {
        if (!empty($action['uses'])) {
            $data = $action['uses'];
            if (($pos = strpos($data, "@")) !== false) {
                return substr($data, $pos + 1);
            } else {
                return "METHOD NOT FOUND";
            }
        } else {
            return 'Closure';
        }
    }

    /**
     * @param  array $action
     * @return string
     */
    protected function getMiddleware(array $action)
    {
        return (isset($action['middleware']))
            ? (is_array($action['middleware']))
            ? join(", ", $action['middleware'])
            : $action['middleware'] : '';
    }

    /**
     * Remove unnecessary columns from the routes.
     *
     * @param  array $routes
     * @return array
     */
    protected function pluckColumns(array $routes)
    {
        return array_map(
            function ($route) {
                return Arr::only($route, $this->getColumns());
            }, $routes
        );
    }

    /**
     * Display the route information on the console.
     *
     * @param  array $routes
     * @return void
     */
protected function displayRoutes(array $routes)
    {
        if (empty($routes)) {
            return $this->error("Your application doesn't have any routes.");
        }

        // Format the routes table for display
        $routes = array_map(function ($route) {
            // Format the route's method
            $route['method'] = $route['method'] === 'GET|HEAD' ? 'GET|HEAD' : $route['method'];
            // Format the route's URI
            $route['uri'] = $this->formatRouteUri($route['uri']);
            // Format the route's action
            $route['action'] = $this->formatRouteAction($route['action']);
            return $route;
        }, $routes);

        $this->table($this->getHeaders(), $routes);
    }
    /**
     * Format the route's URI for display.
     *
     * @param  string $uri
     * @return string
     */
    protected function formatRouteUri($uri)
    {
        // Implement logic to format the URI similar to Laravel's route:list
        // For example, you might use the Symfony's OutputFormatter to truncate the string
        return $uri;
    }

    /**
     * Format the route's action for display.
     *
     * @param  string $action
     * @return string
     */
    protected function formatRouteAction($action)
    {
        // Implement logic to format the action similar to Laravel's route:list
        // For example, you might use the Symfony's OutputFormatter to truncate the string
        return $action;
    }

    /**
     * Get the table headers for the visible columns.
     *
     * @return array
     */
    protected function getHeaders()
    {
        return Arr::only($this->headers, array_keys($this->getColumns()));
    }

    /**
     * Get the column names to show (lowercase table headers).
     *
     * @return array
     */
    protected function getColumns()
    {
        $availableColumns = array_map('lcfirst', $this->headers);

        if ($this->option('compact')) {
            return array_intersect($availableColumns, $this->compactColumns);
        }

        if ($columns = $this->option('columns')) {
            return array_intersect($availableColumns, array_map('lcfirst', $columns));
        }

        return $availableColumns;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            [
                'columns',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Columns to include in the route table (' . implode(', ', $this->headers) . ')'
            ],

            [
                'compact',
                'c',
                InputOption::VALUE_NONE,
                'Only show verb, path, controller and action columns'
            ],

            [
                'method',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Filter the routes by method (e.g., GET, POST, etc.)'
            ]
        ];
    }
}
