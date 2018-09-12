@extends('layouts.single-app')
@section('title', 'Blue Flame | Login')
@section('content')

    <div class="card card-login mx-auto mt-5">
        <div class="card-header">{{ __('Login') }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('login') }}" aria-label="{{ __('Login') }}">
                @csrf
                <div class="form-group">
                    <div class="form-label-group">

                        <input id="inputEmail" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required="required" autofocus="autofocus" placeholder="Email address">

                        <label for="inputEmail">Email address</label>
                    </div>
                    @if ($errors->has('email'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="form-group">
                    <div class="form-label-group">
                        <input id="inputPassword" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required="required" placeholder="Password">
                        <label for="inputPassword">{{ __('Password') }}</label>
                        @if ($errors->has('password'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    <div class="checkbox">

                        <label>
                            <input type="checkbox" value="remember-me" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            {{ __('Remember Me') }}
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    {{ __('Login') }}
                </button>
            </form>
            <div class="text-center">
                {{--<a class="d-block small mt-3" href="register.html">Register an Account</a>--}}
                <a class="d-block small" href="{{ route('password.request') }}">{{ __('Forgot Your Password?') }}</a>
            </div>
        </div>
    </div>

@endsection