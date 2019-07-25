<?php
/**
 * PasswordProtectorController.php
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
namespace Xpressengine\Plugins\Freezer\Controllers;

use App\Http\Controllers\Controller;
use Xpressengine\Http\Request;
use XePresenter;
use XeSkin;
use Illuminate\Contracts\Auth\PasswordBroker;
use App\Events\PreResetUserPasswordEvent;
use Xpressengine\Plugins\Freezer\Handler;
use Xpressengine\User\UserHandler;
use Auth;
use XeDB;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

/**
 * PasswordProtectorController
 *
 * @category    Freezer
 * @package     Xpressengine\Plugins\Freezer
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class PasswordProtectorController extends Controller
{
    use AuthenticatesUsers;

    protected $rules = [
        'current_password' => 'required',
        'password' => 'required|confirmed|password',
    ];

    /**
     * PasswordProtectorController constructor.
     *
     * @param Handler $handler freezer handler
     */
    public function __construct(Handler $handler)
    {
        $skinTarget = 'password_protector/freezer';
        XeSkin::setDefaultSkin($skinTarget, $handler->config('password_protector.skin_id'));
        XePresenter::setSkinTargetId($skinTarget);
    }

    /**
     * index
     *
     * @param Request $request request
     * @param Handler $handler freezer handler
     *
     * @return mixed|\Xpressengine\Presenter\Presentable
     */
    public function index(Request $request, Handler $handler)
    {
        $redirectUrl = $request->get(
            'redirectUrl',
            $request->session()->pull('url.intended') ?: url()->previous()
        );

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

    /**
     * skip
     *
     * @param Request $request request
     * @param Handler $handler freezer handler
     *
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * reset
     *
     * @param Request     $request     request
     * @param Handler     $handler     freezer handler
     * @param UserHandler $userHandler user handler
     *
     * @return \Illuminate\Http\RedirectResponse
     */
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
