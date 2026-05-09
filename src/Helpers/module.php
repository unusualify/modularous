<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\VarDumper\VarDumper;
use Unusualify\Modularous\Entities\Enums\Permission;
use Unusualify\Modularous\Exceptions\ModularousException;
use Unusualify\Modularous\Facades\Modularous;

/*
|--------------------------------------------------------------------------
| #curt is abbreviation of current
| #umod is abbreviation of unusualify/modularous
|--------------------------------------------------------------------------
*/

if (! function_exists('modularousBaseKey')) {
    function modularousBaseKey($notation = null)
    {
        $notation = ! $notation ? $notation : '.' . $notation;

        return Str::snake(env('MODULAROUS_BASE_NAME', 'Modularous')) . $notation;
    }
}

if (! function_exists('curtModule')) {
    function curtModule($file = null)
    {
        $name = curtModuleName($file);

        return Modularous::find(studlyName($name));
    }
}

if (! function_exists('curtModuleName')) {
    function curtModuleName($file = null)
    {

        $dir = $file;

        if (! $file) {

            $pattern = '/^((?![M|m]{1}odules\/Base).)*$/';
            $pattern = '/[M|m]{1}odules\/[A-Za-z]/';

            $dir = fileTrace($pattern);
        }

        // $pattern = '/(?<=\\/[M|m]{1}odules\/).*?(?=(\/|$))/';
        $pattern = '/(?<=[M|m]{1}odules[\/|\\\]).*?(?=(\/|\\\|$))/';

        preg_match($pattern, $dir, $matches);
        if (! count($matches)) {
            Log::error('curtModule: Could not extract module name from path', [
                'file' => $file,
                'matches' => $matches,
                'dir' => $dir,
            ]);

            throw new ModularousException(
                'Could not determine current module from file path. Ensure the path contains a valid module directory (e.g. Modules/ModuleName/).'
            );
        }

        return studlyName($matches[0]);

    }
}

if (! function_exists('curtModuleUrlPrefix')) {
    function curtModuleUrlPrefix($file = null)
    {
        // dd(
        //     curtModule($file)->prefix(),
        //     curtModule($file)->fullPrefix()
        // );
        return curtModule($file)->prefix();
        // return pluralize( kebabCase(curtModule($file)->getName()));
    }
}

if (! function_exists('curtModuleRouteNamePrefix')) {
    function curtModuleRouteNamePrefix($file = null)
    {
        return curtModule($file)->routeNamePrefix();
    }
}

if (! function_exists('curtModuleStudlyName')) {
    function curtModuleStudlyName($file = null)
    {
        // dd( curtModule() );
        return curtModule($file)->getStudlyName();

    }
}
if (! function_exists('curtModuleLowerName')) {
    function curtModuleLowerName($file = null)
    {
        return curtModule($file)->getLowerName();
    }
}
if (! function_exists('curtModuleSnakeName')) {
    function curtModuleSnakeName($file = null)
    {
        return curtModule($file)->getSnakeName();
    }
}

if (! function_exists('classUsesDeep')) {
    /**
     * @param mixed $class
     * @param bool $autoload
     * @return array
     */
    function classUsesDeep($class, $autoload = true)
    {
        $traits = [];

        // Get traits of all parent classes
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while ($class = get_parent_class($class));

        // Get traits of all parent traits
        $traitsToSearch = $traits;
        while (! empty($traitsToSearch)) {
            $newTraits = class_uses(array_pop($traitsToSearch), $autoload);
            $traits = array_merge($newTraits, $traits);
            $traitsToSearch = array_merge($newTraits, $traitsToSearch);
        }

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }

        return array_unique($traits);
    }
}

if (! function_exists('classHasTrait')) {
    /**
     * @param mixed $class
     * @param string $trait
     * @return bool
     */
    function classHasTrait($class, $trait)
    {
        $traits = classUsesDeep($class);

        if (in_array($trait, array_keys($traits))) {
            return true;
        }

        return false;
    }
}

