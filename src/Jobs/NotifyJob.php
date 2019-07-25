<?php
/**
 * NotifyJob.php
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

namespace Xpressengine\Plugins\Freezer\Jobs;

use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Xpressengine\Plugins\Freezer\Handler;

/**
 * NotifyJob
 *
 * @category    Freezer
 * @package     Xpressengine\Plugins\Freezer
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
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
     * NotifyJob constructor.
     *
     * @param array|string $user_ids user id or user ids
     * @param string       $type     type
     */
    public function __construct($user_ids, $type)
    {
        $this->user_ids = $user_ids;
        $this->type = $type;
    }

    /**
     * handle
     *
     * @param Handler $handler freezer handler
     *
     * @return void
     * @throws \Exception
     */
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
