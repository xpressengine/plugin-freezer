<?php
/**
 * Middleware.php
 *
 * This file is part of the Xpressengine package.
 *
 * PHP version 5
 *
 * @category    Freezer
 * @package     Xpressengine\Plugins\Freezer
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        http://www.xpressengine.com
 */

namespace Xpressengine\Plugins\Freezer\Middlewares;

use Closure;
use Xpressengine\Plugins\Freezer\Plugin;

/**
 * Middleware
 *
 * @category    Freezer
 * @package     Xpressengine\Plugins\Freezer
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        http://www.xpressengine.com
 */
class Middleware
{
    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * Middleware constructor.
     *
     * @param Plugin $plugin plugin
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request request
     * @param  \Closure                 $next    next middleware
     *
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
