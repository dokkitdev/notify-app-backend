<?php

	namespace App\Http\Middleware;

	use Closure;
    use Illuminate\Support\Facades\Auth;

    class CheckLogin{
		/**
		 * Handle an incoming request.
		 *
		 * @return mixed
		 */
		public function handle($request,Closure $next){
			if(!Auth::check()){
				return redirect('/');
			}else return $next($request);
		}
	}
