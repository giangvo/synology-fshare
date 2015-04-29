<?php
/* @author: zang_itu@yahoo.com
 * @version: 0.1 */

class SynoFileHostingFshareVN {
    private $Url;
    private $Username;
    private $Password;
    private $HostInfo;
    private $COOKIE_JAR = '/tmp/fsharevn.cookie';
    private $LOG_FILE = '/tmp/fsharevn.log';
    private $LOGIN_URL = 'https://www.fshare.vn/login';
    private $FSHARE_HOME = 'https://www.fshare.vn/home';
    private $FSHARE_URL = 'https://www.fshare.vn';
    
    public function __construct($Url, $Username, $Password, $HostInfo) {
        if(strpos($Url,'http://') !== FALSE){
            $Url = str_replace("http://", "https://", $Url);
        }else{
            if(strpos($Url,'https://') === FALSE){
                $Url = "https://" . $Url;
            }
        }

        $this->Url = $Url;
        $this->Username = $Username;
        $this->Password = $Password;

        /*$this->Username = "zang_itu@yahoo.com";
        $this->Password = "asd123";*/

        $this->AppId = "GUxft6Beh3Bf8qKP7GC2IplYJZz1A53JQfRwne0R";

        $this->HostInfo = $HostInfo;

        ini_set('max_execution_time', 300);
    }
    
    public function Verify($ClearCookie) {
        if(file_exists($this->COOKIE_JAR)) {
            unlink($this->COOKIE_JAR);
        }
            
        return $this->performLogin();
    }
    
    public function GetDownloadInfo() {
        $this->Verify(FALSE);

        $DownloadInfo = array();

        $downloadUrl = $this->getLink();
        
        if($downloadUrl === "error") {
            $DownloadInfo[DOWNLOAD_ERROR] = $downloadUrl;   
        }
        else
        {
            $DownloadInfo[DOWNLOAD_URL] = $downloadUrl;
        }

        return $DownloadInfo;

    }
    
    private function performLogin() {
        $ret = LOGIN_FAIL;

        $this->logInfo("Start login===");

        $service_url = 'https://api2.fshare.vn/api/user/login';
        $curl = curl_init($service_url);
        $data = array(
            "app_key" => $this->AppId,
            "password" => $this->Password,
            "user_email" => $this->Username
        );

        $data_string = json_encode($data);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIEJAR, $this->COOKIE_JAR);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            'Content-Length: ' . strlen($data_string)
        ));
        
        $curl_response = curl_exec($curl);

        if($curl_response === false)
        {
            $this->logError("Login error: " . curl_error($curl));
        }
        else
        {
            $this->Token = json_decode($curl_response)->{'token'};
            $this->logInfo("Login ok: " . $curl_response);
    
            $ret = USER_IS_PREMIUM;;
        }

        $this->logInfo("End login===");

        curl_close($curl);

        return $ret;
        
    }

    private function getLink() {

        $ret = "error";

        $this->logInfo("Start Get Link: " . $this->Url . " ===");

        $service_url = 'https://api2.fshare.vn/api/session/download';

        $curl = curl_init($service_url);
        $data = array(
            "password" => "",
            "token" => $this->Token,
            "url" => $this->Url,
            "zipflag" => false
        );

        $data_string = json_encode($data);


        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $this->COOKIE_JAR);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            'Content-Length: ' . strlen($data_string)
        ));
        
        $curl_response = curl_exec($curl);

        if($curl_response === false)
        {
            $this->logError("Login error: " . curl_error($curl));
        }
        else
        {
            $downloadUrl = json_decode($curl_response)->{'location'};

            $this->logInfo("Get link ok: " . $curl_response);

            $ret = $downloadUrl;
        }

        $this->logInfo("End Get Link ===");

        curl_close($curl);

        return $ret;
    }

    private function logError($msg) {
        $this->log("[ERROR]", $msg);
    }

    private function logInfo($msg) {
        $this->log("[INFO]", $msg);
    }

    private function log($prefix, $msg) {
        error_log($prefix . " - " . date('Y-m-d H:i:s') . " - " . $msg . "\n", 3, $this->LOG_FILE);
    }
}

/*$url = "https://www.fshare.vn/file/BA7TDZNZQHUL";
$username = "zang_itu@yahoo.com";
$password = "asd123";

$client = new SynoFileHostingFshareVN($url, $username, $password, NULL);
$client->GetDownloadInfo();
echo "DONE";*/

?>
