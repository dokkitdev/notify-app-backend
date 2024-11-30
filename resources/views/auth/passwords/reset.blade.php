@extends('auth.layout')
@section('title', 'Notify | Reset Password')
@section('body-class', 'authentication-bg authentication-bg-pattern')
@section('content')
    <div class="account-pages mt-5 mb-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card bg-pattern">
                        <div class="card-body p-4">
                            <div class="text-center w-75 m-auto">
                                <h4>{{ __('Reset Password') }}</h4>
                            </div>
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif
                            <form method="POST" action="{{ route('password.request') }}"
                                  aria-label="{{ __('Reset Password') }}">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">
                                <div class="form-group">
                                    <div class="form-label-group">
                                        <label for="inputEmail">Email address</label>
                                        <input id="inputEmail" type="email"
                                               class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                               name="email"
                                               value="{{ old('email') }}" required="required" autofocus="autofocus"
                                               placeholder="Email address">
                                    </div>
                                    @if ($errors->has('email'))
                                        <div class="alert alert-danger mt-1">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <div class="form-label-group">
                                        <label for="password">{{ __('Password') }}</label>
                                        <input id="password" type="password"
                                               class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                               name="password"
                                               required="required" autofocus="autofocus"
                                               placeholder="{{ __('Password') }}">
                                    </div>
                                    @if ($errors->has('password'))
                                        <div class="alert alert-danger mt-1">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </div>
                                    @endif
                                </div>

                                <div class="form-group mb-3">
                                    <div class="form-label-group">
                                        <label for="password_confirmation">{{ __('Confirm Password') }}</label>
                                        <input id="password_confirmation" type="password" class="form-control"
                                               name="password_confirmation" required="required" autofocus="autofocus"
                                               placeholder="{{ __('Confirm Password') }}">
                                    </div>
                                </div>


                                <button type="submit" class="btn btn-primary btn-block">
                                    {{ __('Send Password Reset Link') }}
                                </button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
