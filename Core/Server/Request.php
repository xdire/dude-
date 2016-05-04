<?php
/**
 * Created by Anton Repin.
 * Date: 2/15/16
 * Time: 11:50 AM
 */

namespace Xdire\Dude\Core\Server;

use Xdire\Dude\Core\User\User;

class Request
{
    /** @var null  */
    private $remoteHost = null;
    /** @var string  */
    private $path = null;
    /** @var null  */
    private $pathLast = null;
    /** @var array */
    private $headers = [];
    /** @var array */
    private $parameters = [];
    /** @var array  */
    private $queryParameters = [];
    /** @var string|null  */
    private $contentType = null;
    /** @var int|null  */
    private $contentLength = null;
    /** @var string|null */
    private $postData = null;
    /** @var string|null */
    private $authKey = null;
    /** @var string|null */
    private $authUser = null;
    /** @var string|null */
    private $authAgent = null;
    /** @var int|null */
    private $apiVer = null;
    /** @var User|null */
    private $user = null;
    /** @var bool */
    private $authorized = false;
    /** @var bool */
    private $legit = false;

    function __construct() {
        $this->_setup();
        if(isset($this->headers) && isset($this->remoteHost))
            $this->legit = true;
    }

    private function _setup() {

        // Polyfill function in nginx case
        if (!function_exists('getallheaders'))
        {

            foreach ($_SERVER as $name => $value)
            {
                if (strpos($name, 'HTTP_') === 0) {
                    $name = strtolower(str_replace('_', '-', substr($name, 5)));

                    if($name === 'authkey') {
                        $this->authKey = $value;
                    } elseif ($name === 'authuser') {
                        $this->authUser = $value;
                    } elseif ($name === 'authagent') {
                        $this->authAgent = $value;
                    } elseif ($name === 'useragentver') {
                        $this->apiVer = $value;
                    }
                    else {
                        $this->headers[$name] = $value;
                    }
                }
                elseif($name === 'CONTENT_LENGTH') {
                    $this->contentLength = (int) $value;
                    $this->headers['content-length'] = $value;
                }
                elseif($name === 'CONTENT_TYPE') {
                    $this->contentType = $value;
                    $this->headers['content-type'] = $value;
                }
            }

        }
        // Standard for Apache Web server
        else
        {
            if($this->headers = getallheaders()) {
                $lcHeaders = [];
                foreach($this->headers as $name=>$value) {
                    $name = strtolower($name);
                    if ($name === 'authkey') {
                        $this->authKey = $value;
                    } elseif ($name === 'authuser') {
                        $this->authUser = $value;
                    } elseif ($name === 'authagent') {
                        $this->authAgent = $value;
                    } elseif ($name === 'useragentver') {
                        $this->apiVer = $value;
                    } elseif ($name === 'content-length'){
                        $this->contentLength = (int) $value;
                    } elseif ($name === 'content-type'){
                        $this->contentType = $value;
                    }
                    $lcHeaders[$name] = $value;
                }
                $this->headers = $lcHeaders;
            }
        }

        if(isset($_SERVER['REMOTE_ADDR'])) {
            $this->remoteHost = $_SERVER['REMOTE_ADDR'];
        }

        if($this->contentLength > 0) {
            $p = fopen("php://input", "r");
            $this->postData = stream_get_contents($p);
            fclose($p);
        }

    }

    public function getHeader($header) {
        $l = strtolower($header);
        if(isset($this->headers[$l])) {
            return $this->headers[$l];
        }
        return null;
    }

    /**
     * Return path variable which was assigned with *mark
     *
     * @param   string  $parameter
     * @return  string | null
     */
    public function getPathParameter($parameter){
        if(isset($this->parameters[$parameter])) {
            return $this->parameters[$parameter];
        }
        return null;
    }

    public function getQueryParameter($parameter){
        if(isset($this->queryParameters[$parameter])) {
            return $this->queryParameters[$parameter];
        }
        return null;
    }

    public function getPostParameter($parameter){
        if(isset($this->postData[$parameter])) {
            return $this->postData[$parameter];
        }
        return null;
    }

    public function __setPathLastElement($string) {
        $this->pathLast = $string;
    }

    public function __setPath($string) {
        $this->path = $string;
    }

    public function __setQueryParameters($queryParameters){
        $this->queryParameters = $queryParameters;
    }

    public function __setPathParameter($name,$value){
        $this->parameters[$name]=$value;
    }
    public function __setPathParameters($pathParameters){
        $this->parameters = $pathParameters;
    }

    /**
     * @param User $userObject
     */
    public function __setUser(User $userObject) {
        $this->user = $userObject;
        if($userObject->isAuthorized()){
            $this->authorized = true;
        }
    }

    /**
     * VOID
     */
    public function __setAuthorized()
    {
        $this->authorized = true;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return null
     */
    public function getRemoteHost()
    {
        return $this->remoteHost;
    }

    /**
     * @return null
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return null
     */
    public function getPathLast()
    {
        return $this->pathLast;
    }

    /**
     * @return null
     */
    public function getPostData()
    {
        return $this->postData;
    }

    /**
     * @return null
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @return null
     */
    public function getAuthUser()
    {
        return $this->authUser;
    }

    /**
     * @return null
     */
    public function getApiVer()
    {
        return $this->apiVer;
    }

    /**
     * @return boolean
     */
    public function isAuthorized()
    {
        return $this->authorized;
    }

    /**
     * @return null|string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @return int|null
     */
    public function getContentLength()
    {
        return $this->contentLength;
    }

    /**
     * @return null|string
     */
    public function getAuthAgent()
    {
        return $this->authAgent;
    }

    /**
     * @return boolean
     */
    public function isLegit()
    {
        return $this->legit;
    }


}
