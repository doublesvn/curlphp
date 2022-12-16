# Login
```halamanlogin.php```

```php
<form action="halamanlogin.php" method="post" >
    <input type="text" name="usernamelogin">
    </br>
    </br>
    <input type="text" name="passwordlogin">
    </br>
    </br>
    <input type="submit" value="Post" name="login">
</form>

<?php
    session_start();
    include 'generatecrsf.php';
    include 'login.php';
    include 'status.php';

    if(isset($_POST['login'])){
        $namalogin =  $_POST['usernamelogin'];
        $katasandilogin =  $_POST['passwordlogin'];

        $inihasilcrsf = generatecrsf($namalogin, $katasandilogin);
        $inihasillogin = login($inihasilcrsf, $namalogin, $katasandilogin);
        [$auth, $crsf] = $inihasillogin;

        $_SESSION["auth"] = $auth;
        $_SESSION["crsf"] = $crsf;

        getuuidfromstatus();
        
        if ((!empty($_SESSION["auth"])) && (!empty($_SESSION["crsf"])) && (!empty($_SESSION["uuid"]))) {
            header('Location: menu.php');
        }else{
            echo "login gagal";
        };
    }
?>
```
Dalam login dibutuhkan user(email) dan password, selain itu juga dibutuhkan ```DSPACE-XSRF-COOKIE``` dan ```X-XSRF-TOKEN``` dengan nilai yang sama. 
#### Login 1
Jadi yang pertama dilakukan adalah login dengan user dan password, login pertama akan gagal namun akan mendapatkan nilai ```DSPACE-XSRF-COOKIE``` dari Cookie.

```generatecrsf.php```
```php
<?php
function generatecrsf($usercrsf, $passwordcrsf){
  $curl = curl_init();
  $headers=[];
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost:8080/server/api/authn/login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => 'user='.$usercrsf.'&password='.$passwordcrsf,
    CURLOPT_HEADER=> true,
    CURLOPT_HEADERFUNCTION => function($curl, $header) use (&$headers)
    {
      $len = strlen($header);
      $header = explode(':', $header, 2);
      if (count($header) < 2) 
        return $len;
      $headers[strtolower(trim($header[0]))][] = trim($header[1]);
      return $len;
    }
  ));
  $response = curl_exec($curl);
  print_r($headers);
  $resultcrsf = $headers['dspace-xsrf-token'][0];
  curl_close($curl);
  return $resultcrsf;
}
?>
```
#### Login 2
Setelah itu, set nilai ```DSPACE-XSRF-COOKIE``` dan ```X-XSRF-TOKEN``` di request header dengan nilai dari login pertama tadi. Setelah set request header, login kembali dan loginpun berhasil.

```login.php```
```php
<?php
function login($usercrsf, $userlogin, $passwordlogin){
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost:8080/server/api/authn/login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => 'user='.$userlogin.'&password='.$passwordlogin,
    CURLOPT_HEADER=> true,
    CURLOPT_HTTPHEADER => array(
        'X-XSRF-TOKEN: '.$usercrsf,
        'Content-Type: application/x-www-form-urlencoded',
        'Cookie: DSPACE-XSRF-COOKIE='.$usercrsf,
        )
    ));
    $response = curl_exec($curl);

    curl_close($curl);
    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    
    $x = get_headers_from_curl_response($header);
    echo $response;
    echo "</br>";
    $authlogin = $x[0]['Authorization'];
    echo "</br>";
    $crsflogin = $x[0]['DSPACE-XSRF-TOKEN'];
    return [$authlogin, $crsflogin];
};


function get_headers_from_curl_response($headerContent)
{

    $headers = array();

    // Split the string on every "double" new line.
    $arrRequests = explode("\r\n\r\n", $headerContent);

    // Loop of response headers. The "count() -1" is to 
    //avoid an empty row for the extra line break before the body of the response.
    for ($index = 0; $index < count($arrRequests) -1; $index++) {

        foreach (explode("\r\n", $arrRequests[$index]) as $i => $line)
        {
            if ($i === 0)
                $headers[$index]['http_code'] = $line;
            else
            {
                list ($key, $value) = explode(': ', $line);
                $headers[$index][$key] = $value;
            }
        }
    }

    return $headers;
};
?>
```
Setelah login berhasil, kita akan mendapatkan ```DSPACE-XSRF-COOKIE``` dan bearer token. Kita ```DSPACE-XSRF-COOKIE``` dan ```X-XSRF-TOKEN``` dengan nilai terbaru dari login 2. Kita juga harus set Authorization dengan bearer token.

