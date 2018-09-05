<h1>
    Hi, {{$data['name']}} !
</h1>
<div>
    Your password in system is: {{$data['pass']}}
</div>
<div>
    Login page is: <a href="{{getenv('APP_DOMAIN')}}">{{getenv('APP_DOMAIN')}}</a>
</div>