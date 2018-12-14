<?php
/**
 * Plugin.php
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

namespace Xpressengine\Plugins\Freezer\Jobs;

use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Xpressengine\Plugins\Freezer\Handler;

/**
 * FreezeJob
 *
 * @category    Freezer
 * @package     Xpressengine\Plugins\Freezer
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        http://www.xpressengine.com
 */
class FreezeJob implements ShouldQueue
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
     * @param array|string $user_ids user id or user ids
     * @param string       $type     string
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
            if ($this->type === 'freeze') {
                $handler->freezeUser($this->user_ids);
            } else {
                $handler->deleteUser($this->user_ids);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
    }
}
