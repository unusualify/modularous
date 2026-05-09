<?php

use Illuminate\Support\Facades\File;
use Unusualify\Modularous\Facades\Modularous;

if (! function_exists('get_installed_composer')) {
    function get_installed_composer()
    {

        if (isset($GLOBALS['_composer_bin_dir'])) {
            $installedPath = realpath(concatenate_path($GLOBALS['_composer_bin_dir'], '../composer/installed.php'));
        } else {
            // If we are in Testbench, base_path() points to the skeleton app
            // We want to find the vendor directory of the package/project
            $vendorPath = base_path('vendor');
            if (! file_exists($vendorPath)) {
                $vendorPath = realpath(__DIR__ . '/../../vendor');
            }
            $installedPath = $vendorPath . '/composer/installed.php';
        }

        $installed = require $installedPath;

        return $installed;
    }
}

if (! function_exists('get_package_installed_version')) {
    function get_package_installed_version($package)
    {
        $installedComposer = get_installed_composer();

        return $installedComposer['versions'][$package]
            ? $installedComposer['versions'][$package]['pretty_version']
            : null;
    }
}

if (! function_exists('is_modularous_development')) {
    function is_modularous_development()
    {
        return Modularous::isDevelopment();
    }
}

if (! function_exists('is_modularous_production')) {
    function is_modularous_production()
    {
        return Modularous::isProduction();
    }
}

if (! function_exists('get_modularous_vendor_dir')) {
    function get_modularous_vendor_dir($dir = null)
    {
        return Modularous::getVendorDir($dir);
    }
}

if (! function_exists('get_modularous_vendor_path')) {
    function get_modularous_vendor_path($dir = null)
    {
        return Modularous::getVendorPath($dir);
    }
}

if (! function_exists('get_modularous_src_path')) {
    function get_modularous_src_path($dir = null)
    {
        return Modularous::getVendorPath(concatenate_path('src', $dir));
    }
}

if (! function_exists('modularous_path')) {
    function modularous_path($path = null)
    {
        return Modularous::getVendorPath($path);
    }
}

if (! function_exists('get_package_version')) {
    function get_package_version($package = null)
    {
        $tag = trim(shell_exec('cd ' . base_path() . ' && git describe --tags --abbrev=0'));

        if ($package) {
            if ($package === 'unusualify/modularous' && Modularous::isDevelopment()) {
                return 'development';
            }

            return get_package_installed_version($package);
        }

        return $tag;
    }
}

if (! function_exists('set_env_file')) {
    function set_env_file($variable, $value)
    {
        // Read the current .env file
        $envFile = base_path('.env');
        $envContents = File::get($envFile);

        // Replace the APP_VERSION line
        $envContents = preg_replace(
            '/' . $variable . '=.*/',
            $variable . '=' . $value,
            $envContents
        );

        // Write the modified contents back to .env
        File::put($envFile, $envContents);

    }
}
