<?php
namespace Xpressengine\Plugins\Freezer\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Xpressengine\Plugins\Freezer\Handler;

class NotifyCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'freezer:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'notify to the users who will be frozen.';
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
        $type = $this->handler->config('freeze_type', 'freeze');
        $users = $this->handler->choose('notify');

        $count = $users->count();
        $now = Carbon::now();

        if ($count === 0) {
            $this->warn("[{$now->format('Y.m.d H:i:s')}] No users to be notified about freezing.");
            return;
        }

        if ($this->input->isInteractive() && $this->confirm(
            // x 명의 회원에게 이메일을 보내려 합니다. 실행하시겠습니까?
                "Emails will be sent to $count users. Do you want to execute it?"
            ) === false
        ) {
            $this->warn('Process is canceled by you.');
            return null;
        }
        $count = $this->handler->notify($users);

        $this->warn(
            "[{$now->format('Y.m.d H:i:s')}] Emails were sent to $count users for notify about freeze.".PHP_EOL
        );
    }
}
