<?php
namespace Xpressengine\Plugins\Freezer\Jobs;

use App\Jobs\Job;
use DB;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Xpressengine\Plugins\Freezer\Handler;

/**
 * @category
 * @package     Xpressengine\Plugins\Store\Jobs
 * @author      XE Team (khongchi) <khongchi@xpressengine.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class NotifyJob extends Job implements SelfHandling, ShouldQueue
{
    /**
     * @var string|array
     */
    private $user_ids;

    /**
     * @var string
     */
    private $type;

    /**
     * FreezeJob constructor.
     *
     * @param string|array $user_ids
     * @param $type
     */
    public function __construct($user_ids, $type)
    {
        $this->user_ids = $user_ids;
        $this->type = $type;
    }

    public function handle(Handler $handler)
    {
        DB::beginTransaction();
        try {
            $handler->notifyUser($this->user_ids);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
    }
}