if (! function_exists('moduleRoute')) {
    /**
     * @param string $moduleName
     * @param string $prefix
     * @param string $action
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    function moduleRoute($moduleName, $prefix, $action = '', $parameters = [], $absolute = true, $singleton = false)
    {

        // Fix module name case
        $kebabName = kebabCase($moduleName);
        $snakeName = snakeCase($moduleName);

        // Nested module, pass in current parameters for deeply nested modules
        if (Str::contains($moduleName, '.')) {
            $parameters = array_merge(Route::current()->parameters(), $parameters);
        }

        // Create base route name
        // $routeName = 'admin.' . ($prefix ? $prefix . '.' : '');
        $routeName = ((bool) $prefix ? $prefix . '.' : '');

        // Prefix it with module name only if prefix doesn't contains it already
        if (
            modularousConfig('allow_duplicates_on_route_names', false) ||
            ($prefix !== $moduleName &&
                ! Str::endsWith($prefix, '.' . $moduleName))
        ) {
            $routeName .= "{$snakeName}";
        }
        // dd($snakeName, $parameters);
        if (preg_match('/edit|show|update|destroy|duplicate|restoreRevision|approveRevision|rejectRevision|preview/', $action) && ! array_key_exists($snakeName, $parameters) && ! $singleton) {
            $parameters[$snakeName] = ':id';
            // dd(
            //     $routeName,
            //     $parameters
            // );
        }

        //  Add the action name
        $routeName .= $action ? ".{$action}" : '';

        // dd(
        //     $routeName,
        //     $parameters,
        //     $absolute,
        //     route($routeName, $parameters, $absolute),
        //     $singleton
        // );
        // Build the route
        try {
            return route($routeName, $parameters, $absolute);
        } catch (Throwable $th) {
            Log::error('modularousRoute: Route generation failed', [
                'routeName' => $routeName,
                'moduleName' => $moduleName,
                'prefix' => $prefix,
                'action' => $action,
                'parameters' => $parameters,
                'exception' => $th->getMessage(),
            ]);

            throw new ModularousException(
                "Failed to generate route '{$routeName}': {$th->getMessage()}",
                (int) $th->getCode(),
                $th
            );
        }
    }
}

if (! function_exists('modularousRoute')) {
    /**
     * @param string $routeName
     * @param string $prefix
     * @param string $action
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    function modularousRoute($route, $prefix, $action = '', $parameters = [], $absolute = true)
    {
        // Fix module name case
        $route = Str::camel($route);

        // Create base route name
        // $routeName = 'admin.' . ($prefix ? $prefix . '.' : '');
        $routeName = ($prefix ? $prefix . '.' : '');

        // Prefix it with module name only if prefix doesn't contains it already
        if (
            modularousConfig('allow_duplicates_on_route_names', false) ||
            ($prefix !== $route &&
                ! Str::endsWith($prefix, '.' . $route))
        ) {
            $routeName .= "{$route}";
        }

        //  Add the action name
        $routeName .= $action ? ".{$action}" : '';

        // Build the route
        return route($routeName, $parameters, $absolute);
    }
}

if (! function_exists('getModularousTraits')) {
    /**
     * @return array
     */
    function getModularousTraits()
    {
        return array_keys(Config::get(modularousBaseKey() . '.traits'));
    }
}

if (! function_exists('activeModularousTraits')) {
    /**
     * @return Collection
     */
    function activeModularousTraits($traitOptions)
    {
        return Collection::make($traitOptions)
            ->only(getModularousTraits())
            ->filter(function ($enabled) {
                return $enabled;
            });
    }
}