# Create User
Create User harus dengan user admin. 

```makeuser.php```
```php
<form action="makeuser.php" method="post" enctype="multipart/form-data">
    <p>nama depan</p>
    <input type="text" name="namadepan">
    </br>
    </br>
    <p>nama belakang</p>
    <input type="text" name="namabelakang">
    </br>
    </br>
    <p>email</p>
    <input type="text" name="email">
    </br>
    </br>
    <p>password</p>
    <input type="text" name="password">
    </br>
    </br>
    <input type="submit" value="Post" name="kirim">
</form>

<?php
session_start();
include 'adduser.php';
include 'addpassword.php';
include 'addtogroup.php';

if(isset($_POST['kirim'])){
    $namadepan =  $_POST['namadepan'];
    $namabelakang =  $_POST['namabelakang'];
    $email =  $_POST['email'];
    $password =  $_POST['password'];
    
    echo "</br>";
    echo "ADD USER";
    echo "</br>";
    $uuid = adduser($namadepan, $namabelakang, $email);
    echo "</br>";
    echo "ADDINFO";
    echo "</br>";
    addpassword($uuid, $password);
    echo "</br>";
    echo "ADDTOGROUPW";
    echo "</br>";
    addtogroup($uuid);     
}
?>
```
#### Add user data
Pertama kirim json yang berisi nama depan, nama belakang dan email.
Setelah berhasil kirim, ambil ```uuid``` dari response, ```uuid``` ini akan digunakan sebagai parameter untuk menambah password user dan menambahkan user ke group.

```adduser.php```
```php
<?php
function adduser($namadepan, $namabelakang, $email){
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost:8080/server/api/eperson/epersons',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
    "metadata": {
        "eperson.firstname": [{"value": "'.$namadepan.'"}],
        "eperson.language": [{"value": "en"}],
        "eperson.lastname": [{"value": "'.$namabelakang.'"}]
    },
    "netid": null,
    "canLogIn": true,
    "email": "'.$email.'",
    "requireCertificate": false,
    "selfRegistered": false,
    "type": "eperson"
    }',
    CURLOPT_HTTPHEADER => array(
        'X-XSRF-TOKEN: '.$_SESSION['crsf'],
        'Authorization: '.$_SESSION['auth'],
        'Cookie: DSPACE-XSRF-COOKIE='.$_SESSION['crsf'],
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);
    $x = json_decode($response, true);

    if (curl_errno($curl)) {
        $error_msg = curl_error($curl);
        echo $error_msg;
    }else{
        echo "Add User berhasil";
        echo "</br>";
        return $x['uuid'];
    };
    curl_close($curl);

}
?>
```
#### Add password
Setelah mengirim data user dan mendapatkan ```uuid```, kita menambakan password agar user dapat login.

```addpassword.php```
```php
<?php
function addpassword($uuid, $password){
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost:8080/server/api/eperson/epersons/'.$uuid,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'PATCH',
    CURLOPT_POSTFIELDS =>'[
        { "op": "add",
        "path": "/password",
        "value": {"new_password": "'.$password.'"}
        }
    ]',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'X-XSRF-TOKEN: '.$_SESSION['crsf'],
        'Authorization: '.$_SESSION['auth'],
        'Cookie: DSPACE-XSRF-COOKIE='.$_SESSION['crsf']
    ),
    ));

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        $error_msg = curl_error($curl);
        echo $error_msg;
    }else{
        echo "Add Password berhasil";
        echo "</br>";
    };

    curl_close($curl);
    
}
?>
```

