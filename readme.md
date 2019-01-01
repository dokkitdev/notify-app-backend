<h2>Credentials</h2>
<div>configure credentials <a href="https://github.com/aws/aws-sdk-php/issues/887">https://github.com/aws/aws-sdk-php/issues/887</a>:<br>
nano ~/.aws/credentials<br>
contents:<br>
[default]<br>
aws_access_key_id =<br> 
aws_secret_access_key =<br>
</div>

<h2>Queues</h2>
At first U need to install supervisor (<a href="https://laravel.com/docs/5.6/queues#supervisor-configuration">link</a>)<br/>
sudo apt-get install supervisor <br/><br/>

Configuring:<br/>
cd /etc/supervisor/conf.d<br/>
nano laravel-worker.conf<br/>

contents:<br/>
[program:laravel-worker]<br/>
process_name=%(program_name)s_%(process_num)02d<br/>
command=php /var/www/blueflame/artisan queue:work<br/>
autostart=true<br/>
autorestart=true<br/>
user=root<br/>
numprocs=8<br/>
redirect_stderr=true<br/>
stdout_logfile=/var/www/blueflame/storage/logs/worker.log<br/>
<br/>
command - project path<br/>
stdout_logfile - log files path<br/>
user - user from /etc/supervisor/supervisord.conf<br/>
<br/>
sudo supervisorctl reread - response laravel-worker: available <br/>
sudo supervisorctl update - response laravel-worker: added process group<br/>
sudo supervisorctl start laravel-worker:* - start daemon<br/><br/>

ps aux | grep queue:work - to see started processes<br/>


queues
install: sudo apt-get install beanstalkd
run: beanstalkd