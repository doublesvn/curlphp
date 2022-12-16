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

