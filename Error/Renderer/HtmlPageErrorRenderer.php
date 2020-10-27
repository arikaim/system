<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System\Error\Renderer;

use Arikaim\Core\System\Error\ErrorRendererInterface;
use Arikaim\Core\Interfaces\View\HtmlPageInterface;
use Exception;

/**
 * Render error
 */
class HtmlPageErrorRenderer implements ErrorRendererInterface
{
    /**
     * Page reference
     *
     * @var HtmlPageInterface
     */
    protected $page;

    /**
     * Constructor
     *
     * @param SystemErrorInterface $error
     */
    public function __construct(HtmlPageInterface $page)
    {
        $this->page = $page;
    }

    /**
     * Render error
     *
     * @param array $errorDetails
     * @return string
     */
    public function render($errorDetails)
    {                 
        try {   
            switch($errorDetails['base_class']) {
                case 'HttpNotFoundException': {       
                    $output = $this->page->renderPageNotFound($errorDetails)->getHtmlCode();                   
                    break;
                }
                default: {                                   
                    $output = $this->page->renderApplicationError($errorDetails)->getHtmlCode();            
                }
            }
        } catch(Exception $exception) {           
            $output = $this->page->renderApplicationError($errorDetails)->getHtmlCode();  
        }

        return $output;        
    }
}
