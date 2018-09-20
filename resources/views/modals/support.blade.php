<div id="support" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="supportLabel" class="modal-title text-align-center">Need support?</h4>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <p>We'll get back to you as soon as we can!</p>
                </div>
                {!! Form::open(['url' => '/support', 'method' => 'POST']) !!}
                <div class="form-group">
                    {!! Form::label('Name', 'Your name:') !!}
                    {!! Form::text('name',Auth::user()->name,['class'=>'form-control','disabled'=>'disabled']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('Email', 'Your email address:') !!}
                    {!! Form::text('email',Auth::user()->email,['class'=>'form-control','disabled'=>'disabled']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('Message', 'Message') !!}
                    {!! Form::textarea('message','',['class'=>'form-control']) !!}
                </div>
                {!! Form::submit('Send', ['class'=>'btn btn-primary float-right']) !!}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>