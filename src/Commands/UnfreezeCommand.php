<?php
/**
 * UnfreezeCommand.php
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

use DB;
use Illuminate\Console\Command;
use Xpressengine\Plugins\Freezer\Handler;

/**
 * UnfreezeCommand
 *
 * @category    Freezer
 * @package     Xpressengine\Plugins\Freezer
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class UnfreezeCommand extends Command
{
    protected $signature = 'freezer:unfreeze {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'unfreeze the user.';

    /**
     * @var Handler
     */
    protected $handler;

    /**
     * UnfreezeCommand constructor.
     *
     * @param Handler $handler freezer handler
     */
    public function __construct(Handler $handler)
    {
        parent::__construct();
        $this->handler = $handler;
    }

    /**
     * handle
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $user_id = $this->argument('user_id');

        $userInfo = DB::table('freezer_user')->find($user_id);

        if ($userInfo === null) {
            $this->warn('the user do not exist in list of frozen users.');
            return;
        }

        if ($this->input->isInteractive() && $this->confirm(
            // 총 x명의 회원을 휴면처리 하려고 합니다. 실행하시겠습니까?
                "'{$userInfo->display_name}' users will be unfreezed. Do you want to execute it?"
        ) === false) {
            $this->warn('Process is canceled by you.');
            return null;
        }

        $users = $this->handler->unfreeze($user_id);
        $this->warn("the user was unfreezed." . PHP_EOL);
    }
}
