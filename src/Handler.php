<?php
/**
 *  This file is part of the Xpressengine package.
 *
 * PHP version 5
 *
 * @category
 * @package     Xpressengine\
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */

namespace Xpressengine\Plugins\Freezer;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Xpressengine\Plugins\Freezer\Jobs\FreezeJob;
use Xpressengine\Plugins\Freezer\Jobs\NotifyJob;
use Xpressengine\Plugins\Freezer\Mails\Common;
use Xpressengine\Plugins\Freezer\Models\Log;
use Xpressengine\Plugins\Freezer\Models\User;
use Xpressengine\User\UserHandler;

/**
 * @category
 * @package     Xpressengine\Plugins\Freezer
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class Handler
{
    use DispatchesJobs;

    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * @var UserHandler
     */
    protected $handler;

    /**
     * Handler constructor.
     *
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin, UserHandler $handler)
    {
        $this->plugin = $plugin;
        $this->handler = $handler;
    }

    public function config($field = null, $default = null)
    {
        $config = $this->plugin->config();
        return array_get($config, $field, $default);
    }

    public function choose($action = 'freeze')
    {
        $users = new Collection();
        if ($action === 'freeze') {
            $duration = $this->config('timer');
            $eventDate = Carbon::now()->subDays($duration);
            $users = $this->handler->where('login_at', '<', $eventDate)->get();
        } elseif ($action === 'notify') {
            $duration = $this->config('notify_timer');
            $eventDate = Carbon::now()->subDays($duration);
            $candidates = User::where('login_at', '<', $eventDate)->with(
                [
                    'freeze_logs' => function ($q) {
                        return $q->orderBy('created_at', 'desc');
                    }
                ]
            )->get();

            foreach ($candidates as $user) {
                $latestLog = $user->freeze_logs->first();
                if (data_get($latestLog, 'action') !== 'notify') {
                    $users->add($user);
                }
            }
        }
        return $users;
    }

    /**
     * 휴면처리 대상이 되는 회원에게 예고 이메일 전송하는 잡을 생성하여 Queue에 추가한다.
     *
     * @param null $users
     *
     * @return int
     */
    public function notify($users = null)
    {
        $freezeType = $this->config('freeze_type', 'freeze');
        $size = $this->config('queue_size', 1);
        $queue = $this->config('queue.notify', 'sync');

        if ($users === null) {
            $users = $this->choose('notify');
        }

        $user_ids = [];
        foreach ($users as $user) {
            $user_ids[] = $user->id;

            if (count($user_ids) === $size) {
                $this->dispatch((new NotifyJob($user_ids, $freezeType))->onQueue($queue));
                $user_ids = [];
            }
        }
        if (count($user_ids)) {
            $this->dispatch((new NotifyJob($user_ids, $freezeType))->onQueue($queue));
        }

        return $users->count();
    }

    /**
     * 휴면처리 대상이 되는 모든 회원을 조회후 휴면처리 잡(FreezeJob)을 생성하여 Queue에 추가한다.
     *
     * @param $users
     *
     * @return int
     */
    public function freeze($users = null)
    {
        $freezeType = $this->config('freeze_type', 'freeze');
        $size = $this->config('queue_size', 1);
        $queue = $this->config('queue.freeze', 'sync');

        if ($users === null) {
            $users = $this->choose('freeze');
        }

        $user_ids = [];
        foreach ($users as $user) {
            $user_ids[] = $user->id;

            if (count($user_ids) === $size) {
                $this->dispatch((new FreezeJob($user_ids, $freezeType))->onQueue($queue));
                $user_ids = [];
            }
        }
        if (count($user_ids)) {
            $this->dispatch((new FreezeJob($user_ids, $freezeType))->onQueue($queue));
        }
        return $users->count();
    }

    public function freezeUser($user_ids)
    {
        if (is_string($user_ids)) {
            $user_ids = [$user_ids];
        }

        $users = $this->handler->findMany($user_ids);

        foreach ($users as $user) {
            try {
                $this->moveData('freeze', $user->id);
                $this->sendEmail($user, 'freeze');
            } catch (\Exception $e) {
                $this->logging($user->id, 'freeze', ['message' => $e->getMessage()], 'failed');
                throw $e;
            }
            $this->logging($user->id, 'freeze', $user->toArray(), 'successed');
        }
    }

    public function deleteUser($user_ids)
    {
        if (is_string($user_ids)) {
            $user_ids = [$user_ids];
        }

        $users = $this->handler->findMany($user_ids);

        foreach ($users as $user) {
            try {
                $this->handler->leave($user->id);
                $this->sendEmail($user, 'delete');
            } catch (\Exception $e) {
                $this->logging($user->id, 'delete', ['message' => $e->getMessage()], 'failed');
                throw $e;
            }
            $this->logging($user->id, 'delete');
        }
    }

    public function notifyUser($user_ids)
    {
        if (is_string($user_ids)) {
            $user_ids = [$user_ids];
        }

        $users = $this->handler->findMany($user_ids);

        foreach ($users as $user) {
            try {
                $this->sendEmail($user, 'notify');
            } catch (\Exception $e) {
                $this->logging($user->id, 'notify', ['message' => $e->getMessage()], 'failed');
                throw $e;
            }
            $this->logging($user->id, 'notify');
        }
    }

    public function unfreeze($user_id)
    {
        try {
            $this->moveData('recovery', $user_id);
            $user = $this->handler->find($user_id);
            $this->sendEmail($user, 'unfreeze');
        } catch (\Exception $e) {
            $this->logging($user_id, 'unfreeze', ['message' => $e->getMessage()], 'failed');
            throw $e;
        }
        $this->logging($user->id, 'unfreeze');
    }

    protected function moveData($type, $user_id)
    {
        if ($type === 'freeze') {
            $origin = 'origin';
            $target = 'target';
        } else {
            $origin = 'target';
            $target = 'origin';
        }

        // copy user table
        $table = ['origin' => 'user', 'target' => 'freezer_user'];
        $user = DB::table($table[$origin])->find($user_id);
        DB::table($table[$origin])->delete($user_id);
        DB::table($table[$target])->insert((array) $user);

        // copy user_account table
        $table = ['origin' => 'user_account', 'target' => 'freezer_user_account'];
        $accounts = DB::table($table[$origin])->where('user_id', $user_id)->get();
        DB::table($table[$origin])->where('user_id', $user_id)->delete();
        foreach ($accounts as $account) {
            DB::table($table[$target])->insert((array) $account);
        }

        // copy user_email table
        $table = ['origin' => 'user_email', 'target' => 'freezer_user_email'];
        $emails = DB::table($table[$origin])->where('user_id', $user_id)->get();
        DB::table($table[$origin])->where('user_id', $user_id)->delete();
        foreach ($emails as $email) {
            DB::table($table[$target])->insert((array) $email);
        }

        // copy user_group_user
        $table = ['origin' => 'user_group_user', 'target' => 'freezer_user_group_user'];
        $group_users = DB::table($table[$origin])->where('user_id', $user_id)->get();
        DB::table($table[$origin])->where('user_id', $user_id)->delete();
        foreach ($group_users as $group) {
            DB::table($table[$target])->insert((array) $group);
        }
    }

    protected function logging($user_id, $action, $content = [], $result = 'successd')
    {
        // type = freeze, delete, unfreeze, notify
        $log = new Log();
        $log->user_id = $user_id;
        $log->action = $action;
        $log->result = $result;
        $log->content = $content;
        $log->save();
    }

    protected function sendEmail($user, $type)
    {
        $subject = $this->config("email.$type.subject");

        $content = $this->config("email.$type.content");

        $content = $content($user, $type, $this->config());

        // type = freeze, delete, unfreeze, notify
        $view = $this->plugin->view('views.email');
        $emailAddr = $user->email;
        if ($emailAddr) {
            app('mailer')->to($emailAddr)->queue(new Common($view, $subject, compact('content')));
        }
    }

    public function attempt($credentials = [])
    {

        if (array_has($credentials, 'password')) { // 이메일/비번 로그인
            $email = array_get($credentials, 'email');
            $userInfo = DB::table('freezer_user')->where('email', $email)->first();
            if ($userInfo !== null) {
                $plain = $credentials['password'];
                if (app('hash')->check($plain, $userInfo->password)) {
                    return $userInfo->id;
                }
            }
        } elseif ($credentials instanceof \Laravel\Socialite\AbstractUser) { // 소셜로그인(회원가입)
            $email = $credentials->getEmail();
            $account_id = $credentials->getId();

            // account info가 freeze 되어 있다면 바로 반환
            // email info가 freeze 되어 있다면,
            $accountInfo = DB::table('freezer_user_account')->where('account_id', $account_id)->first();
            if ($accountInfo !== null) {
                return $accountInfo->user_id;
            } elseif ($email !== null) {
                $emailInfo = DB::table('freezer_user_email')->where('address', $email)->first();
                if ($emailInfo !== null) {
                    return $emailInfo->user_id;
                }
            }
        } elseif (array_has($credentials, 'display_name')) { // 이름 검사
            $name = array_get($credentials, 'display_name');
            $userInfo = DB::table('freezer_user')->where('display_name', $name)->first();
            if ($userInfo !== null) {
                return $userInfo->id;
            }
        } elseif (array_has($credentials, 'address')) { // 이메일 검사
            $address = array_get($credentials, 'address');
            $emailInfo = DB::table('freezer_user_email')->where('address', $address)->first();
            if ($emailInfo !== null) {
                return $emailInfo->user_id;
            }
        }

        return null;
    }
}
