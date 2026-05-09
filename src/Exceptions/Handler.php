<?php

namespace Unusualify\Modularous\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Middleware\AuthorizationMiddleware;
use Unusualify\Modularous\Http\Middleware\LanguageMiddleware;
use Unusualify\Modularous\Http\Middleware\NavigationMiddleware;

class Handler extends ExceptionHandler
{
    /**
     * Get the view used to render HTTP exceptions.
     */
    protected function getHttpExceptionView(HttpExceptionInterface $e): ?string
    {
        $statusCode = $e->getStatusCode();

        $isAuthenticated = Auth::guard(Modularous::getAuthGuardName())->check();

        // For 404 errors, manually attempt authentication since middleware didn't run
        if (! $isAuthenticated && $statusCode === 404) {
            $isAuthenticated = $this->attemptModularousAuthentication();
        }

        if (in_array($statusCode, [404, 403, 500]) && $isAuthenticated) {
            // Return custom error view for modularous authenticated users
            $view = modularousBaseKey() . "::errors.{$statusCode}";

            if (view()->exists($view)) {
                return $view;
            }
        }

        // For all other cases, use the default Laravel behavior
        return parent::getHttpExceptionView($e);
    }

    /**
     * Manually attempt modularous authentication by checking cookies
     */
    protected function attemptModularousAuthentication(): bool
    {
        try {
            // Check if user is already authenticated (for 403/500 cases where middleware ran)
            $guard = Auth::guard('modularous');
            if ($guard->check()) {
                return true;
            }

            // For 404 errors, check if any session files contain modularous authentication data
            $sessionDir = storage_path('framework/sessions');
            if (is_dir($sessionDir)) {
                $files = scandir($sessionDir);
                $sessionFiles = array_filter($files, function ($file) {
                    return $file !== '.' && $file !== '..' && $file !== '.gitignore';
                });

                // Check each session file for modularous authentication
                foreach ($sessionFiles as $sessionFile) {
                    $userData = $this->getUserDataFromSession($sessionFile);
                    if ($userData) {
                        // Start the session if not already started
                        if (! session()->isStarted()) {
                            session()->start();
                        }

                        // Set the user on the guard
                        $guard->setUser($userData);

                        // Also set the user on the default guard so middleware can access it
                        Auth::setUser($userData);

                        // Run the actual middleware pipeline
                        $this->runModularousMiddleware();

                        Log::info('Successfully set modularous user and ran middleware:', ['file' => $sessionFile, 'user_id' => $userData->id]);

                        return true;
                    }
                }
            }

            // Also check for remember token cookies
            $rememberTokenCookieName = 'remember_' . Modularous::getAuthGuardName();
            if (request()->hasCookie($rememberTokenCookieName)) {
                Log::info('Found remember token cookie');

                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Error in attemptModularousAuthentication: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Run the actual modularous middleware pipeline
     */
    protected function runModularousMiddleware(): void
    {
        try {
            $middleware = [
                LanguageMiddleware::class,
                NavigationMiddleware::class,
                AuthorizationMiddleware::class,
            ];

            // Create a pipeline to run the middleware
            $pipeline = app(Pipeline::class)
                ->send(request())
                ->through($middleware)
                ->then(function ($request) {
                    // This function is called after all middleware has run
                    return $request;
                });

            // Execute the pipeline

        } catch (\Exception $e) {
            Log::error('Error running modularous middleware: ' . $e->getMessage());
        }
    }

    /**
     * Get user data from session file if it contains valid authentication data
     */
    protected function getUserDataFromSession(string $sessionId): ?User
    {
        try {
            // Try to read the session file directly
            $sessionPath = storage_path('framework/sessions/' . $sessionId);

            if (file_exists($sessionPath)) {
                $sessionData = file_get_contents($sessionPath);

                // Laravel stores session data in a serialized format
                // Try to unserialize it
                $unserializedData = @unserialize($sessionData);

                if ($unserializedData !== false) {
                    // Check if the session contains authentication data
                    // Look for the modularous guard session key
                    $userClass = User::class;
                    $loginKey = 'login_modularous_' . sha1($userClass);

                    // Check if session data contains the login key
                    if (array_key_exists($loginKey, $unserializedData)) {
                        $userId = $unserializedData[$loginKey];

                        // Load the user from the database
                        $user = $userClass::find($userId);
                        if ($user) {
                            return $user;
                        }
                    }
                }

                // Fallback to string search if unserialization fails
                if (str_contains($sessionData, 'login_modularous_')) {
                    // Try to extract user ID from string
                    preg_match('/login_modularous_[a-f0-9]+";i:(\d+);/', $sessionData, $matches);
                    if (isset($matches[1])) {
                        $userId = $matches[1];
                        $user = User::find($userId);
                        if ($user) {
                            return $user;
                        }
                    }
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error getting user data from session: ' . $e->getMessage());

            return null;
        }
    }
}
