<?php

namespace adoolaard\LumenRoutesList;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
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
    protected $headers = array('Verb', 'Path', 'NamedRoute', 'Controller', 'Action', 'Middleware');

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
        $routes = $this->getRoutes();

        if ($this->option('json')) {
            return $this->displayRoutesAsJson($routes);
        }

        $this->displayRoutes($routes);
    }

        /**
     * Display the route information as JSON.
     *
     * @param  array $routes
     * @return void
     */
    protected function displayRoutesAsJson(array $routes)
    {
        if (empty($routes)) {
            return $this->error("Your application doesn't have any routes.");
        }

        $json = json_encode($routes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->line($json);
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
            // Show class name without namesapce
            if ($this->option('compact') && $controller !== 'None') {
                $controller = substr($controller, strrpos($controller, '\\') + 1);
            }

            $rows[] = [
                'verb'       => $route['method'],
                'path'       => $route['uri'],
                'namedRoute' => $this->getNamedRoute($route['action']),
                'controller' => $controller,
                'action'     => $this->getAction($route['action']),
                'middleware' => $this->getMiddleware($route['action']),
            ];
        }

        // Filter the routes by the specified HTTP method if the 'method' option is provided
        if ($method = $this->option('method')) {
            $rows = array_filter($rows, function ($route) use ($method) {
                // You can use 'strcasecmp' for case-insensitive comparison or '===' for case-sensitive comparison
                return strcasecmp($route['verb'], $method) === 0;
            });
        }

        return $this->pluckColumns($rows);
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

        $this->table($this->getHeaders(), $routes);
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
                'json',
                null,
                InputOption::VALUE_NONE,
                'Display the routes in JSON format'
            ],

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
