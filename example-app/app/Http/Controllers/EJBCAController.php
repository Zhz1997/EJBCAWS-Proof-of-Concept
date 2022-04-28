<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SoapClient;
use Throwable;

class extendedInformationWS
{
    public $name;
    public $value;
}
    
class userDataVOWS
{
    public $caName;
    public $cardNumber;
    public $certificateProfileName;
    public $certificateSerialNumber;
    public $clearPwd;
    public $email;
    public $endEntityProfileName;
    public $endTime;
    public $extendedInformation;
    public $hardTokenIssuerName;
    public $keyRecoverable;
    public $password;
    public $sendNotification;
    public $startTime;
    public $status;
    public $subjectAltName;
    public $subjectDN;
    public $tokenType;
    public $username;
}

class EJBCAController
{
    /*
    |--------------------------------------------------------------------------
    | EJBCAController Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles generating a certificate for a user. 
    | If the user is not already present in the database it will be 
    | added otherwise it will be overwritten.
    |
    */

    private $wsdl = "https://localhost:443/ejbca/ejbcaws/ejbcaws?wsdl";
    private $ssl_cert;//
    private $admin_cert;
    private $classmap;
    private $lParams;
    public function __construct(){
        global   $classmap;
        $this->classmap = array(
            'userDataVOWS' => 'userDataVOWS',
            'extendedInformationWS' => 'extendedInformationWS'
            );

        global   $ssl_cert;
        global   $admin_cert;
        global   $lParams;

        $this->ssl_cert = dirname(__FILE__) . '/ssl.pem';//
        $this->admin_cert = dirname(__FILE__) . '/admin.pem';

        $this->lParams = [
            'trace' => 1, 
            'cache_wsdl' => 'WSDL_CACHE_NONE',
            'exceptions' => 1,
            'local_cert' => $this->admin_cert,
            'passphrase' => 'abc123',
            'classmap' => $this->classmap,
            'stream_context' => stream_context_create([
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name'  => true,
                    'allow_self_assign' => false,
                    'cafile' => $this->ssl_cert
                ],
            ]),
        ];
    }

    // /**
    //  * verify the EJBCA Web Service
    //  * @param string Request $request which includes the UserName and PublicKey
    //  * @return string 
    //  */
    // public function test(Request $request)
    // {
    //     return "awsdfasdf";

    // }


    /**
     * Get username and publickey from front-end
     * @param string Request $request which includes the UserName and PublicKey
     * @return http status code
     */
    public function certReq(Request $request)
    {
        // requests the EJBCA Web Service, 
        // returns a certificate for a user
        $resultinfo = $this->requestCertificatetEJBCAWS($request);
        if ($resultinfo){
            $response_info = [ 'Good request ' => trans('201') ];
            return response()->json($response_info, 201); 
        }else{
            $response_error = [ 'Bad request ' => trans('400') ];
            return response()->json($response_error, 400); 
        }
    }
    

    /**
     * Create a dummy user instance.
     * @param string input UserName and PublicKey
     * @return userDataVOWS $userData
     */
    private function createUser(Request $request) {
        $extendedInformation = new extendedInformationWS();
        $extendedInformation->name = "subjectdirattributes";
        $extendedInformation->value = "";
    
        $userData = new userDataVOWS();
    
        $userData->username = $request->input('username');
        $userData->caName = "ManagementCA";
        $userData->endEntityProfileName = "EMPTY";
        $userData->certificateProfileName = "ENDUSER";
        $userData->tokenType = "USERGENERATED";
        $userData->subjectAltName = null;
        $userData->clearPwd = FALSE;
        $userData->keyRecoverable = FALSE;
        $userData->status = 10;
        $userData->subjectDN = "CN=".$request->input('username');
        $userData->email = null;
        $userData->password = "abc123";
        $userData->extendedInformation = $extendedInformation;
        
        return $userData;
    }
    


    /**
     * Generates a certificate for a user by calling certificateRequest in EJBCA Web Service
     * @param mixed Request $request
     * @param mixed $nFlag: 1 to request EJBCA WS, 2 to verify the EJBCA WS
     * @return boolean TRUE or FALSE
     */
    private function requestCertificatetEJBCAWS(Request $request)
    {
        // creat the input params as the sub params of SoapClient function.
        $inputParams = array(
            'arg0' => $this->createUser($request),
            'arg1' => $request->input('publickey'), //
            'arg2' => 3,
            'arg3' => null,
            'arg4' => 'CERTIFICATE'
        );
        

        try{
            global  $wsdl;
            global  $lParams;

            // Returns: the generated certificate, in either just X509Certificate or PKCS7
            $soapclient = new SoapClient( $this->wsdl, $this->lParams);//this->
            $response = null;
            $response = $soapclient->certificateRequest($inputParams);

            if($response){
                return true;
            }else{
                return false;
            }
        }catch(Throwable $e){
            return false;
        }
    }

    /**
     * verify the EJBCA Web Service
     * @param string Request $request which includes the UserName and PublicKey
     * @return boolean returns good(TRUE) or bad(FALSE) service
     */
    public function verify(Request $request)
    {

        global  $wsdl;
        global  $lParams;

        $soapclient = new SoapClient($this->wsdl, $this->lParams);
        $response = $soapclient->getEjbcaVersion();
    
        if ($response){
            $response_info = [ 'Server is good' => trans('200') ];
            return response()->json($response_info, 200); 
        }else{
            $response_error = [ 'Server is bad' => trans('500') ];
            return response()->json($response_error, 500); 
        }


    }

}