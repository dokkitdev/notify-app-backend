<h1>
    Hi, {{$data['name']}} !
</h1>
<div>
    Your password <a href="{{ route('password.reset', ['token' => $data['token']]) }}" title="Reset password link">resetting link</a>.
</div>
<div>
    If you did not request a change of password, please ignore this e-mail
</div>
