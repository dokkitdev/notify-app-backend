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

        <?php
        /**
         * This file is part of PHPWord - A pure PHP library for reading and writing
         * word processing documents.
         *
         * PHPWord is free software distributed under the terms of the GNU Lesser
         * General Public License version 3 as published by the Free Software Foundation.
         *
         * For the full copyright and license information, please read the LICENSE
         * file that was distributed with this source code. For the full list of
         * contributors, visit https://github.com/PHPOffice/PHPWord/contributors.
         *
         * @see         https://github.com/PHPOffice/PHPWord
         * @copyright   2010-2018 PHPWord contributors
         * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
         */
        
        namespace PhpOffice\PhpWord;
        
        use PhpOffice\Common\Text;
        use PhpOffice\PhpWord\Escaper\RegExp;
        use PhpOffice\PhpWord\Escaper\Xml;
        use PhpOffice\PhpWord\Exception\CopyFileException;
        use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
        use PhpOffice\PhpWord\Exception\Exception;
        use PhpOffice\PhpWord\Shared\ZipArchive;
        
        class TemplateProcessor
        {
            const MAXIMUM_REPLACEMENTS_DEFAULT = -1;
        
            /**
             * ZipArchive object.
             *
             * @var mixed
             */
            protected $zipClass;
        
            /**
             * @var string Temporary document filename (with path)
             */
            protected $tempDocumentFilename;
        
            /**
             * Content of main document part (in XML format) of the temporary document
             *
             * @var string
             */
            protected $tempDocumentMainPart;
        
            /**
             * Content of headers (in XML format) of the temporary document
             *
             * @var string[]
             */
            protected $tempDocumentHeaders = array();
        
            /**
             * Content of footers (in XML format) of the temporary document
             *
             * @var string[]
             */
            protected $tempDocumentFooters = array();
        
            /**
             * @since 0.12.0 Throws CreateTemporaryFileException and CopyFileException instead of Exception
             *
             * @param string $documentTemplate The fully qualified template filename
             *
             * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
             * @throws \PhpOffice\PhpWord\Exception\CopyFileException
             */
            public function __construct($documentTemplate)
            {
                // Temporary document filename initialization
                $this->tempDocumentFilename = tempnam(Settings::getTempDir(), 'PhpWord');
                if (false === $this->tempDocumentFilename) {
                    throw new CreateTemporaryFileException();
                }
        
                // Template file cloning
                if (false === copy($documentTemplate, $this->tempDocumentFilename)) {
                    throw new CopyFileException($documentTemplate, $this->tempDocumentFilename);
                }
        
                // Temporary document content extraction
                $this->zipClass = new ZipArchive();
                $this->zipClass->open($this->tempDocumentFilename);
                $index = 1;
                while (false !== $this->zipClass->locateName($this->getHeaderName($index))) {
                    $this->tempDocumentHeaders[$index] = $this->fixBrokenMacros(
                        $this->zipClass->getFromName($this->getHeaderName($index))
                    );
                    $index++;
                }
                $index = 1;
                while (false !== $this->zipClass->locateName($this->getFooterName($index))) {
                    $this->tempDocumentFooters[$index] = $this->fixBrokenMacros(
                        $this->zipClass->getFromName($this->getFooterName($index))
                    );
                    $index++;
                }
                $this->tempDocumentMainPart = $this->fixBrokenMacros($this->zipClass->getFromName($this->getMainPartName()));
            }
        
            /**
             * @param string $xml
             * @param \XSLTProcessor $xsltProcessor
             *
             * @throws \PhpOffice\PhpWord\Exception\Exception
             *
             * @return string
             */
            protected function transformSingleXml($xml, $xsltProcessor)
            {
                libxml_disable_entity_loader(true);
                $domDocument = new \DOMDocument();
                if (false === $domDocument->loadXML($xml)) {
                    throw new Exception('Could not load the given XML document.');
                }
        
                $transformedXml = $xsltProcessor->transformToXml($domDocument);
                if (false === $transformedXml) {
                    throw new Exception('Could not transform the given XML document.');
                }
        
                return $transformedXml;
            }
        
            /**
             * @param mixed $xml
             * @param \XSLTProcessor $xsltProcessor
             *
             * @return mixed
             */
            protected function transformXml($xml, $xsltProcessor)
            {
                if (is_array($xml)) {
                    foreach ($xml as &$item) {
                        $item = $this->transformSingleXml($item, $xsltProcessor);
                    }
                } else {
                    $xml = $this->transformSingleXml($xml, $xsltProcessor);
                }
        
                return $xml;
            }
        
            /**
             * Applies XSL style sheet to template's parts.
             *
             * Note: since the method doesn't make any guess on logic of the provided XSL style sheet,
             * make sure that output is correctly escaped. Otherwise you may get broken document.
             *
             * @param \DOMDocument $xslDomDocument
             * @param array $xslOptions
             * @param string $xslOptionsUri
             *
             * @throws \PhpOffice\PhpWord\Exception\Exception
             */
            public function applyXslStyleSheet($xslDomDocument, $xslOptions = array(), $xslOptionsUri = '')
            {
                $xsltProcessor = new \XSLTProcessor();
        
                $xsltProcessor->importStylesheet($xslDomDocument);
                if (false === $xsltProcessor->setParameter($xslOptionsUri, $xslOptions)) {
                    throw new Exception('Could not set values for the given XSL style sheet parameters.');
                }
        
                $this->tempDocumentHeaders = $this->transformXml($this->tempDocumentHeaders, $xsltProcessor);
                $this->tempDocumentMainPart = $this->transformXml($this->tempDocumentMainPart, $xsltProcessor);
                $this->tempDocumentFooters = $this->transformXml($this->tempDocumentFooters, $xsltProcessor);
            }
        
            /**
             * @param string $macro
             *
             * @return string
             */
            protected static function ensureMacroCompleted($macro)
            {
                if (substr($macro, 0, 2) !== '${' && substr($macro, -1) !== '}') {
                    $macro = '${' . $macro . '}';
                }
        
                return $macro;
            }
        
            /**
             * @param string $subject
             *
             * @return string
             */
            protected static function ensureUtf8Encoded($subject)
            {
                if (!Text::isUTF8($subject)) {
                    $subject = utf8_encode($subject);
                }
        
                return $subject;
            }
        
            /**
             * @param mixed $search
             * @param mixed $replace
             * @param int $limit
             */
            public function setValue($search, $replace, $limit = self::MAXIMUM_REPLACEMENTS_DEFAULT)
            {
                if (is_array($search)) {
                    foreach ($search as &$item) {
                        $item = self::ensureMacroCompleted($item);
                    }
                } else {
                    $search = self::ensureMacroCompleted($search);
                }
        
                if (is_array($replace)) {
                    foreach ($replace as &$item) {
                        $item = self::ensureUtf8Encoded($item);
                    }
                } else {
                    $replace = self::ensureUtf8Encoded($replace);
                }
        
                if (Settings::isOutputEscapingEnabled()) {
                    $xmlEscaper = new Xml();
                    $replace = $xmlEscaper->escape($replace);
                }
        
                $this->tempDocumentHeaders = $this->setValueForPart($search, $replace, $this->tempDocumentHeaders, $limit);
                $this->tempDocumentMainPart = $this->setValueForPart($search, $replace, $this->tempDocumentMainPart, $limit);
                $this->tempDocumentFooters = $this->setValueForPart($search, $replace, $this->tempDocumentFooters, $limit);
            }
        
            /**
             * Returns array of all variables in template.
             *
             * @return string[]
             */
            public function getVariables()
            {
                $variables = $this->getVariablesForPart($this->tempDocumentMainPart);
        
                foreach ($this->tempDocumentHeaders as $headerXML) {
                    $variables = array_merge($variables, $this->getVariablesForPart($headerXML));
                }
        
                foreach ($this->tempDocumentFooters as $footerXML) {
                    $variables = array_merge($variables, $this->getVariablesForPart($footerXML));
                }
        
                return array_unique($variables);
            }
        
            /**
             * Clone a table row in a template document.
             *
             * @param string $search
             * @param int $numberOfClones
             *
             * @throws \PhpOffice\PhpWord\Exception\Exception
             */
            public function cloneRow($search, $numberOfClones)
            {
                if ('${' !== substr($search, 0, 2) && '}' !== substr($search, -1)) {
                    $search = '${' . $search . '}';
                }
        
                $tagPos = strpos($this->tempDocumentMainPart, $search);
                if (!$tagPos) {
                    throw new Exception('Can not clone row, template variable not found or variable contains markup.');
                }
        
                $rowStart = $this->findRowStart($tagPos);
                $rowEnd = $this->findRowEnd($tagPos);
                $xmlRow = $this->getSlice($rowStart, $rowEnd);
        
                // Check if there's a cell spanning multiple rows.
                if (preg_match('#<w:vMerge w:val="restart"/>#', $xmlRow)) {
                    // $extraRowStart = $rowEnd;
                    $extraRowEnd = $rowEnd;
                    while (true) {
                        $extraRowStart = $this->findRowStart($extraRowEnd + 1);
                        $extraRowEnd = $this->findRowEnd($extraRowEnd + 1);
        
                        // If extraRowEnd is lower then 7, there was no next row found.
                        if ($extraRowEnd < 7) {
                            break;
                        }
        
                        // If tmpXmlRow doesn't contain continue, this row is no longer part of the spanned row.
                        $tmpXmlRow = $this->getSlice($extraRowStart, $extraRowEnd);
                        if (!preg_match('#<w:vMerge/>#', $tmpXmlRow) &&
                            !preg_match('#<w:vMerge w:val="continue" />#', $tmpXmlRow)) {
                            break;
                        }
                        // This row was a spanned row, update $rowEnd and search for the next row.
                        $rowEnd = $extraRowEnd;
                    }
                    $xmlRow = $this->getSlice($rowStart, $rowEnd);
                }
        
                $result = $this->getSlice(0, $rowStart);
                for ($i = 1; $i <= $numberOfClones; $i++) {
                    $result .= preg_replace('/\$\{(.*?)\}/', '\${\\1#' . $i . '}', $xmlRow);
                }
                $result .= $this->getSlice($rowEnd);
        
                $this->tempDocumentMainPart = $result;
            }
        
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
            /**
             * Replace a block.
             *
             * @param string $blockname
             * @param string $replacement
             */
            public function replaceBlock($blockname, $replacement)
            {
                preg_match(
                    '/(<\?xml.*)(<w:p.*>\${' . $blockname . '}<\/w:.*?p>)(.*)(<w:p.*\${\/' . $blockname . '}<\/w:.*?p>)/is',
                    $this->tempDocumentMainPart,
                    $matches
                );
        
                if (isset($matches[3])) {
                    $this->tempDocumentMainPart = str_replace(
                        $matches[2] . $matches[3] . $matches[4],
                        $replacement,
                        $this->tempDocumentMainPart
                    );
                }
            }
        
            /**
             * Delete a block of text.
             *
             * @param string $blockname
             */
            public function deleteBlock($blockname)
            {
                $this->replaceBlock($blockname, '');
            }
        
            /**
             * Saves the result document.
             *
             * @throws \PhpOffice\PhpWord\Exception\Exception
             *
             * @return string
             */
            public function save()
            {
                foreach ($this->tempDocumentHeaders as $index => $xml) {
                    $this->zipClass->addFromString($this->getHeaderName($index), $xml);
                }
        
                $this->zipClass->addFromString($this->getMainPartName(), $this->tempDocumentMainPart);
        
                foreach ($this->tempDocumentFooters as $index => $xml) {
                    $this->zipClass->addFromString($this->getFooterName($index), $xml);
                }
        
                // Close zip file
                if (false === $this->zipClass->close()) {
                    throw new Exception('Could not close zip file.');
                }
        
                return $this->tempDocumentFilename;
            }
        
            /**
             * Saves the result document to the user defined file.
             *
             * @since 0.8.0
             *
             * @param string $fileName
             */
            public function saveAs($fileName)
            {
                $tempFileName = $this->save();
        
                if (file_exists($fileName)) {
                    unlink($fileName);
                }
        
                /*
                 * Note: we do not use `rename` function here, because it loses file ownership data on Windows platform.
                 * As a result, user cannot open the file directly getting "Access denied" message.
                 *
                 * @see https://github.com/PHPOffice/PHPWord/issues/532
                 */
                copy($tempFileName, $fileName);
                unlink($tempFileName);
            }
        
            /**
             * Finds parts of broken macros and sticks them together.
             * Macros, while being edited, could be implicitly broken by some of the word processors.
             *
             * @param string $documentPart The document part in XML representation
             *
             * @return string
             */
            protected function fixBrokenMacros($documentPart)
            {
                $fixedDocumentPart = $documentPart;
        
                $fixedDocumentPart = preg_replace_callback(
                    '|\$[^{]*\{[^}]*\}|U',
                    function ($match) {
                        return strip_tags($match[0]);
                    },
                    $fixedDocumentPart
                );
        
                return $fixedDocumentPart;
            }
        
            /**
             * Find and replace macros in the given XML section.
             *
             * @param mixed $search
             * @param mixed $replace
             * @param string $documentPartXML
             * @param int $limit
             *
             * @return string
             */
            protected function setValueForPart($search, $replace, $documentPartXML, $limit)
            {
                // Note: we can't use the same function for both cases here, because of performance considerations.
                if (self::MAXIMUM_REPLACEMENTS_DEFAULT === $limit) {
                    return str_replace($search, $replace, $documentPartXML);
                }
                $regExpEscaper = new RegExp();
        
                return preg_replace($regExpEscaper->escape($search), $replace, $documentPartXML, $limit);
            }
        
            /**
             * Find all variables in $documentPartXML.
             *
             * @param string $documentPartXML
             *
             * @return string[]
             */
            protected function getVariablesForPart($documentPartXML)
            {
                preg_match_all('/\$\{(.*?)}/i', $documentPartXML, $matches);
        
                return $matches[1];
            }
        
            /**
             * Get the name of the header file for $index.
             *
             * @param int $index
             *
             * @return string
             */
            protected function getHeaderName($index)
            {
                return sprintf('word/header%d.xml', $index);
            }
        
            /**
             * @return string
             */
            protected function getMainPartName()
            {
                return 'word/document.xml';
            }
        
            /**
             * Get the name of the footer file for $index.
             *
             * @param int $index
             *
             * @return string
             */
            protected function getFooterName($index)
            {
                return sprintf('word/footer%d.xml', $index);
            }
        
            /**
             * Find the start position of the nearest table row before $offset.
             *
             * @param int $offset
             *
             * @throws \PhpOffice\PhpWord\Exception\Exception
             *
             * @return int
             */
            protected function findRowStart($offset)
            {
                $rowStart = strrpos($this->tempDocumentMainPart, '<w:tr ', ((strlen($this->tempDocumentMainPart) - $offset) * -1));
        
                if (!$rowStart) {
                    $rowStart = strrpos($this->tempDocumentMainPart, '<w:tr>', ((strlen($this->tempDocumentMainPart) - $offset) * -1));
                }
                if (!$rowStart) {
                    throw new Exception('Can not find the start position of the row to clone.');
                }
        
                return $rowStart;
            }
        
            /**
             * Find the end position of the nearest table row after $offset.
             *
             * @param int $offset
             *
             * @return int
             */
            protected function findRowEnd($offset)
            {
                return strpos($this->tempDocumentMainPart, '</w:tr>', $offset) + 7;
            }
        
            /**
             * Get a slice of a string.
             *
             * @param int $startPosition
             * @param int $endPosition
             *
             * @return string
             */
            protected function getSlice($startPosition, $endPosition = 0)
            {
                if (!$endPosition) {
                    $endPosition = strlen($this->tempDocumentMainPart);
                }
        
                return substr($this->tempDocumentMainPart, $startPosition, ($endPosition - $startPosition));
            }
        }
