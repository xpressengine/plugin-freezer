<?php
namespace Xpressengine\Plugins\Freezer\Commands;

use DB;
use Illuminate\Console\Command;
use Xpressengine\Plugins\Freezer\Handler;

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
     * Create a new command instance.
     *
     * @return void
     */

    public function __construct(Handler $handler)
    {
        parent::__construct();
        $this->handler = $handler;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $user_id = $this->argument('user_id');

        $userInfo = DB::table('freezer_user')->find($user_id);

        if($userInfo === null) {
            $this->warn('the user do not exist in list of frozen users.');
            return;
        }

        if ($this->input->isInteractive() && $this->confirm(
                // 총 x명의 회원을 휴면처리 하려고 합니다. 실행하시겠습니까?
                "'{$userInfo->display_name}' users will be unfreezed. Do you want to execute it?"
            ) === false
        ) {
            $this->warn('Process is canceled by you.');
            return null;
        }

        $users = $this->handler->unfreeze($user_id);
        $this->warn("the user was unfreezed.".PHP_EOL);
    }
}
