<?php

namespace Xpressengine\Plugins\Freezer\Controllers\Settings;

use App\Http\Controllers\Controller;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\Freezer\Models\Log;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $logs = $this->getLogs($request);

        return \XePresenter::make('freezer::views.settings.log.index', compact('logs'));
    }

    protected function getLogs(Request $request)
    {
        $query = new Log();

        if ($startDate = $request->get('startDate')) {
            $query = $query->where('created_at', '>=', $startDate . ' 00:00:00');
        }

        if ($endDate = $request->get('endDate')) {
            $query = $query->where('created_at', '<=', $endDate . ' 23:59:59');
        }

        $query = $query->orderBy('created_at', 'desc');
        $logs = $query->paginate(10, ['*'], 'page')->appends($request->except('page'));

        return $logs;
    }
}
