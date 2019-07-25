<?php
/**
 * FreezeCommand.php
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

namespace Xpressengine\Plugins\Freezer\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Xpressengine\Plugins\Freezer\Handler;

/**
 * FreezeCommand
 *
 * @category    Freezer
 * @package     Xpressengine\Plugins\Freezer
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class FreezeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'freezer:freeze';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'freeze the users who have not logged in for a long time. ';

    /**
     * @var Handler
     */
    protected $handler;

    /**
     * FreezeCommand constructor.
     *
     * @param Handler $handler freezer handler
     */
    public function __construct(Handler $handler)
    {
        parent::__construct();
        $this->handler = $handler;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $type = $this->handler->config('freeze_type', 'freeze');
        $typeTitle = ['freeze' => 'frozen', 'delete' => 'deleted'][$type];
        $users = $this->handler->choose();

        $count = $users->count();

        $now = Carbon::now();

        if ($count === 0) {
            $this->warn("[{$now->format('Y.m.d H:i:s')}] No users to be frozen.");
            return;
        }

        if ($this->input->isInteractive() && $this->confirm(
            // 총 x명의 회원을 휴면처리 하려고 합니다. 실행하시겠습니까?
                "$count users will be $typeTitle. Do you want to execute it?"
        ) === false) {
            $this->warn('Process is canceled by you.');
            return null;
        }
        $count = $this->handler->freeze($users);

        $this->warn("[{$now->format('Y.m.d H:i:s')}] $count users ware $typeTitle." . PHP_EOL);
    }
}
