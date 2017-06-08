<?php
namespace Xpressengine\Plugins\Freezer;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\RedirectResponse;
use Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Xpressengine\Plugin\AbstractPlugin;
use Xpressengine\Plugins\Freezer\Commands\FreezeCommand;
use Xpressengine\Plugins\Freezer\Commands\NotifyCommand;
use Xpressengine\Plugins\Freezer\Commands\UnfreezeCommand;
use Xpressengine\Plugins\Freezer\Middlewares\Middleware;
use Xpressengine\User\Exceptions\DisplayNameAlreadyExistsException;
use Xpressengine\User\Exceptions\EmailAlreadyExistsException;
use Xpressengine\User\UserInterface;

class Plugin extends AbstractPlugin
{

    protected $actions = [];

    /**
     * 이 메소드는 활성화(activate) 된 플러그인이 부트될 때 항상 실행됩니다.
     *
     * @return void
     */
    public function boot()
    {
        $this->register();

        $this->registerMiddleware();

        $this->registerEvents();

        $this->route();

        $schedule = app('Illuminate\Console\Scheduling\Schedule');
        $at = array_get($this->config(), 'scheduled_at');
        if ($at) {
            $schedule->command('freezer:freeze')->dailyAt(array_get($at, 'freeze'))->appendOutputTo('storage/logs/freezer.log');
            $schedule->command('freezer:notify')->dailyAt(array_get($at, 'notify'))->appendOutputTo('storage/logs/freezer.log');
        }
    }

    protected function registerEvents()
    {
        // core login 시도시
        intercept('Auth@attempt', 'freezer::attempt', function($target, array $credentials = [], $remember = false, $login = true){
            $result = $target($credentials, $remember, $login);

            if($result === false) {
                $frozenId = app('freezer::handler')->attempt($credentials);

                if($frozenId !== null) {
                    \DB::beginTransaction();
                    try {
                        app('freezer::handler')->unfreeze($frozenId);
                    } catch (\Exception $e) {
                        \DB::rollBack();
                        throw $e;
                    }
                    \DB::commit();

                    $this->addAction(function($request, $response) {
                        /** @var RedirectResponse $response */
                        return $response->with('alert', ['type' => 'warning', 'message' => '휴면 상태의 계정을 복구후 로그인되었습니다.']);
                    });
                    return $target($credentials, $remember, $login);
                }
            }
            return $result;
        });

        // social_login - login 시도시
        intercept('SocialLoginAuth@login', 'freezer::social_login', function($target, $userInfo){

            $frozenId = app('freezer::handler')->attempt($userInfo);

            if($frozenId !== null) {
                \DB::beginTransaction();
                try {
                    app('freezer::handler')->unfreeze($frozenId);
                } catch (\Exception $e) {
                    \DB::rollBack();
                    throw $e;
                }
                \DB::commit();

                $this->addAction(function($request, $response) {
                    /** @var RedirectResponse $response */
                    return $response->with('alert', ['type' => 'warning', 'message' => '휴면 상태의 계정을 복구후 로그인되었습니다.']);
                });
            }
            return $target($userInfo);
        });

        // social_login - connect 시도시
        intercept('SocialLoginAuth@connect', 'freezer::social_connect', function($target, $userInfo){
            $frozenId = app('freezer::handler')->attempt($userInfo);
            if($frozenId !== null) {
                throw new HttpException('400','이미 다른 회원에 의해 등록된 계정입니다.');
            }
            return $target($userInfo);
        });

        // display name - update 시도시, 휴면상태인 계정에 존재하는지 검사
        intercept(
            'XeUser@validateDisplayName',
            'freezer::validateDisplayName',
            function ($target, $name) {

                $result = $target($name);
                if($result === true) {
                    $frozenId = app('freezer::handler')->attempt(['displayName' => $name]);
                    if($frozenId !== null) {
                        throw new DisplayNameAlreadyExistsException();
                    }
                }
                return $result;

            }
        );

        // email, 휴면상태인 계정에 존재하는지 검사
        intercept(
            'XeUser@validateEmail',
            'freezer::validateEmail',
            function ($target, $email) {
                $result = $target($email);
                if($result === true) {
                    $frozenId = app('freezer::handler')->attempt(['address' => $email]);
                    if($frozenId !== null) {
                        throw new EmailAlreadyExistsException();
                    }
                }
                return $result;
            }
        );

        // email - add 시도시
        intercept(
            'XeUser@createEmail',
            'freezer::createEmail',
            function ($target, UserInterface $user, array $data, $confirmed = true) {

                $frozenId = app('freezer::handler')->attempt($data);

                if($frozenId !== null) {
                    throw new HttpException('400','이미 다른 회원에 의해 등록된 이메일입니다.');
                }
                $result = $target($user, $data, $confirmed);
                return $result;
            }
        );

    }

    protected function registerMiddleware()
    {
        app(\Illuminate\Contracts\Http\Kernel::class)->pushMiddleware(Middleware::class);
    }

