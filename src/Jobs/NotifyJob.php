<?php
namespace Xpressengine\Plugins\Freezer\Jobs;

use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Xpressengine\Plugins\Freezer\Handler;

/**
 * @category
 * @package     Xpressengine\Plugins\Store\Jobs
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class NotifyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
