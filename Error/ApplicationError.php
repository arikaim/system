<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System\Error;

use Arikaim\Core\System\Error\ErrorHandlerInterface;
use Arikaim\Core\System\Error\Renderer\JsonErrorRenderer;
use Arikaim\Core\System\Error\PhpError;
use Throwable;

/**
 * Application error handler
 */
class ApplicationError implements ErrorHandlerInterface
{  
    /**
     * Html renderer
     *
     * @var ErrorRendererInterface
     */
    protected $htmlRenderer;

    /**
     * Json renderer
     *
     * @var ErrorRendererInterface
     */
    protected $jsonRenderer;

    /**
     * Constructor
     *
     * @param ErrorRendererInterface $htmlRenderer
     * @param boolean $displayErrorDetails
     * @param boolean $displayErrorTrace
     */
    public function __construct(ErrorRendererInterface $htmlRenderer)
    {
        $this->htmlRenderer = $htmlRenderer;
        $this->jsonRenderer = new JsonErrorRenderer();
    }

    /**
     * Render error
     *
     * @param Throwable  $exception The caught Throwable object
     * @param string $renderType   
     * @return string   
     */
    public function renderError(Throwable $exception, string $renderType): string
    {
        switch ($renderType) {
            case ErrorHandlerInterface::JSON_RENDER_TYPE:
                return $this->jsonRenderer->render(PhpError::toArray($exception));                            
        }
        
        return $this->htmlRenderer->render(PhpError::toArray($exception));      
    }
}