#### Add to group
Secara default user yang terdaftar tidak dapat langsung mengirim ```workspaceitem``` di ```collection```. Jadi kita harus menambakan user ke grup admin di ```collection``` tersebut. Kita tambakan ke grup admin agar ```workspaceitem``` dari user langsung ter-publish.

```addtogroup.php```
```php
<?php
function addtogroup($uuid){
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost:8080/server/api/eperson/groups/55194609-eec5-4309-b290-d80b55e64b26/epersons',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => 'http://localhost:8080/server/api/eperson/epersons/'.$uuid,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: text/uri-list',
        'X-XSRF-TOKEN: '.$_SESSION['crsf'],
        'Authorization: '.$_SESSION['auth'],
        'Cookie: DSPACE-XSRF-COOKIE='.$_SESSION['crsf']
    ),
    ));

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        $error_msg = curl_error($curl);
        echo $error_msg;
    }else{
        echo "Add to group berhasil";
        echo "</br>";
    };
    curl_close($curl);
    curl_close($curl);
}
?>
```
# Upload File
```halamanupload.php```
```php
<form action="halamanupload.php" method="post" enctype="multipart/form-data">
    <p>Nama Workspace</p>
    <input type="text" name="nameworkspace">
    </br>
    </br>
    <p>Date Workspace</p>
    <input type="date" name="dateworkspace">
    </br>
    </br>
    <p>Add File</p>
    <input type="file" name="pdf">
    </br>
    </br>
    <input type="submit" value="Post" name="kirim">
</form>

<?php
session_start();
include 'makeworkspace.php';
include 'addinfo.php';
include 'addpdf.php';
include 'moveflow.php';
if(isset($_POST['kirim'])){
    $namaw =  $_POST['nameworkspace'];
    $datew =  $_POST['dateworkspace'];
    
    $path = "uploads/".$_FILES['pdf']['name'];
    move_uploaded_file($_FILES['pdf']['tmp_name'], $path);
    
    $filew = getcwd()."/".$path;
    echo "</br>";
    echo "MAKEWORKSPACE";
    echo "</br>";
    $idwork = makeworkspace();
    echo "</br>";
    echo "ADDINFO";
    echo "</br>";
    addinfo($idwork,$namaw,$datew);
    echo "</br>";
    echo "ADDPDF";
    echo "</br>";
    addpdf($idwork, $filew);
    echo "</br>";
    echo "MOVEWORKFLOW";
    echo "</br>";
    $moveflow = moveflow($idwork);
}
?>
```
#### Membuat workspaceitem
```Workspaceitem``` ini seperti folder yang digunakan untuk tempat menaruh file-file. Kemudian ambil ```id workspaceitem```  yang akan digunakan sebagai parameter untuk menambah informasi dan upload file. Di workspace ini ada 4 data required yang harus ada dan jika tidak ada, ```workspace``` tidak dapat ter-publish. 4 data tersebut adalah ```title```, ```date```, ```license``` dan juga ```file```.

```makeworkspace.php```
```php
<?php

function makeworkspace(){

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost:8080/server/api/submission/workspaceitems/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{}',
    CURLOPT_HEADER=> true,
    CURLOPT_HTTPHEADER => array(
      'X-XSRF-TOKEN: '. $_SESSION["crsf"],
      'Authorization: '.$_SESSION["auth"],
      'Content-Type: application/json',
      'Cookie: DSPACE-XSRF-COOKIE='.$_SESSION["crsf"]
    ),
  ));

  $response = curl_exec($curl);
  $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
  $header = substr($response, 0, $header_size);
  $body = substr($response, $header_size);
  $x = json_decode($body, true);

  if (curl_errno($curl)) {
    $error_msg = curl_error($curl);
    echo $error_msg;
  }else{
    echo "buat workspaceitems berhasil";
    echo "</br>";
    return $x['id'];
  };
  curl_close($curl);
}
?>
```
#### Menambah Informasi
Data yang perlu dimasukkan adalah ```title``` dan ```date```, untuk ```license``` kita perlu set menjadi ```true```.