if (! function_exists('modularousTraitOptions')) {
    /**
     * @param bool $asSignature Return as command signature string instead of array
     * @return array|string
     */
    function modularousTraitOptions($asSignature = false)
    {
        $options = Collection::make(Config::get(modularousBaseKey() . '.traits'))->map(function ($trait, $key) use ($asSignature) {
            if ($asSignature) {
                $shortcut = isset($trait['command_option']['shortcut']) ? $trait['command_option']['shortcut'] . '|' . $key : '--' . $key;
                $valueType = ($trait['command_option']['input_type'] ?? InputOption::VALUE_NONE) !== InputOption::VALUE_NONE ? '=' : '';
                $description = $trait['command_option']['description'] ?? '';

                return sprintf("\n{%s%s : %s}", $shortcut, $valueType, $description);
            }

            return [
                $key,
                $trait['command_option']['shortcut'] ?? null,
                $trait['command_option']['input_type'] ?? InputOption::VALUE_NONE,
                $trait['command_option']['description'] ?? '',
            ];
        });

        return $asSignature ? $options->implode(' ') : $options->values()->toArray();
    }
}

if (! function_exists('modularousConfig')) {
    /**
     * @return string|array
     */
    function modularousConfig($notation = null, $default = '')
    {
        if (! $notation) {
            return config(modularousBaseKey());
        } else {
            return config(modularousBaseKey($notation), $default);
        }
    }
}

if (! function_exists('findParentRoute')) {
    /**
     * @return string|array
     */
    function findParentRoute($config)
    {
        return array_values(array_filter($config['routes'], function ($r) {
            return isset($r['parent']) && $r['parent'];
        }))[0] ?? [];
    }
}

if (! function_exists('formatPermissionName')) {
    /**
     * @return string
     */
    function formatPermissionName($routeName, $permissionType)
    {
        // dd(
        //     Permission::get($permissionType)
        //     // Permission::cases(),
        //     // get_class_methods(Permission::class)
        //     // Permission::{$permissionType}->value
        // );
        return kebabCase($routeName) . '_' . Permission::get($permissionType);
    }
}

if (! function_exists('formatPermissionRecord')) {
    /**
     * @return array
     */
    function formatPermissionRecord($routeName, $permissionType, $guardName)
    {
        return ['name' => formatPermissionName($routeName, $permissionType), 'guard_name' => $guardName];
    }
}

if (! function_exists('routePermissionRecords')) {
    /**
     * @return array
     */
    function routePermissionRecords($routeName, $guardName, $cases = null)
    {
        return Arr::map($cases ?: Permission::cases(), function ($item) use ($routeName, $guardName) {
            return ['name' => kebabCase($routeName) . '_' . $item->value, 'guard_name' => $guardName];
        });
    }
}

if (! function_exists('permissionRecordsFromRoutes')) {
    /**
     * @return array
     */
    function permissionRecordsFromRoutes($routes, $guardName)
    {
        $cases = Permission::cases();
        $records = [];

        foreach ($routes as $routeName) {
            $records = array_merge($records, routePermissionRecords($routeName, $guardName, $cases));
        }

        return $records;
    }
}

if (! function_exists('ifdd')) {
    function ifdd($condition, mixed ...$vars)
    {
        if ($condition) {
            if (! \in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true) && ! headers_sent()) {
                header('HTTP/1.1 500 Internal Server Error');
            }

            if (array_key_exists(0, $vars) && count($vars) === 1) {
                VarDumper::dump($vars[0]);
            } else {
                foreach ($vars as $k => $v) {
                    VarDumper::dump($v, is_int($k) ? 1 + $k : $k);
                }
            }

            exit(1);
        }
    }
}

if (! function_exists('exceptionalRunningInConsole')) {
    function exceptionalRunningInConsole()
    {
        return ! (App::runningInConsole() && App::runningConsoleCommand([
            'modularous:make:module',
            'modularous:fix:module',
            'modularous:make:route',
            'modularous:dev',
        ]));
    }
}

