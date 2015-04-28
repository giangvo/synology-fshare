<?php
/* @author: zang_itu@yahoo.com
 * @version: 0.1 */

class SynoFileHostingFshareVN {
    private $Url;
    private $Username;
    private $Password;
    private $HostInfo;
    private $COOKIE_JAR = '/tmp/fsharevn.cookie';
    private $LOGIN_URL = 'https://www.fshare.vn/login';
    private $FSHARE_HOME = 'https://www.fshare.vn/home';
    private $FSHARE_URL = 'https://www.fshare.vn';
    
    public function __construct($Url, $Username, $Password, $HostInfo) {
        /*if(strpos($Url,'http://') !== FALSE){
            $Url = str_replace("http://", "https://", $Url);
        }else{
            if(strpos($Url,'https://') === FALSE){
                $Url = "https://" . $Url;
            }
        }*/

        /*$this->Url = $Url;
        $this->Username = $Username;
        $this->Password = $Password;*/

        $this->Url = "https://www.fshare.vn/file/BA7TDZNZQHUL";
        $this->Username = "zang_itu@yahoo.com";
        $this->Password = "asd123";
        $this->AppId = "GUxft6Beh3Bf8qKP7GC2IplYJZz1A53JQfRwne0R";

        $this->HostInfo = $HostInfo;

        ini_set('max_execution_time', 300);
    }
    
    public function Verify($ClearCookie) {
        if(file_exists($this->COOKIE_JAR)) {
            unlink($this->COOKIE_JAR);
            $this->COOKIE_JAR = tempnam('/tmp','cookie');
        }
            
        return $this->performLogin();
    }
    
    public function GetDownloadInfo() {
        
        $DownloadInfo = array();

        $downloadUrl = $this->getLink();
        
        if($downloadUrl === "error") {
            $DownloadInfo[DOWNLOAD_ERROR] = $downloadUrl;   
        }
        else
        {
            $DownloadInfo[DOWNLOAD_URL] = $downloadUrl;
        }

        echo "====";
        echo $DownloadInfo;
        
        return $DownloadInfo;

    }
    
    private function performLogin() {
    
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
            echo "login error: " . curl_error($curl);

            curl_close($curl);
            return FALSE;
        }
        else
        {
            $this->Token = json_decode($curl_response)->{'token'};
            
            curl_close($curl);
            return TRUE;
        }
    }

    private function getLink() {
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
            echo "get link error: " . curl_error($curl);
            
            curl_close($curl);
            return "error";
        }
        else
        {
            $downloadUrl = json_decode($curl_response)->{'location'};
            echo $downloadUrl;

            curl_close($curl);
            return $downloadUrl;
        }
    }
}

$url = "https://www.fshare.vn/file/BA7TDZNZQHUL";
$username = "zang_itu@yahoo.com";
$password = "asd123";

$client = new SynoFileHostingFshareVN($url, $username, $password, NULL);
$client->Verify(FALSE);
$client->GetDownloadInfo(FALSE);

?>
