<?php
/*================================================================
 *  File Name：Driver.php
 *  Author：carlziess, chengmo9292@126.com
 *  Create Date：2016-09-10 17:50:37
 *  Description：
 ===============================================================*/
namespace Auth;
use Encryptions\OpenSSL;
use Utility\Cookies;

abstract class Driver
{
    public $user;
    private $token;
    private $config;

    public function __construct()
    {
        $this->config = (new \Yaf\Config\Ini(APPLICATION_PATH. DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'security.ini'));
        if (is_null($this->token)) {
            $this->token = $this->recall();
        }
        return $this->token;
    }

    abstract public function attempt($arguments = []);

    abstract public function retrieve($token);

    public function guest()
    {
        return !$this->check();
    }

    public function check()
    {
        return !is_null($this->user());
    }

    public function user()
    {
        if (!is_null($this->user)) return $this->user;
        return $this->user = $this->retrieve($this->token);
    }

    public function login($token, $remember = false,$domain='')
    {
        $this->token = $token;
        return $this->remember($token, $remember,$domain);
    }

    public function loginOut()
    {
        $this->user = NULL;
        $this->cookie($this->recaller(), NULL, -2628000);
        $this->token = NULL;
    }

    protected function remember($token, $remember = false,$domain)
    {
        $config = $this->config->secure->cookie;
        $token = OpenSSL::encrypt($token);
        $expire = false === $remember && 1 == $config->expire_on_close ? 0 : ($config->lifetime ? : Cookies::forever);
        return $this->cookie($this->recaller(), $token, $expire, $domain);
    }

    protected function recall()
    {
        $cookie = Cookies::get($this->recaller());
        if (!is_null($cookie)) {
            return OpenSSL::decrypt($cookie);
        }
        return NULL;
    }

    protected function cookie($name, $value, $minutes,$domain = '')
    {
        $config = $this->config->secure->cookie;
        $domainList = explode(',',$config->domain);
        if(!in_array($domain,$domainList)){
            return false;
        }
        return Cookies::put($name, $value, $minutes, $config->path, $domain, $config->secure, $config->httponly);
    }

    protected function getTokenKey()
    {
        return $this->name().'_login';
    }

    protected function recaller()
    {
        return $this->config->secure->cookie->name ? : 'usi';
    }

    protected function name()
    {
        return $this->config->secure->cookie->cookie ? : 'user';
    }

}

