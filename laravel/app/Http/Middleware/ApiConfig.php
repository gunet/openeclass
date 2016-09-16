<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Storage;

class ApiConfig
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        // In each request we dynamically assign config variables related to database
        $openEclassConfigPath  = Storage::disk('config')->getDriver()->getAdapter()->getPathPrefix();
        include( $openEclassConfigPath . '/config.php');
        config([
            'database.connections.mysql.host' => $mysqlServer,
            'database.connections.mysql.database' => $mysqlMainDb,
            'database.connections.mysql.username' => $mysqlUser,
            'database.connections.mysql.password' => $mysqlPassword,
        ]);
        return $next($request);
    }
}
