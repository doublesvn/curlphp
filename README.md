#
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
```
Ada 3 langkah untuk login:
1. Generate crsf token
2. Login
3. Ambil UUID atau ID User


### 1. Generate CRSF
Dalam login dibutuhkan user(email) dan password, selain itu juga dibutuhkan ```DSPACE-XSRF-COOKIE``` dan ```X-XSRF-TOKEN``` dengan nilai yang sama. 
Jadi yang pertama dilakukan adalah login dengan user dan password, login akan gagal namun akan mendapatkan nilai ```DSPACE-XSRF-COOKIE``` dari Cookie.
```php
$inihasilcrsf = generatecrsf($namalogin, $katasandilogin);
```
fungsi ```generatecrsf()```  adalah fungsi yang membutuhkan user dan katasandi kemudian fungsi ini mengambalikan nilai ```DSPACE-XSRF-COOKIE```. Ada beberapa hal yang harus diperhatikan agar fungsi berjalan lancar.
```php
function generatecrsf($usercrsf, $passwordcrsf){
    ...
    $headres = [];
    curl_setopt_array($curl, array(
        ...
        CURLOPT_URL => 'http://localhost:8080/server/api/authn/login',
        CURLOPT_POSTFIELDS => 'user='.$usercrsf.'&password='.$passwordcrsf,
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
    $resultcrsf = $headers['dspace-xsrf-token'][0];
    curl_close($curl);
    return $resultcrsf;
}
```
Fungsi generatecrsf() megembalikan nilai ```DSPACE-XSRF-COOKIE``` yang terdapat di response header. ```$headres``` nantinya digunakan untuk menyimpan nilai response header. 
Kemudian ```CURLOPT_URL``` berisi URL untuk login. Variable ```CURLOPT_POSTFIELDS``` berisi data yang user dan password untuk login. Ada juga ```CURLOPT_HEADERFUNCTION```, ini adalah fungsi yang mengubah response header yang tadinya string menjadi array, array ini akan tersimpan di ```$headers```. Fungsi ini punya kelemahan karena response status codes tidak terambil.
Setelah ```curl``` dieksekusi, ambil nilai ```DSPACE-XSRF-COOKIE``` yang ada di ```$headers``` dengan cara  ``` $headers['dspace-xsrf-token'][0]```
Kemudian return nilai crsf.

#### 2. Login
Setelah dapat nilai crsf, set nilai tersebut ke ```DSPACE-XSRF-COOKIE``` dan ```X-XSRF-TOKEN``` di request header. Setelah set request header, login kembali dan loginpun berhasil. 
Login menggunakan function ```login()```. Fungsi pada dasarnya sama dengan fungsi ```generatecrsf()``` namun punya input dan output yang berbeda. fungsi ```login()``` membutuhkan nilai crsf, email user dan password. Output dari fungsi ini adalah bearer token dan nilai crsf. 

```php
function login($usercrsf, $userlogin, $passwordlogin){
    ...
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost:8080/server/api/authn/login',
    CURLOPT_POSTFIELDS => 'user='.$userlogin.'&password='.$passwordlogin,
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
    $authlogin = $x[0]['Authorization'];
    $crsflogin = $x[0]['DSPACE-XSRF-TOKEN'];
    return [$authlogin, $crsflogin];
};
...
?>
```

Untuk ```CURLOPT_URL``` dan ```CURLOPT_POSTFIELDS``` nilainya sama dengan yang ada difungsi ```generatecsrf()```. Set nilai csrf dari fungsi ```generatecsrf()``` ke dalam ```X-XSRF-TOKEN``` dan ```DSPACE-XSRF-COOKIE``` yang ada direquest header. Masukan kedua variable tersebut ke array lalu masukan array ke ```CURLOPT_HTTPHEADER```.
Setelah curl tereksekusi, ambil nilai header kemudian ubah ke array menggunakan fungsi ```get_headers_from_curl_response()```. Ambil nilai bearer token dengan ```$x[0]['Authorization']``` dan ambil nilai csrf dengan ```$x[0]['DSPACE-XSRF-TOKEN']```. Kemudian return kedua nilai tersebut dan set ke ```SESSION```
#### 3. Ambil UUID atau ID User
UUID atau ID user di Dspace digunakan sebagai parameter untuk beberapa tugas.
Cara mengambil UUID adalah dengan mengunakan URL Status. kemudian fungsi tersebut akan mengembalikan URL Eperson. Jika kita jalankan  URL ini kita akan mendapatkan data-data tentang user tersebut. 
```status.php```
```php
<?php
function getuuidfromstatus(){
   include 'getuuid.php';
   $curl = curl_init();
   curl_setopt_array($curl, array(
   CURLOPT_URL => "http://localhost:8080/server/api/authn/status",
   ...
   CURLOPT_HTTPHEADER => array(
      'Authorization: '.$_SESSION["auth"],
      ),
   ));

      $resp = curl_exec($curl);
      $x = json_decode($resp, true);
      curl_close($curl);
      $uuidlink = $x["_links"]["eperson"]["href"];
      $uuid = getuuid($uuidlink);
      $_SESSION["uuid"] = $uuid;
}
?>
```
Didalam file  ```status.php ``` terdapat fungsi ```getuuidfromstatus()```. Difungsi ini kita menjalankan curl. Di ```CURLOPT_URL```, isi URL status dan ```CURLOPT_HTTPHEADER``` isi ```Authorization``` dengan ```bearer token```. Kemudian ambil URL Eperson dengan ```$uuidlink = $x["_links"]["eperson"]["href"]```. URL eperson ini digunakan sebagai parameter fungsi ```getuuid()```. 

Didalam ```getuuidfromstatus()``` terdapat ```getuuid()``` yang di import dari file ```getuuid.php```. 

```getuuid.php```
```php
<?php
function getuuid($urleperson){
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $urleperson,
        ...
        CURLOPT_HTTPHEADER => array(
          'Authorization: '.$_SESSION["auth"],
        ),
    ));
    $response = curl_exec($curl);
    $x = json_decode($response, true);
    curl_close($curl);
    return $x['uuid'];
}
?>
```
Fungsi ini digunakan untuk mendapatkan UUID. Set ```CURLOPT_URL``` dengan URL eperson dan set juga ```CURLOPT_HTTPHEADER``` dengan ```bearer token```. Kemudian return nilai ```UUID```
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
Ada tiga langkah untuk membuat User:
1. Add User Data
2. Add User Password
3. Add User to Group

#### Add user data
Pertama kirim json yang berisi nama depan, nama belakang dan email.
Setelah berhasil kirim, ambil ```uuid``` dari response, ```uuid``` ini akan digunakan sebagai parameter untuk menambah password user dan menambahkan user ke group.

```php
<?php
function adduser($namadepan, $namabelakang, $email){
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost:8080/server/api/eperson/epersons',
    ...
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
Data yang perlukan untuk membuat user adalah nama depan, nama belakang, dan email. Data tersebut dimasukan kedalam ```CURLOPT_POSTFIELDS```. Kemudian set juga ```X-XSRF-TOKEN```, ```Authorization``` dan ```DSPACE-XSRF-COOKIE``` di ```CURLOPT_HTTPHEADER```. Kemdian return ```UUID```.

#### Add password
Setelah mengirim data user dan mendapatkan ```uuid```, kita menambakan password agar user dapat login.

```php
function addpassword($uuid, $password){
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost:8080/server/api/eperson/epersons/'.$uuid,
    ...
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
```CURLOPT_URL``` berisi URL dengan request param ```UUID```. ```CURLOPT_CUSTOMREQUEST``` berisi method ```PATCH```. Di ```CURLOPT_POSTFIELDS``` operasi(```op```) ```add``` untuk menambah data, ```path``` dimana value akan disimpan, ```value``` berisi password. Kemudian ```CURLOPT_HTTPHEADER``` set ```X-XSRF-TOKEN```, ```Authorization``` dan ```DSPACE-XSRF-COOKIE```.

#### Add to group
Secara default user yang terdaftar tidak dapat langsung mengirim ```workspaceitem``` di ```collection```. Jadi kita harus menambakan user ke grup admin di ```collection``` tersebut. Kita tambakan ke grup admin agar ```workspaceitem``` dari user langsung ter-publish.


```php
function addtogroup($uuid){
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost:8080/server/api/eperson/groups/55194609-eec5-4309-b290-d80b55e64b26/epersons',
    ...
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
Di ```CURLOPT_URL``` ada URL untuk menambahkan user ke grup. ```55194609-eec5-4309-b290-d80b55e64b26``` adalah ```UUID``` dari salah satu grup. Di ```CURLOPT_POSTFIELDS``` berisi URL Eperson dari user yang ingin ditambahkan. Set ```CURLOPT_HTTPHEADER``` seperti proses-proses sebelumnya.

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
Ada 4 proses untuk mengupload file ke Dspace
1. Membuat workspaceitem
2. Menambahkan data required
3. Menambahkan file ke workspaceitem
#### Membuat workspaceitem
```Workspaceitem``` ini seperti folder yang digunakan untuk tempat menaruh file-file.
```php

function makeworkspace(){

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost:8080/server/api/submission/workspaceitems/',
    ...
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
```CURLOPT_URL``` berisi URL untuk membuat workspaceitem. Jika user hanya terdaftar di satu collection kita tidak perlu menambahkan parameter lain di URL-nya. Namun jika user terdaftar di banyak collection, kita harus menambakan UUID dari collection yang kita tuju, sehingga URL menjadi ```http://localhost:8080/server/api/submission/workspaceitems?owningCollection=<UUID>```. Kemudian set request header di ```CURLOPT_HTTPHEADER``` sams seperti proses-proses sebelumnya. Setelah ```curl``` tereksekusi, kita ambil body responsenya kemudian ubah menjadi array. 
 Kemudian ambil ```id workspaceitem```  yang akan digunakan sebagai parameter untuk menambah informasi dan upload file.

#### Menambah Informasi
Di workspace ini ada 4 data required yang harus ada dan jika tidak ada, ```workspace``` tidak dapat ter-publish. 4 data tersebut adalah ```title```, ```date```, ```license``` dan juga ```file```. 

```addinfo.php```
```php
<?php

function addinfo($idworkspace, $nameworkspace, $dateworkspace){

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost:8080/server/api/submission/workspaceitems/'.$idworkspace,
    ...
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
Di ```CURLOPT_URL``` kita isi dengan URL workspaceitem beserta id workspaceitem yang tadi telah dibuat. Di ```CURLOPT_POSTFIELDS``` berisi 3 list data yaitu ```title```, ```date``` dan ```license```. Pertama kita kirim ```title``` dan ```date``` yang diinput oleh user. Untuk ```licence``` diisi dengan nilai ```true```. ```"op":"add" ``` digunakan untuk juka kita ingin menambahkan data, ```path``` adalah tempat menyimpan value. ```Value``` adalah data yang ingin kita kirim. Set ```CURLOPT_HTTPHEADER``` seperti biasa.

#### Upload file
Untuk upload file kita perlu ```path``` dari file yang ingin kita upload dan juga ```id workspaceitem```.

```php
<?php    
    function addpdf($idaddpdf, $pathfile){

        $curl = curl_init();   
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://localhost:8080/server/api/submission/workspaceitems/'.$idaddpdf,
        CURLOPT_RETURNTRANSFER => true,
        ...
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
```CURLOPT_POSTFIELDS``` berisi path dari file yang ingin kita kirim. Jangan lupa set ```CURLOPT_HTTPHEADER``` seperti  biasa.

#### Move to workflow
Memindahkan ```workspaceitem``` ke  ```workflowitem``` sama saja dengan mengajukan  ```workspaceitem``` kita ke admin. Apabila user yang mengajukan  ```workspaceitem ``` adalah admin maka  ```workspaceitem ``` akan langsung terpublish tanpa proses penerimaan. Untuk memindahkannya, kita perlu URL  ```workspaceitem ``` kita untuk digunakan sebagai inputan.

```php 
function moveflow($idworkspace){

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost:8080/server/api/workflow/workflowitems',
    ...
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
Kita set ```CURLOPT_HTTPHEADER``` seperti biasa. Untuk memindahkan workspaceitem ke workflow kita hanya perlu mengirim URL workspaceitem kita. Kita set ```CURLOPT_POSTFIELDS``` denga URL workspaceitem.





