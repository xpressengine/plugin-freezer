{{
XeFrontend::css(
    [
        'assets/core/xe-ui-component/xe-ui-component.css',
        'assets/core/user/auth.css'
    ]
)->load()
}}

<div class="user find-password">
    <h1>{{xe_trans('freezer::activateUserAccount')}}</h1>

    <p class="sub-text">{{xe_trans('freezer::descriptionActivationUserAccount')}}</p>

    <form role="form" method="POST" action="{{ route('freezer::unfreeze.activate') }}" data-rule="reset">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <button type="submit" class="xe-btn xe-btn-primary">{{xe_trans('freezer::activateUserAccount')}}</button>
    </form>
</div>
