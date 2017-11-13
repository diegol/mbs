<?php

namespace App\Http\Middleware;

use Closure;
use Cache;
use Illuminate\Http\Request;

/**
 * Cache the entire HTML page to get more performance specially under heavy traffic
 *
 * Also this could be implemented with a reverse trafic like varnish
 *
 * @package App\Http\Middleware
 */
class CachePage
{
    /**
     * Cache time in hours
     *
     * In reality this could be cached forever as cache is clear when we receive
     * new conversion data
     */
    const CACHE_TIME_HOURS = 2;

    /**
     * @inheritdoc
     */
    public function handle($request, Closure $next)
    {
        $key = $request->fullUrl();

        /**
         * If cache return html from cache
         *
         * Ideally Memcache or Redis should be implemented for the cache
         *
         */
        if (Cache::has($key) && $request->hasSession() && !$request->session()->has('errors')) {
            return response(Cache::get($key));
        }

        /**
         * If it wasn't cached, execute the request and grab the response
         */
        $response = $next($request);
        /**
         * Saving the cache, only if response code is 200 Nand no errors !!
         */
        if ($response->getStatusCode() == 200 && $request->hasSession() && !$request->session()->has('errors')) {
            Cache::put($key, $response->getContent(), self::CACHE_TIME_HOURS * 60);
        }
        return $response;
    }
}
