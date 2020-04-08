<?php  
function encrypt($data, $key) {
        $data = openssl_encrypt($data, 'aes-256-ecb', base64_decode($key), OPENSSL_RAW_DATA);
        return base64_encode($data);
    }
    function decrypt($data, $key) {
        $encrypted = base64_decode($data);
        return openssl_decrypt($encrypted, 'aes-256-ecb', base64_decode($key), OPENSSL_RAW_DATA);
    }
   
    
?>