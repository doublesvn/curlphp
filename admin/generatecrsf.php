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