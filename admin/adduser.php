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
