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