<?php

class Contacts_Yahoo{
    public $consumer_key = 'dj0yJmk9RnpKTjI3MU1wYnJkJmQ9WVdrOVVXWnFWRlJoTm0wbWNHbzlNekU0TURrMU56WXkmcz1jb25zdW1lcnNlY3JldCZ4PTlh';
    public $consumer_sec = '8d6e2cb4089b8222b6333b82aa822c7c5e21da12';  //注册应用时取得的key和sec

    const REQUEST_TOKEN_URL = 'https://api.login.yahoo.com/oauth/v2/get_request_token';
    const REQUEST_AUTH_URL = 'https://api.login.yahoo.com/oauth/v2/request_auth';
    const GET_TOKEN_URL = 'https://api.login.yahoo.com/oauth/v2/get_token';
    const ENDPOINT_URL = 'http://social.yahooapis.com/v1/user/me/contacts';

    public $callback_url;
    private $oauth_token_sec;
    private $oauth;

    public function __construct($callback_url){
        $this->callback_url = $callback_url;
        $this->oauth = new OAuth($this->consumer_key, $this->consumer_sec, OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
    }
    /* public headerUserAuth() {{{ */
    /**  定向到用户授权页面
     * headerUserAuth
     *
     * @access public
     * @return void
     */
    public function headerUserAuth(){
        $request_token_info = $this->oauth->getRequestToken(self::REQUEST_TOKEN_URL, $this->callback_url);
        $this->oauth_token_sec = $request_token_info['oauth_token_secret'];
        $_SESSION['oauth_token_sec'] = $request_token_info['oauth_token_secret'];
        header('Location: '.self::REQUEST_AUTH_URL.'?oauth_token='.$request_token_info['oauth_token']);
    }
    /* public getMailContacts($code) {{{ */
    /** 根据用户授权后拿到的CODE，去拿token，并调用API获取联系人列表
     * getMailContacts
     *
     * @param mixed $code
     * @access public
     * @return 数组 name=>姓名 email=>邮件地址
     */
    public function getMailContacts($oauth_token){
        $this->oauth->setToken($oauth_token, $_SESSION['oauth_token_sec']);
        $access_token_info = $this->oauth->getAccessToken(self::GET_TOKEN_URL);
        $this->oauth->setToken($access_token_info['oauth_token'], $access_token_info['oauth_token_secret']);
        $this->oauth->fetch(self::ENDPOINT_URL, array('format'=>'json', 'count'=>'max'));
        $res = $this->oauth->getLastResponse();
        $json = json_decode($res);

        $result = array();
        foreach($json->contacts->contact as $contact){
            $tmp = array();
            foreach($contact->fields as $field){
                if($field->type == 'name'){
                    $tmp['name'] = $field->value->givenName . $field->value->middleName;
                }else if($field->type == 'email'){
                    $tmp['email'] = $field->value;
                }
            }
            $result[] = $tmp;
        }

        return $result;
    }
}

class Contacts_Gmail{
    public $client_id = '448692193190.apps.googleusercontent.com';
    public $client_secret = 'SF6IW4YBZxNn7EPzWHG_HM__';

    const OAUTH2_TOKEN_URI = 'https://accounts.google.com/o/oauth2/token';
    const OAUTH2_AUTH_URL = 'https://accounts.google.com/o/oauth2/auth';
    const ENDPOINT_URL = 'https://www.google.com/m8/feeds/contacts/default/full/';
    const SCOPE_URL = 'https://www.google.com/m8/feeds/';

    /**
     * callback_url
     *回调地址
     * @var mixed
     * @access public
     */
    public $callback_url;

    public function __construct($callback_url){
        $this->callback_url = $callback_url;

    }

    /* public headerUserAuth() {{{ */
    /**  定向到用户授权页面
     * headerUserAuth
     *
     * @access public
     * @return void
     */
    public function headerUserAuth(){
        $accessTokenUri = self::OAUTH2_AUTH_URL
            . '?client_id=' . $this->client_id
            . '&redirect_uri=' . $this->callback_url
            . '&scope=' . self::SCOPE_URL
            . '&response_type=code';
        header("Location:".$accessTokenUri);
    }
    /* }}} */

    /* public getMailContacts($code) {{{ */
    /** 根据用户授权后拿到的CODE，去拿token，并调用API获取联系人列表
     * getMailContacts
     *
     * @param mixed $code
     * @access public
     * @return 数组 name=>姓名 email=>邮件地址
     */
    public function getMailContacts($code){
        $post_token_param = array(
            'code'=>  urlencode($code),
            'client_id'=>  urlencode($this->client_id),
            'client_secret'=>  urlencode($this->client_secret),
            'redirect_uri'=>  urlencode($this->callback_url),
            'grant_type'=>  urlencode('authorization_code')
        );
        $fields_string = '';
        foreach($post_token_param as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        $fields_string = rtrim($fields_string,'&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::OAUTH2_TOKEN_URI);
        curl_setopt($ch, CURLOPT_POST, count($post_token_param));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        curl_close($ch);
        $response =  json_decode($result);
        if(isset($response->error)){
            return array('result'=>false, 'errno'=>1, 'reason'=>$response->error);
        }

        $accesstoken= $response->access_token;
        $jsonresponse=  file_get_contents('https://www.google.com/m8/feeds/contacts/default/full?alt=json&max-results=1000&oauth_token='.$accesstoken);
        //echo $jsonresponse; exit();
        $res = json_decode($jsonresponse, true);
        $result = array();
        foreach($res['feed']['entry'] as $entry){
              if(!empty($entry['gd$email'])){
                  $tmp['name'] = $entry['title']['$t'];
                  $tmp['email'] = $entry['gd$email'][0]['address'];
                  $result[] = $tmp;
              }
        }
        return $result;
    }
    /* }}} */


}

?>
