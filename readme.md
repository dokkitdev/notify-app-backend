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



for PHP Word start to wok Make changes in vendor
/vendor/phpoffice/phpword/src/PhpWord/TemplateProcessor.php
/**
     * Clone a block.
     *
     * @param string $blockname
     * @param int $clones How many time the block should be cloned
     * @param bool $replace
     * @param bool $indexVariables If true, any variables inside the block will be indexed (postfixed with #1, #2, ...)
     * @param array $variableReplacements Array containing replacements for macros found inside the block to clone
     *
     * @return string|null
     */
    public function cloneBlock($blockname, $clones = 1, $replace = true, $indexVariables = false, $variableReplacements = null)
    {
        $xmlBlock = null;

        $matches = array();
        list($matches[1],$matches[2],$matches[3]) = $this->getBlocks($blockname);
        if (isset($matches[2])&&$matches[2]) {

            $xmlBlock = $matches[2];
            if ($indexVariables) {
                $cloned = $this->indexClonedVariables($clones, $xmlBlock);
            } elseif ($variableReplacements !== null && is_array($variableReplacements)) {
                $cloned = $this->replaceClonedVariables($variableReplacements, $xmlBlock);
            } else {
                $cloned = array();
                for ($i = 1; $i <= $clones; $i++) {
                    $cloned[] = $xmlBlock;
                }
            }

            if ($replace) {
                $this->tempDocumentMainPart = str_replace(
                    $matches[1] . $matches[2] . $matches[3],
                    implode('', $cloned),
                    $this->tempDocumentMainPart
                );
            }
        }

        return $xmlBlock;
    }
    /**
     * Get part of block for cloneBlock
     *
     * @param string $blockName Block name to clone
     *
     * @return array
     */
    private function getBlocks($blockName){
        $dataXML = $this->tempDocumentMainPart;
        if(stripos($dataXML,'{'.$blockName.'}') && stripos($dataXML,'{/'.$blockName.'}')){
            $startBlock1 = strrpos(substr($dataXML,0,stripos($dataXML,'{'.$blockName.'}')), '<w:p ');
            $lengthBlock1 = (stripos(substr($dataXML,$startBlock1),'p>')+2);
            $block1 = substr($dataXML,$startBlock1,$lengthBlock1);
            $startBlock3 = strrpos(substr($dataXML,0,stripos($dataXML,'{/'.$blockName.'}')), '<w:p ');
            $lengthBlock3 = (stripos(substr($dataXML,$startBlock3),'p>')+2);
            $block3 = substr($dataXML,$startBlock3,$lengthBlock3);
            $block2 = substr($dataXML,$startBlock1+$lengthBlock1,$startBlock3-($startBlock1+$lengthBlock1));
            return array($block1, $block2, $block3);
        }
        return array(0,0,0);
    }