<?php
/**
 * UnfreezeController.php
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

namespace Xpressengine\Plugins\Freezer\Controllers;

use App\Http\Controllers\Controller;
use Xpressengine\Http\Request;
use XePresenter;
use XeSkin;
use Xpressengine\Plugins\Freezer\Handler;
use Xpressengine\Support\Exceptions\XpressengineException;

/**
 * UnfreezeController
 *
 * @category    Freezer
 * @package     Xpressengine\Plugins\Freezer
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class UnfreezeController extends Controller
{
    /**
     * UnfreezeController constructor.
     *
     * @param Handler $handler freezer handler
     */
    public function __construct(Handler $handler)
    {
        $skinTarget = 'unfreeze/freezer';
        XeSkin::setDefaultSkin($skinTarget, $handler->config('unfreeze_skin_id'));
        XePresenter::setSkinTargetId($skinTarget);
    }

    /**
     * index
     *
     * @param Request $request request
     * @param Handler $handler freezer handler
     *
     * @return mixed|\Xpressengine\Presenter\Presentable
     */
    public function index(Request $request, Handler $handler)
    {
        $frozenId = $request->session()->pull('unfreeze_id');
        if ($frozenId == null) {
            throw new XpressengineException();
        }
        $request->session()->flash('unfreeze_id', $frozenId);

        return XePresenter::make('index', []);
    }

    /**
     * active
     *
     * @param Request $request request
     * @param Handler $handler freezer handler
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activate(Request $request, Handler $handler)
    {
        $frozenId = $request->session()->pull('unfreeze_id');
        app('freezer::handler')->unfreeze($frozenId);

        return redirect()->to(route('login', ['redirectUrl' => '/']))
            ->with('alert', ['type' => 'info', 'message' => xe_trans('freezer::msgUserAccountActivated')]);
    }
}
