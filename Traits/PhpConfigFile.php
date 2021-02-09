<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System\Traits;

use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Utils;

/**
 * Php Config file loader and writer
 */
trait PhpConfigFile
{
    /**
     * Config array comments
     *
     * @var array
    */
    protected $comments = [];

    /**
     * Include config file
     *  
     * @param string $fileName
     * @return array|null
     */
    public function include(string $fileName): ?array 
    {       
        return (File::exists($fileName) == true) ? include($fileName) : null;             
    }   

    /**
     * Set array key comment
     *
     * @param string $comment
     * @param string $key
     * @return void
     */
    protected function setComment(string $comment, string $key): void
    {
        $this->comments[$key] = $comment;
    }

    /**
     * Get array imtem comment as text
     *
     * @param string $key
     * @return string
     */
    protected function getCommentsText(string $key): string
    {
        return (isset($this->comments[$key]) == true) ? "\t// " . $this->comments[$key] . "\n" : '';
    }

    /**
     * Return config file content
     *
     * @param array $data
     * @return string
     */
    private function getFileContent(array $data): string 
    {   
        $code = $this->getFileContentHeader();
        $code .= $this->exportConfig($data);

        return $code;
    }

    /**
     * Export array as text
     *
     * @param array $data
     * @param string $arrayKey
     * @param int $currentTab
     * @return string
     */
    protected function exportArray(array $data, string $arrayKey, int $currentTab = 1, int $endTab = 1): string
    {     
        $items = '';  
        $maxTabs = $this->determineMaxTabs($data);
        $currentTabs = $this->getTabs($currentTab);
   
        foreach ($data as $key => $value) {
            $items .= (empty($items) == false) ? ",\n" : '';
          
            if (\is_array($value) == true) {                         
                $value = $this->exportArray($value,$key,$currentTab,$currentTab + 1); 
               // $currentTab++;   
                $tabs = $maxTabs - $this->determineTabs($key) + $currentTab;
                $items .= $this->getTabs($tabs) . $value;
            } else {
                $value = Utils::getValueAsText($value);      
                $tabs = $maxTabs - $this->determineTabs($key);  
             
                if (\is_numeric($key) == true) {
                    $items .= $this->getTabs($currentTab + 2) . "$value";
                } else {
                    $items .= $this->getTabs($currentTab + 1) . "'$key'" . $this->getTabs($tabs) . "=> $value";
                }                   
            }
        }
        $comment = $this->getCommentsText($arrayKey);

        $endTabs = $this->getTabs($endTab);

        if (\is_numeric($arrayKey) == true) {
            return "$comment" . $currentTabs . "[\n" . $items . "\n" . $endTabs . "]";
        }

        return "$comment" . $currentTabs . "'" . $arrayKey . "' => [\n" . $items . "\n"  . $endTabs . "]";
    }

    /**
     * Export item as text
     *
     * @param string $key
     * @param mixed $value
     * @param integer $maxTabs
     * @return string
     */
    protected function exportItem(string $key, $value, int $maxTabs): string
    {
        $tabs = $maxTabs - $this->determineTabs($key);
        $value = Utils::getValueAsText($value);

        return "\t'$key'" . $this->getTabs($tabs) . "=> $value";
    }

    /**
     * Export config as text
     *
     * @param array $data
     * @return string
     */
    protected function exportConfig(array $data): string
    {
        $items = '';
        $maxTabs = $this->determineMaxTabs($data);

        foreach ($data as $key => $item) {
            if (\is_array($item) == true) {
                $items .= (empty($items) == false) ? ",\n" : '';
                $items .= $this->exportArray($item,$key,1,1);
            } else {
                $items .= (empty($items) == false) ? ",\n" : '';
                $items .= $this->exportItem($key,$item,$maxTabs);
            }
        }

        return "return [\n $items \n];\n";      
    }

    /**
     * Get config file header
     *
     * @return string
     */
    private function getFileContentHeader(): string 
    {
        $code = "<?php \n/**\n";
        $code .= "* Arikaim\n";
        $code .= "* @link        http://www.arikaim.com\n";
        $code .= "* @copyright   Copyright (c) 2017-" . date('Y') . " Konstantin Atanasov <info@arikaim.com>\n";
        $code .= "* @license     http://www.arikaim.com/license\n";
        $code .= "*/\n\n";

        return $code;
    }

    /**
     * Get max tabs count
     *
     * @param array $data
     * @param integer $tabSize
     * @return integer
     */
    private function determineMaxTabs(array $data, int $tabSize = 4): int
    {
        $keys = [];
        foreach ($data as $key => $value) {
            \array_push($keys,\strlen($key));
        }
        $len = (\count($keys) == 0) ? 1 : \max($keys);

        return \ceil($len / $tabSize);
    }

    /**
     * Get tabs count for array key
     *
     * @param string $key
     * @param integer $tabSize
     * @return integer
     */
    private function determineTabs(string $key, int $tabSize = 4): int
    {
        return \round(\strlen($key) / $tabSize);
    }

    /**
     * Get tabs text
     *
     * @param integer $count
     * @return string
     */
    private function getTabs(int $count): string
    {       
        if ($count <= 0) {
            return ' ';
        }
       
        return \str_repeat("\t",$count);  
    }

    /**
     * Save config file
     *
     * @param string $fileName
     * @param array $data
     * @return bool
    */
    public function saveConfigFile(string $fileName, array $data): bool
    {
        if (File::isWritable($fileName) == false) {
            File::setWritable($fileName);
        }
        $content = $this->getFileContent($data);  
     
        return (bool)File::write($fileName,$content);       
    }
}
