<?php 
// Declaring controller namespace
namespace App\Helpers;
//Aliasing psr-4 autoloaded class
use Illuminate\Support\Facades\Response;

class ApiHelper {
    public $statusCode = 200;
    
    public function getStatusCode() {
        return $this->statusCode;
    }
    
    /**
     * 
     * @param type $statusCode
     * @return \App\Http\Controllers\ApiController
     */
    
    public function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;
        
        return $this;
    }
    
    /**
     * 
     * @param type $message
     */
    
    public function respondNotFound($message = 'Not Found.') {
        return $this->setStatusCode(404)->respondWithError($message);
    }
    
    /**
     * 
     * @param type $data
     * @param type $headers
     * @return type
     */

    public function respond ($data, $headers = []) {
        return Response::json($data, $this->getStatusCode(), $headers);
    }
  
    /**
     * 
     * @param type $message
     */
    
    public function respondWithError($message) {
        return $this->respond([
                'error' => 
                    [   
                        'message' => $message,
                        'status_code' => $this->getStatusCode()
                    ]
            ]);        
    }
       
}

