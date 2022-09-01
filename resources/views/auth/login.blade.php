@extends('auth.layout')
@section('title', 'Blue Flame | Login')
@section('body-class', 'authentication-bg authentication-bg-pattern')
@section('content')
    <div class="account-pages mt-5 mb-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card bg-pattern">

                        <div class="card-body p-4">
                            <div class="text-center w-75 m-auto">
                                <h4>Login</h4>
                            </div>

                            <form action="{{ route('login') }}" aria-label="{{ __('Login') }}" method="post">
                                @csrf
                                <div class="form-group">
                                    <label for="inputEmail">Email address</label>
                                    <input id="inputEmail" type="email"
                                           class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                           name="email" value="{{ old('email') }}" required="required"
                                           autofocus="autofocus">
                                    @if ($errors->has('email'))
                                        <div class="alert alert-danger mt-1">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="inputPassword">{{ __('Password') }}</label>
                                    <div class="input-group input-group-merge">
                                        <input id="inputPassword" type="password"
                                               class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                               name="password"
                                               required="required">
                                        <div class="input-group-append" data-password="false">
                                            <div class="input-group-text">
                                                <span class="password-eye font-12"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($errors->has('password'))
                                        <div class="alert alert-danger mt-1">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="_remember_me"
                                               id="checkbox-signin" value="remember-me"
                                            checked>
                                        <label class="custom-control-label"
                                               for="checkbox-signin">{{ __('Remember Me') }}</label>
                                    </div>
                                </div>

                                <div class="form-group mb-0 text-center">
                                    <button class="btn btn-primary btn-block" type="submit">{{ __('Login') }}</button>
                                </div>
                                <div class="text-center mt-2">
                                    <a class="d-block small"
                                       href="{{ route('password.request') }}">{{ __('Forgot Your Password?') }}</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="footer footer-alt text-white-50">
        2018 -
        <script>document.write(new Date().getFullYear())</script> &copy; Notify app by <a href="https://dokkit.co.uk"
                                                                                          class="text-white-50">Dokkit</a>
    </footer>
@endsection
