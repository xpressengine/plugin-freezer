<?php
namespace Xpressengine\Plugins\Freezer\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
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
        $this->handler->notify();
    }
}
