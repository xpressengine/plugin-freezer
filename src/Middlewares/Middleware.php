<?php
/**
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Freezer\Middlewares;

use Closure;
use Xpressengine\Plugins\Freezer\Plugin;

class Middleware
{
    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * Middleware constructor.
     *
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $response = $next($request);

        /** @var Closure $action */
        foreach ($this->plugin->getActions() as $action) {
            $response = $action($request, $response);
        }
        return $response;
    }

}
