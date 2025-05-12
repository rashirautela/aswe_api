<?php
$ch=curl_init("https://ec2-3-16-218-236.us-east-2.compute.amazonaws.com:8080/api/");
$data="test";
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//ignore ssl
curl_setopt($ch, CURLOPT_POST,1);//tell curl we are using post
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//this is the data
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//prepare a response
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'content-type: application/x-www-form-urlencoded',
    'content-length: '.strlen($data))
            );
$result=curl_exec($ch);
curl_close($ch);
//$jsonResult=json_decode($result,true);
//echo "<pre>";
echo $result;
//echo "</pre>";
?>