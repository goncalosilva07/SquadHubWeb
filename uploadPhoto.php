<?php

include('config.php');

if ($_POST['idUser'] != null) {
    
    $idUser = filter_var($_POST['idUser'], FILTER_VALIDATE_INT);

    $upload_name = strtolower(pathinfo($_FILES["photo"]["name"],PATHINFO_FILENAME));
    $upload_extension = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
    //$upload_mime_type = $_FILES["photo"]["type"];
    //$upload_tmp_name = $_FILES["photo"]["tmp_name"];
    //$upload_error = $_FILES["photo"]["error"];
    //$upload_size = $_FILES["photo"]["size"];

    $filepath = UPLOAD_PATH . $upload_name . '.' . $upload_extension;
    $filename = $upload_name . '.' . $upload_extension;

    if (is_file($filepath) || is_dir($filepath)){
        http_response_code(400);
        echo json_encode(array("message" => "Ficheiro já existente."), JSON_PRETTY_PRINT);           
    }else{
        try {
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $filepath)) {
                try {
             
                    $sql = "UPDATE USER SET photo = ? WHERE id = ?;";
    
                    $pdo = $conn;
                    // Preparar a query
                    $stmt = $pdo->prepare($sql);
    
                    // Atribuir valores aos parâmetros
                    $stmt->bindParam(1, $filename, PDO::PARAM_STR);
                    $stmt->bindParam(2, $idUser, PDO::PARAM_STR);     
                    
                    // Executar a query
                    $stmt->execute();
    
                    http_response_code(200);
                    echo json_encode(array("message" => "Foto inserida com sucesso!"), JSON_PRETTY_PRINT);
            
                } catch (PDOException $e) {
                    http_response_code(500);
                    echo json_encode(array("message" => $e->getMessage()), JSON_PRETTY_PRINT);
                }
                         
            }else{
                http_response_code(400);
                echo json_encode(array("message" => "Erro ao fazer o upload."), JSON_PRETTY_PRINT);
                die;
            }
        } catch (Exception $e){
            http_response_code(501);
            echo json_encode(array("message" => $e->getMessage()), JSON_PRETTY_PRINT);
        }
        
    }
    
    
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Dados inválidos fornecidos."), JSON_PRETTY_PRINT);
}


?>