    protected function register()
    {
        app()->singleton(
            ['freezer::handler' => Handler::class],
            function ($app) {
                $proxyClass = app('xe.interception')->proxy(Handler::class, 'Freezer');
                return new $proxyClass($this, app('xe.user'));
            }
        );

        // register commands
        app()->singleton(
            'freezer::command.freeze',
            function ($app) {
                return new FreezeCommand(app('freezer::handler'));
            }
        );
        app()->singleton(
            'freezer::command.notify',
            function ($app) {
                return new NotifyCommand(app('freezer::handler'));
            }
        );
        app()->singleton(
            'freezer::command.unfreeze',
            function ($app) {
                return new UnfreezeCommand(app('freezer::handler'));
            }
        );

        $commands = ['freezer::command.notify', 'freezer::command.freeze', 'freezer::command.unfreeze'];
        app('events')->listen(
            'artisan.start',
            function ($artisan) use ($commands) {
                $artisan->resolveCommands($commands);
            }
        );



    }

    public function config() {
        return config('services.freezer');
    }

    protected function route()
    {
        // implement code
    }

    /**
     * addAction
     *
     * @param Closure $action
     *
     * @return void
     */
    public function addAction($action)
    {
        $this->actions[] = $action;
    }

    public function getActions()
    {
        return $this->actions;
    }

    /**
     * 플러그인이 활성화될 때 실행할 코드를 여기에 작성한다.
     *
     * @param string|null $installedVersion 현재 XpressEngine에 설치된 플러그인의 버전정보
     *
     * @return void
     */
    public function activate($installedVersion = null)
    {
        // implement code
    }

    /**
     * 플러그인을 설치한다. 플러그인이 설치될 때 실행할 코드를 여기에 작성한다
     *
     * @return void
     */
    public function install()
    {
        if(!Schema::hasTable('freezer_user')) {
            Schema::create('freezer_user', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->string('id', 36);
                $table->string('displayName', 255)->unique();
                $table->string('email', 255)->nullable();
                $table->string('password', 255)->nullable();
                $table->string('rating', 15)->default('member');
                $table->char('status', 20);
                $table->text('introduction')->default(null)->nullable();
                $table->string('profileImageId', 36)->nullable();
                $table->string('rememberToken', 255)->nullable();
                $table->timestamp('loginAt');
                $table->timestamp('createdAt')->index();
                $table->timestamp('updatedAt')->index();
                $table->timestamp('passwordUpdatedAt');

                $table->primary('id');
            });
        }

        if(!Schema::hasTable('freezer_user_group_user')) {
            Schema::create('freezer_user_group_user', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->increments('id');
                $table->string('groupId', 36);
                $table->string('userId', 36);
                $table->timestamp('createdAt');

                $table->unique(['groupId','userId']);
                $table->index('groupId');
                $table->index('userId');
            });
        }

        if(!Schema::hasTable('freezer_user_account')) {
            Schema::create('freezer_user_account', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->string('id', 36);
                $table->string('userId');
                $table->string('accountId');
                $table->string('email')->nullable();
                $table->char('provider', 20);
                $table->string('token', 500);
                $table->string('tokenSecret', 500);
                $table->string('data');
                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');

                $table->primary('id');
                $table->unique(['provider','accountId']);
            });
        }

        if(!Schema::hasTable('freezer_user_email')) {
            Schema::create('freezer_user_email', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->increments('id');
                $table->string('userId', 36);
                $table->string('address');
                $table->timestamp('createdAt')->index();
                $table->timestamp('updatedAt');

                $table->index('userId');
                $table->index('address');
            });
        }

        if(!Schema::hasTable('freezer_log')) {
            Schema::create('freezer_log', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->increments('id');
                $table->string('userId', 36);
                $table->string('action', 20); // freeze, delete, unfreeze, notify
                $table->string('result', 20); // successd, failed
                $table->string('content');
                $table->timestamp('createdAt')->index();
                $table->timestamp('updatedAt');
                $table->index('userId');
            });
        }
    }

    /**
     * 해당 플러그인이 설치된 상태라면 true, 설치되어있지 않다면 false를 반환한다.
     * 이 메소드를 구현하지 않았다면 기본적으로 설치된 상태(true)를 반환한다.
     *
     * @return boolean 플러그인의 설치 유무
     */
    public function checkInstalled()
    {
        return Schema::hasTable('freezer_user')
               && Schema::hasTable('freezer_user_group_user')
               && Schema::hasTable('freezer_user_account')
               && Schema::hasTable('freezer_user_email')
               && Schema::hasTable('freezer_log');
    }

    /**
     * 플러그인을 업데이트한다.
     *
     * @return void
     */
    public function update()
    {
    }

    /**
     * 해당 플러그인이 최신 상태로 업데이트가 된 상태라면 true, 업데이트가 필요한 상태라면 false를 반환함.
     * 이 메소드를 구현하지 않았다면 기본적으로 최신업데이트 상태임(true)을 반환함.
     *
     * @return boolean 플러그인의 설치 유무,
     */
    public function checkUpdated()
    {
        return true;
    }
}
