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