```addinfo.php```
```php
<?php

function addinfo($idworkspace, $nameworkspace, $dateworkspace){

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost:8080/server/api/submission/workspaceitems/'.$idworkspace,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_HEADER=> true,
    CURLOPT_CUSTOMREQUEST => 'PATCH',
    CURLOPT_POSTFIELDS =>'[
    {"op":"add","path":"/sections/traditionalpageone/dc.title", "value": [{"value": "'.$nameworkspace.'"}]},
    {"op":"add","path":"/sections/traditionalpageone/dc.date.issued", "value": [{"value": "'.$dateworkspace.'"}]},
    {"op":"add","path":"/sections/license/granted", "value": true}
    ]',
    CURLOPT_HTTPHEADER => array(
      'X-XSRF-TOKEN: '.$_SESSION["crsf"],
      'Authorization: '.$_SESSION["auth"],
      'Content-Type: application/json',
      'Cookie: DSPACE-XSRF-COOKIE='.$_SESSION["crsf"]
    ),
  ));

  curl_exec($curl);
  if (curl_errno($curl)) {
    $error_msg = curl_error($curl);
    echo $error_msg;
  }else{
    echo "add info berhasil";
    echo "</br>";
  };
  curl_close($curl);
}


?>
```
#### Upload file
Untuk upload file kita perlu ```path``` dari file yang ingin kita upload.
```addpdf.php```
```php
<?php    
    function addpdf($idaddpdf, $pathfile){

        $curl = curl_init();   
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://localhost:8080/server/api/submission/workspaceitems/'.$idaddpdf,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('file'=> new CURLFile($pathfile)),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: multipart/form-data',
            'X-XSRF-TOKEN: '. $_SESSION["crsf"],
            'Authorization: '.$_SESSION["auth"],
            'Cookie: DSPACE-XSRF-COOKIE='.$_SESSION["crsf"],
            ),
        ));
        curl_exec($curl);

        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            echo $error_msg;
        }else{
            echo "add pdf berhasil";
            echo "</br>";
        };

        curl_close($curl);

    }
?>
```
#### Move to workflow
Memindahkan ```workspaceitem``` ke  ```workflowitem``` sama saja dengan mengajukan  ```workspaceitem``` kita ke admin. Apabila user yang mengajukan  ```workspaceitem ``` adalah admin maka  ```workspaceitem ``` akan langsung terpublish tanpa proses penerimaan. Untuk memindahkannya, kita perlu link  ```workspaceitem ``` kita untuk digunakan sebagai inputan.

```moveflow.php```
```php 
<?php

function moveflow($idworkspace){

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost:8080/server/api/workflow/workflowitems',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'http://localhost:8080/server/api/submission/workspaceitems/'.$idworkspace,
    CURLOPT_HTTPHEADER => array(
      'Content-Type: text/uri-list',
      'X-XSRF-TOKEN: '.$_SESSION["crsf"],
      'Authorization: '.$_SESSION["auth"],
      'Cookie: DSPACE-XSRF-COOKIE='.$_SESSION["crsf"]
    )
  ));

  $response = curl_exec($curl);
  $x = json_decode($response, true);


  if (curl_errno($curl)) {
    $error_msg = curl_error($curl);
    echo $error_msg;
  }else{
    echo "move to workflow berhasil";
    echo "</br>";
    echo $x;
  }
  curl_close($curl);
}
?>
```





