<?php
/**
 * Middleware.php
 *
 * This file is part of the Xpressengine package.
 *
 * PHP version 7
 *
 * @category    Freezer
 * @package     Xpressengine\Plugins\Freezer
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
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
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
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
