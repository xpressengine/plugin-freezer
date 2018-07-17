<?php
namespace Xpressengine\Plugins\Freezer\Controllers;

use App\Http\Controllers\Controller;
use Xpressengine\Http\Request;
use XePresenter;
use XeSkin;
use Xpressengine\Plugins\Freezer\Handler;
use Xpressengine\Support\Exceptions\XpressengineException;

class UnfreezeController extends Controller
{
    public function __construct(Handler $handler)
    {
        $skinTarget = 'unfreeze/freezer';
        XeSkin::setDefaultSkin($skinTarget, $handler->config('unfreeze_skin_id'));
        XePresenter::setSkinTargetId($skinTarget);
    }

    public function index(Request $request, Handler $handler)
    {
        $frozenId = $request->session()->pull('unfreeze_id');
        if ($frozenId == null) {
            throw new XpressengineException();
        }
        $request->session()->flash('unfreeze_id', $frozenId);

        return XePresenter::make('index', []);
    }

    public function activate(Request $request, Handler $handler)
    {
        $frozenId = $request->session()->pull('unfreeze_id');
        app('freezer::handler')->unfreeze($frozenId);

        return redirect()->to(route('login', ['redirectUrl' => '/']))
            ->with('alert', ['type' => 'info', 'message' => xe_trans('freezer::msgUserAccountActivated')]);
    }
}