if (! function_exists('backtrace_formatter')) {
    function backtrace_formatter($carry, $item)
    {
        try {
            $carry[$item['file'] ?? $item['class']] = [
                'line' => $item['line'] ?? null,
                'function' => $item['function'] ?? null,
            ];
        } catch (Throwable $th) {
            Log::error('backtrace_formatter: Failed to format backtrace item', [
                'item' => $item,
                'exception' => $th->getMessage(),
            ]);

            throw new ModularousException(
                "Failed to format backtrace: {$th->getMessage()}",
                (int) $th->getCode(),
                $th
            );
        }

        return $carry;
    }
}

if (! function_exists('backtrace_formatted')) {
    function backtrace_formatted()
    {
        return array_reduce(debug_backtrace(), 'backtrace_formatter', []);
    }
}

/**
 * Helper function to benchmark execution time of a callback
 *
 * Usage:
 * $time = benchmark(function () {
 *     // code to benchmark
 * });
 *
 * @param callable $callback The function to benchmark
 * @param bool $returnResult Whether to return the result of the callback
 * @return mixed Time elapsed in seconds or [time, result] if $returnResult is true
 */
if (! function_exists('benchmark')) {
    function benchmark(callable $callback, ?string $label = null, bool $die = false, $unit = 'milliseconds', &$elapsedString = null)
    {
        if (! $die && is_null($label)) {
            throw new Exception('Label is required');
        }

        if (! $die && ! modularousConfig('benchmark_enabled', false)) {
            return $callback();
        }

        if (! in_array($unit, ['microseconds', 'milliseconds', 'seconds'])) {
            throw new Exception('Invalid unit: ' . $unit);
        }

        $startTime = microtime(true);

        $result = $callback();

        // $elapsed is in seconds by default from microtime(true)
        $elapsed = microtime(true) - $startTime;

        if ($unit === 'microseconds') {
            $elapsed = $elapsed * 1000000;
        } elseif ($unit === 'milliseconds') {
            $elapsed = $elapsed * 1000;
        }

        $elapsedString = $elapsed . ' in ' . $unit;

        if ($die) {
            throw new ModularousException(
                "Benchmark stopped: {$elapsedString}"
            );
        }

        // $modularousLogDir = concatenate_path(modularousConfig('log_dir', storage_path('logs/modularous')), 'benchmarks');
        // $channel = [
        //     'driver' => 'single'
        // ];

        $emergencyThreshold = (int) modularousConfig('benchmark_emergency_time', 1000);
        $elapsedMs = $elapsed * 1000;
        $logged = false;
        $logEvent = null;

        if ($elapsedMs > $emergencyThreshold) {
            // $modularousLogPath = concatenate_path($modularousLogDir, 'emergency.log');
            // $channel['path'] = $modularousLogPath;
            $logEvent = 'emergency';
            $message = "BENCHMARK: {$elapsedMs}ms exceeded emergency threshold ({$emergencyThreshold}ms) for {$label}";
            $logged = true;
        }

        if (! $logged && modularousConfig('benchmark_log_level') === 'debug') {
            // $modularousLogPath = concatenate_path($modularousLogDir, 'debug.log');
            // $channel['path'] = $modularousLogPath;

            $logEvent = 'debug';
            $message = "BENCHMARK: {$elapsedString} elapsed for {$label}";
            $logged = true;
        }

        // if(!$logged && modularousConfig('benchmark_log_level') === 'info'){
        //     // $modularousLogPath = concatenate_path($modularousLogDir, 'info.log');
        //     // $channel['path'] = $modularousLogPath;

        //     $logEvent = 'info';
        //     $message = "BENCHMARK: {$elapsed} elapsed for {$label}";
        //     $logged = true;
        // }

        if ($logged && $logEvent) {
            // Log::build($channel)
            Log::channel('modularous-benchmark')
                ->{$logEvent}($message);
        }

        return $result;
    }
}

if (! function_exists('mergeConfigFrom')) {
    function mergeConfigFrom($path, $key)
    {
        return app()->make('config')->set($key, array_merge_recursive_preserve(config($key, []), require $path));
    }
}
