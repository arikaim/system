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

/**
 * Render error
 */
class HtmlPageErrorRenderer implements ErrorRendererInterface
{
    /**
     * Page reference
     *
     * @var Arikaim\Core\View\Html\Page
     */
    protected $page;

    /**
     * Constructor
     *
     * @param Page $page
     * @return void
     */
    public function __construct($page)
    {
        $this->page = $page;
    }

    /**
     * Render error
     *
     * @param array $errorDetails
     * @return void
     */
    public function render($errorDetails)
    {           
        try {   
            switch($errorDetails['base_class']) {
                case 'HttpNotFoundException': {                   
                    $output = $this->page->renderPageNotFound(['error' => $errorDetails])->getHtmlCode();
                    break;
                }
                default: {                   
                    $output = $this->page->renderApplicationError(['error' => $errorDetails])->getHtmlCode();                       
                }
            }
        } catch(\Exception $exception) {  
            $output = $this->page->renderApplicationError(['error' => $errorDetails])->getHtmlCode();  
        }

        echo $output;
    }
}
