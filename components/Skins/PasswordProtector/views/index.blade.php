{{
XeFrontend::css(
    [
        'assets/core/xe-ui-component/xe-ui-component.css',
        'assets/core/user/auth.css'
    ]
)->load()
}}

<div class="user find-password">
    <h1>{{xe_trans('freezer::changePasswordInfo')}}</h1>

    <p class="sub-text">{{xe_trans('freezer::changePasswordDescription')}}</p>

    <p class="sub-text">{{xe_trans('freezer::skipDescription')}}</p>

    <form role="form" method="POST" action="{{ route('freezer::password_protector.reset') }}" data-rule="reset">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <fieldset>
            <legend>{{xe_trans('xe::password')}}</legend>
            <div class="auth-group {{--wrong--}}">
                <label for="current_password" class="sr-only">{{xe_trans('freezer::currentPassword')}}</label>
                <input type="password" id="current_password" class="xe-form-control" placeholder="{{xe_trans('freezer::currentPassword')}}" name="current_password" value="">
            </div>
            <div class="auth-group {{--wrong--}}">
                <label for="pwd" class="sr-only">{{xe_trans('freezer::newPassword')}}</label>
                <input type="password" id="pwd" class="xe-form-control" placeholder="{{xe_trans('xe::password')}}" name="password">
                <button class="btn-eye on" style="display:none"><i class="xi-eye"></i><i class="xi-eye-off"></i>
                </button>
            </div>
            <div class="auth-group {{--wrong--}}">
                <label for="pwd2" class="sr-only">{{xe_trans('freezer::newPasswordConfirm')}}</label>
                <input type="password" id="pwd2" class="xe-form-control" placeholder="{{xe_trans('xe::passwordConfirm')}}" name="password_confirmation" data-valid-name="{{xe_trans('freezer::newPasswordConfirm')}}">
                <button class="btn-eye on"><i class="xi-eye"></i><i class="xi-eye-off"></i></button>
            </div>

            <a type="button" class="xe-btn xe-btn-primary" href="{{ route('freezer::password_protector.skip') }}">{{xe_trans('freezer::skipChange')}}</a>
            <button type="submit" class="xe-btn xe-btn-primary">{{xe_trans('xe::changePassword')}}</button>
        </fieldset>
    </form>
</div>
