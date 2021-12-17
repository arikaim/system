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

use Arikaim\Core\Http\ApiResponse;
use Arikaim\Core\System\Error\ErrorRendererInterface;

/**
 * Render error
 */
class JsonErrorRenderer implements ErrorRendererInterface
{
    /**
     * Api response
     *
     * @var ApiResponse
    */
    protected $response;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->response = new ApiResponse();
    }

    /**
    * Render error
    *
    * @param array $errorDetails 
    * @return string
    */
    public function render(array $errorDetails): string
    {      
        $this->response->setError($errorDetails['message']);
        $this->response->setStatus('error');
        $this->response->setCode($errorDetails['code'] ?? 400);
       
        return $this->response->getResponseJson();
    }
}
