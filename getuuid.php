<?php

function getuuid($urleperson){
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $urleperson,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
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