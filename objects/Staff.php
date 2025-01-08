<?php

class Staff{
    
    private $pdo;
    private $table_name = "USER";

    public function __construct($db){
        $this->pdo = $db;
    }

    function getStaff($data){
        try {
            if (isset($_SESSION['idClub'])) {
    
                // Lista que armazenará os jogos
                $staffList = [];

                $query = "SELECT u.id, u.idClub, u.username, u.name, u.surname, u.birthdate, u.email, u.phone, s.careerStartDate FROM USER AS u
                JOIN STAFF AS s ON u.id = s.idUser
                WHERE u.idClub = :idClub AND u.role = 4;";
    
                try {
                    // Preparar a query
                    $stmt = $this->pdo->prepare($query);
    
                    // Atribuir valores aos parâmetros
                    $stmt->bindParam(':idClub', $_SESSION['idClub'], PDO::PARAM_INT);
    
                    // Executar a query
                    $stmt->execute();
    
                    // Buscar os resultados
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        // Criar um objeto palyer a partir dos dados da tabela
                        $staff = [
                            'id' => (int)$row['id'],
                            'idClub' => (int)$row['idClub'],
                            'username' => $row['username'],
                            'name' => $row['name'],
                            'surname' => $row['surname'],
                            'birthdate' => $row['birthdate'],
                            'email' => $row['email'],
                            'phone' => $row['phone'],
                            'careerStartDate' => $row['careerStartDate'],
                        ];
    
                        // Adicionar o jogo à lista
                        $staffList[] = $staff;
                    }
    
                    return ["code" => 200, "staffList" => $staffList];
    
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

    function deleteStaffFromClub($data){
        try {
            if (isset($data->idStaff)) {
    
                $idStaff = filter_var($data->idStaff, FILTER_VALIDATE_INT);
                $sql = "UPDATE $this->table_name SET idClub = ? WHERE id = ? AND idClub = ?;";
                
                try {
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->bindValue(1, null, PDO::PARAM_NULL);
                    $stmt->bindParam(2, $idStaff, PDO::PARAM_INT);
                    $stmt->bindParam(3, $_SESSION['idClub'], PDO::PARAM_INT);
                    $stmt->execute();
    
                    return ["code" => 200, "message" => "Staff removido com sucesso!"];
    
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

    function inviteStaff($data){
        try {
            if (isset($data->email, $_SESSION['idClub'])) {
    
                $email = filter_var($data->email, FILTER_VALIDATE_EMAIL);
    
                $insertSql = "INSERT INTO NOTIFICATIONS (idClub, idUser, title, description, isInvite, isActive) VALUES (?, ?, ?, ?, ?, ?)";
                $checkEmailSql = "SELECT u.id FROM USER as u WHERE email = ? AND role = 4;";
                $getClubInfoSql = "SELECT * FROM CLUB WHERE id = ?;";
    
                try {
    
                    //Verificar se o utiliazdor existe
                    $stmt = $this->pdo->prepare($checkEmailSql);
                    $stmt->bindParam(1, $email, PDO::PARAM_STR);
                    $stmt->execute();
    
                    $row = $stmt->fetch();
    
                    if ($row){
    
                        //Buscar o id do utilizador
                        $idUser = $row['id'];
    
                        //Buscar informações do Clube
                        $stmt = $this->pdo->prepare($getClubInfoSql);
                        $stmt->bindParam(1, $_SESSION['idClub'], PDO::PARAM_STR);
                        $stmt->execute();
    
                        $clubName = '';
    
                        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                            $clubName = $row['name'];
                        }else{
                            return ["code" => 400, "message" => "Erro ao aceder as informações do clube."];
                        }
    
                        // Preparar a query
                        $stmt = $this->pdo->prepare($insertSql);
    
                        // Atribuir valores aos parâmetros
                        $stmt->bindParam(1, $_SESSION['idClub'], PDO::PARAM_INT);
                        $stmt->bindParam(2, $idUser, PDO::PARAM_INT);
                        $stmt->bindValue(3, 'Convite para juntar-se ao ' .$clubName, PDO::PARAM_STR);
                        $stmt->bindValue(4, 'Pedido de Adesão para Staff no Clube ' .$clubName, PDO::PARAM_STR);
                        $stmt->bindValue(5, true, PDO::PARAM_BOOL);
                        $stmt->bindValue(6, true, PDO::PARAM_BOOL);
    
                        // Executar a query
                        $stmt->execute();
    
                        return ["code" => 201, "message" => "Convite enviado com sucesso!"];
                    }else{
                        return ["code" => 400, "message" => "Utilizador não encontrado."];                       
                    }
    
                    //echo json_encode($row);
    
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
}
?>