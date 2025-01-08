<?php

class User{
    
    private $pdo;
    private $table_name = "USER";

    public function __construct($db){
        $this->pdo = $db;
    }

    function getUserData($data){
        try {
            if (isset($_SESSION['id'])) {
    
                $sql = "SELECT u.id, u.photo, u.username, u.name, u.surname, u.birthdate, u.email, u.phone, ut.name as role FROM USER AS u
                        JOIN USER_TYPE AS ut ON u.role = ut.id
                        WHERE u.id = ?
                        LIMIT 1;";
    
                try {
                    // Preparar a query
                    $stmt = $this->pdo->prepare($sql);
    
                    // Atribuir valores aos parâmetros
                    $stmt->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
    
                    // Executar a query
                    $stmt->execute();
    
                    $row = $stmt->fetch();

                    if($row){
                        $userData = [
                            'id' => (int)$row['id'],
                            'username' => $row['username'],
                            'name' => $row['name'],
                            'surname' => $row['surname'],
                            'birthdate' => $row['birthdate'],
                            'email' => $row['email'],
                            'phone' => $row['phone'],
                            'role' => $row['role'],
                            'photo' => $row['photo']
                        ];
                        return ["code" => 200, "userData" => $userData];
                    }else{
                        return ["code" => 400, "message" => "Erro ao aceder as informações do utilizador."];
                    }

                } catch (PDOException $e) {
                    return ["code" => 500, "message" => $e->getMessage()];
                }
    
            } else {
                return ["code" => 400, "message" => "Erro na sessão."];
            }
    
        } catch (Exception $e) {
            return ["code" => 500, "message" => $e->getMessage()];
        }
    }

    function updateUserData($data){
        try {
            if (isset($_SESSION['id'], $data->name, $data->surname, $data->birthdate, $data->email, $data->phone)) {
    
                $name = filter_var($data->name, FILTER_SANITIZE_STRING);
                $surname = filter_var($data->surname, FILTER_SANITIZE_STRING);
                $birthdate = filter_var($data->birthdate, FILTER_SANITIZE_STRING);
                $email = filter_var($data->email, FILTER_SANITIZE_STRING);
                $phone = filter_var($data->phone, FILTER_SANITIZE_STRING);

                $sql = "UPDATE USER SET name = ?, surname = ?, birthdate = ?, email = ?, phone = ? WHERE id = ?;";

                try {
            
                    // Preparar a query
                    $stmt = $this->pdo->prepare($sql);

                    // Atribuir valores aos parâmetros
                    $stmt->bindParam(1, $name, PDO::PARAM_STR);
                    $stmt->bindParam(2, $surname, PDO::PARAM_STR);
                    $stmt->bindParam(3, $birthdate, PDO::PARAM_STR);
                    $stmt->bindParam(4, $email, PDO::PARAM_STR);
                    $stmt->bindParam(5, $phone, PDO::PARAM_STR);
                    $stmt->bindParam(6, $_SESSION['id'], PDO::PARAM_INT);
                    
                    // Executar a query
                    $stmt->execute();

                    return ["code" => 200, "message" => "Dados atualizados com sucesso!"];    
    
                } catch (PDOException $e) {
                    return ["code" => 500, "message" => $e->getMessage()]; 
                }
    
            } else {
                return ["code" => 400, "message" => "Dados inválidos fornecidos."];
            }
    
        } catch (Exception $e) {
            return ["code" => 500, "message" => $e->getMessage()];
        }
    }

    function logout($data){
        try {
            if (isset($_SESSION['id'])) {
    
                session_destroy();
                return ["code" => 200, "message" => "Logout efetuado com sucesso!"];    
    
            } else {
                return ["code" => 400, "message" => "Sem sessão válida."];
            }
    
        } catch (Exception $e) {
            return ["code" => 500, "message" => $e->getMessage()];
        }
    }
}
?>