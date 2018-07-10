<?php
namespace Xpressengine\Plugins\Freezer\Controllers;

use App\Http\Controllers\Controller;
use Xpressengine\Http\Request;
use XePresenter;
use Illuminate\Contracts\Auth\PasswordBroker;
use App\Events\PreResetUserPasswordEvent;
use Xpressengine\Plugins\Freezer\Handler;
use Xpressengine\User\UserHandler;
use Auth;
use XeDB;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class PasswordProtectorController extends Controller
{
    use AuthenticatesUsers;

    protected $rules = [
        'current_password' => 'required',
        'password' => 'required|confirmed|password',
    ];
    public function __construct()
    {
        XePresenter::setSkinTargetId('password_protector/freezer');
    }

    public function index(Request $request, Handler $handler)
    {
        $redirectUrl = $request->get('redirectUrl',
            $request->session()->pull('url.intended') ?: url()->previous());

        if ($redirectUrl !== $request->url()) {
            $request->session()->put('url.intended', $redirectUrl);
        }

        $user = Auth::user();
        $userName = $user->getDisplayName();

        $config = $handler->config('password_protector');
        $timer = $config['timer'];
        $nextCheckTimer = $config['next_check_timer'];

        return XePresenter::make('index', [
            'user' => $user,
            'config' => $config,
            'userName' => $userName,
            'token' => '토큰',
            'timer' => $timer,
            'nextCheckTimer' => $nextCheckTimer,
            'rules' => $this->rules,
        ]);
    }

    public function skip(Request $request, Handler $handler)
    {
        $user = Auth::user();
        $handler->passwordProtectSkip($user->getId(), $user->email);

        if ($handler->config('password_protector.send_email') == true) {
            send_notice_email(
                $user->email,
                xe_trans('freezer::pleaseChangePasswordPeriodically'),
                xe_trans('freezer::descriptionChangePasswordPeriodically'),
                function ($notifiable) {
                    $applicationName = xe_trans(app('xe.site')->getSiteConfig()->get('site_title'));

                    $subject = sprintf(
                        '[%s] %s',
                        $applicationName,
                        xe_trans('freezer::pleaseChangePasswordPeriodically')
                    );
                    return $subject;
                }
            );
        }

        return redirect()
            ->intended($this->redirectPath());
    }

    public function reset(Request $request, Handler $handler, UserHandler $userHandler)
    {
        $user = Auth::user();

        $this->validate($request, $this->rules);

        if ($user->getAuthPassword() !== "") {

            $credentials = [
                'id' => $user->getId(),
                'password' => $request->get('current_password')
            ];

            if (Auth::validate($credentials) === false) {
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->with('alert', ['type' => 'danger', 'message' => xe_trans('xe::currentPasswordIncorrect')]);
            }
        }

        $password = $request->get('password');


        XeDB::beginTransaction();
        $user = $userHandler->update($request->user(), compact('password'));
        $handler->dropPasswordProtectSkip($user->getId());
        XeDB::commit();

        return redirect()
            ->intended($this->redirectPath());
    }
}
