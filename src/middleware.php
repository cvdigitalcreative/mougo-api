<?php
// Application middleware
$tokenCheck = function($request, $response, $next){
    if(empty($request->getHeader('token')[0])){
        return $response->withJson(['status'=>'error','message'=>'Token Required'],401);
    }
    $token = $request->getHeader('token')[0];

    
    $sql = "SELECT id_user , hits , token
            FROM api_token
            WHERE token LIKE :token";
    $stmt = $this->db->prepare($sql);
    $data = [
        ':token'=>$token
    ];

    $stmt->execute($data);
    $numrowApi = $stmt->rowCount();

    if($numrowApi>0){
        $result = $stmt->fetch();
        if($token==$result['token']){
            $sql = "UPDATE api_token
                    SET hits=hits+1
                    WHERE token LIKE :token";
            $stmt = $this->db->prepare($sql);
            $dataUpdate = [
                ':token'=>$token
            ];
            $stmt->execute($dataUpdate);
            var_dump($next);
            return $response = $next($request,$response);

        }
    }
    return $response->withJson(['status'=>'error','message'=>'Unauthorized'],401);
};