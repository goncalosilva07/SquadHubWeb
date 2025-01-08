<?php

class Player{
    
    private $pdo;
    private $table_name = "USER";

    public function __construct($db){
        $this->pdo = $db;
    }


    function getPlayers($data){
        try {
            if (isset($_SESSION['idClub'])) {
    
                // Lista que armazenará os jogos
                $playersList = [];
    
                $query = "SELECT u.id, u.idClub, u.username, u.name, u.surname, u.birthdate, u.email, u.phone, p.goals, p.assists FROM $this->table_name as u
                        JOIN PLAYER as p ON u.id = p.idUser
                        WHERE u.idClub = :idClub AND u.role = 3;";
    
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
                        $player = [
                            'id' => (int)$row['id'],
                            'idClub' => (int)$row['idClub'],
                            'username' => $row['username'],
                            'name' => $row['name'],
                            'surname' => $row['surname'],
                            'birthdate' => $row['birthdate'],
                            'email' => $row['email'],
                            'phone' => $row['phone'],
                            'goals' => $row['goals'],
                            'assists' => $row['assists']
                        ];
    
                        // Adicionar o jogo à lista
                        $playersList[] = $player;
                    }
    
                    return ["code" => 200, "playersList" => $playersList];
    
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

    function invitePlayer($data){
        try {
            if (isset($data->email, $_SESSION['idClub'])) {
    
                $email = filter_var($data->email, FILTER_VALIDATE_EMAIL);
    
                $insertSql = "INSERT INTO NOTIFICATIONS (idClub, idUser, title, description, isInvite, isActive) VALUES (?, ?, ?, ?, ?, ?)";
                $checkEmailSql = "SELECT u.id FROM USER as u WHERE email = ? AND role = 3;";
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
                        $stmt->bindValue(4, 'Pedido de Adesão para Jogador no Clube ' .$clubName, PDO::PARAM_STR);
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

    function deletePlayerFromClub($data){
        try {
            if (isset($data->idPlayer)) {
    
                $idPlayer = filter_var($data->idPlayer, FILTER_VALIDATE_INT);
                $sql = "UPDATE $this->table_name SET idClub = ? WHERE id = ? AND idClub = ?;";
                
                try {
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->bindValue(1, null, PDO::PARAM_NULL);
                    $stmt->bindParam(2, $idPlayer, PDO::PARAM_INT);
                    $stmt->bindParam(3, $_SESSION['idClub'], PDO::PARAM_INT);
                    $stmt->execute();
    
                    return ["code" => 200, "message" => "Jogador removido com sucesso!"];
    
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

    function getPlayerInjuries($data){
        try {
            if (isset($data->idPlayer)) {
    
                $injuryList = [];

                $idPlayer = filter_var($data->idPlayer, FILTER_VALIDATE_INT);
    
                $query = "SELECT * FROM PLAYER_INJURY WHERE idUser = ?";
    
                try {
                    // Preparar a query
                    $stmt = $this->pdo->prepare($query);
    
                    // Atribuir valores aos parâmetros
                    $stmt->bindParam(1, $idPlayer, PDO::PARAM_INT);
    
                    // Executar a query
                    $stmt->execute();
    
                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        $injury = [
                            'id' => (int)$row['id'],
                            'injury' => $row['injury'],
                            'startDate' => $row['startDate'],
                            'endDate' => $row['endDate'],
                        ];
    
                        $injuryList[] = $injury;
                    }

                    return ["code" => 200, "injuryList" => $injuryList];
    
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

    function addPlayerInjury($data){
        try {
            if (isset($data->injury, $data->startDate, $data->idPlayer)) {
    
                $injury = filter_var($data->injury, FILTER_SANITIZE_STRING);
                $startDate = filter_var($data->startDate, FILTER_SANITIZE_STRING);
                $endDate = (($data->endDate == "") ? null : filter_var($data->endDate, FILTER_SANITIZE_STRING));
                $idPlayer = filter_var($data->idPlayer, FILTER_VALIDATE_INT);
    
                $insertSql = "INSERT INTO PLAYER_INJURY (idUser, injury, startDate, endDate) VALUES (?, ?, ?, ?)";
                
                try {
            
                    // Preparar a query
                    $stmt = $this->pdo->prepare($insertSql);

                    // Atribuir valores aos parâmetros
                    $stmt->bindParam(1, $idPlayer, PDO::PARAM_INT);
                    $stmt->bindParam(2, $injury, PDO::PARAM_STR);
                    $stmt->bindParam(3, $startDate, PDO::PARAM_STR);
                    $stmt->bindParam(4, $endDate, PDO::PARAM_STR);
                    
                    // Executar a query
                    $stmt->execute();

                    return ["code" => 201, "message" => "Lesão criada com sucesso!"];    
    
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

    function deletePlayerInjury($data){
        try {
            if (isset($data->idPlayer, $data->idInjury)) {
    
                $idPlayer = filter_var($data->idPlayer, FILTER_VALIDATE_INT);
                $idInjury = filter_var($data->idInjury, FILTER_VALIDATE_INT);

                $sql = "DELETE FROM PLAYER_INJURY WHERE id = ? AND idUser = ?;";

                try {
                    $stmt = $this->pdo->prepare($sql);
                  
                    $stmt->bindParam(1, $idInjury, PDO::PARAM_INT);
                    $stmt->bindParam(2, $idPlayer, PDO::PARAM_INT);

                    $stmt->execute();
    
                    return ["code" => 200, "message" => "Lesão removida com sucesso!"];
    
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

    function getPlayerInjuryInfo($data){
        try {
            if (isset($data->idPlayer, $data->idInjury)) {
    
                $idPlayer = filter_var($data->idPlayer, FILTER_VALIDATE_INT);
                $idInjury = filter_var($data->idInjury, FILTER_VALIDATE_INT);
    
                $query = "SELECT * FROM PLAYER_INJURY WHERE id = ? AND idUser = ?;";
    
                try {
                    // Preparar a query
                    $stmt = $this->pdo->prepare($query);
    
                    // Atribuir valores aos parâmetros
                    $stmt->bindParam(1, $idInjury, PDO::PARAM_INT);
                    $stmt->bindParam(2, $idPlayer, PDO::PARAM_INT);
    
                    // Executar a query
                    $stmt->execute();
    
                    $row = $stmt->fetch();

                    if($row){
                        $injury = [
                            'id' => (int)$row['id'],
                            'injury' => $row['injury'],
                            'startDate' => $row['startDate'],
                            'endDate' => $row['endDate'],
                        ];
                        return ["code" => 200, "injuryInfo" => $injury];
                    }else{
                        return ["code" => 400, "message" => "Erro ao aceder as informações da lesão."];
                    }

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

    function editPlayerInjury($data){
        try {
            if (isset($data->injury, $data->startDate, $data->idPlayer, $data->idInjury)) {
    
                $injury = filter_var($data->injury, FILTER_SANITIZE_STRING);
                $startDate = filter_var($data->startDate, FILTER_SANITIZE_STRING);
                $endDate = (($data->endDate == "") ? null : filter_var($data->endDate, FILTER_SANITIZE_STRING));
                $idPlayer = filter_var($data->idPlayer, FILTER_VALIDATE_INT);
                $idInjury = filter_var($data->idInjury, FILTER_VALIDATE_INT);

                $sql = "UPDATE PLAYER_INJURY SET injury = ?, startDate = ?, endDate = ? WHERE id = ? AND idUser = ?;";

                try {
            
                    // Preparar a query
                    $stmt = $this->pdo->prepare($sql);

                    // Atribuir valores aos parâmetros
                    $stmt->bindParam(1, $injury, PDO::PARAM_STR);
                    $stmt->bindParam(2, $startDate, PDO::PARAM_STR);
                    $stmt->bindParam(3, $endDate, PDO::PARAM_STR);
                    $stmt->bindParam(4, $idInjury, PDO::PARAM_INT);
                    $stmt->bindParam(5, $idPlayer, PDO::PARAM_INT);
                    
                    // Executar a query
                    $stmt->execute();

                    return ["code" => 200, "message" => "Lesão atualizada com sucesso!"];    
    
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

    function getPlayersFromGameCall($data){
        try {
            if (isset($data->idGameCall)) {
               
                $idGameCall = filter_var($data->idGameCall, FILTER_VALIDATE_INT);
                $playerList = [];

                $sql = "SELECT gcp.id, gcp.idGameCall, gcp.idUser, gcp.position, u.name, u.surname FROM GAME_CALL_PLAYERS gcp
                        JOIN USER AS u ON gcp.idUser = u.id  
                        WHERE idGameCall = ?;";

                try {
                    // Preparar a query
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->bindParam(1, $idGameCall, PDO::PARAM_INT);
                    $stmt->execute();


                    foreach ($stmt->fetchAll() as $row){
                        $player = [
                            'idUser' => $row['idUser'],
                            'name' => $row['name'],
                            'surname' => $row['surname']
                        ];
                        $playerList[] = $player;
                    }

                    return ["code" => 200, "playerList" => $playerList];    
    
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

    function getPlayersWithoutInjury($data){
        try {
            if (isset($_SESSION['idClub'], $data->gameDate)) {
    
                // Lista que armazenará os jogos
                $playersList = [];
    
                $sql = "SELECT u.id, u.idClub, u.username, u.name, u.surname, u.birthdate, u.email, u.phone, p.goals, p.assists
                        FROM USER AS u
                        JOIN PLAYER AS p ON u.id = p.idUser
                        LEFT JOIN PLAYER_INJURY AS pi ON u.id = pi.idUser AND ? BETWEEN pi.startDate AND COALESCE(pi.endDate, ?)
                        WHERE u.idClub = ?
                        AND u.role = 3
                        AND pi.idUser IS NULL;";
    
                try {
                    // Preparar a query
                    $stmt = $this->pdo->prepare($sql);
    
                    // Atribuir valores aos parâmetros
                    $stmt->bindParam(1, $data->gameDate, PDO::PARAM_STR);
                    $stmt->bindParam(2, $data->gameDate, PDO::PARAM_STR);
                    $stmt->bindParam(3, $_SESSION['idClub'], PDO::PARAM_INT);
    
                    // Executar a query
                    $stmt->execute();
    
                    // Buscar os resultados
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        // Criar um objeto palyer a partir dos dados da tabela
                        $player = [
                            'id' => (int)$row['id'],
                            'idClub' => (int)$row['idClub'],
                            'username' => $row['username'],
                            'name' => $row['name'],
                            'surname' => $row['surname'],
                            'birthdate' => $row['birthdate'],
                            'email' => $row['email'],
                            'phone' => $row['phone'],
                            'goals' => $row['goals'],
                            'assists' => $row['assists']
                        ];
    
                        // Adicionar o player à lista
                        $playersList[] = $player;
                    }
    
                    return ["code" => 200, "playersList" => $playersList];
    
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

    function getPlayerPerformance($data){
        try {
            if (isset($_SESSION['idClub'])) {
    
                $performanceList = [];
    
                $sql = "SELECT T.date, T.trainingType, TP.pontuation, TP.description FROM TRAINING_PERFORMANCE AS TP
                        JOIN TRAINING AS T ON TP.idTraining = T.id
                        WHERE TP.idUser = ?
                        ORDER BY T.date DESC;";
    
                $idUser = null;

                if ($_SESSION['role'] == 3){
                    $idUser = $_SESSION['id'];
                }else{
                    $idUser = $data->idUser;
                }

                try {
                    // Preparar a query
                    $stmt = $this->pdo->prepare($sql);
    
                    // Atribuir valores aos parâmetros
                    $stmt->bindParam(1, $idUser, PDO::PARAM_INT);
    
                    // Executar a query
                    $stmt->execute();
    
                    // Buscar os resultados
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        // Criar um objeto palyer a partir dos dados da tabela
                        $performance = [
                            'date' => $row['date'],
                            'trainingType' => $row['trainingType'],
                            'pontuation' => $row['pontuation'],
                            'description' => $row['description']
                        ];
                        $performanceList[] = $performance;
                    }
    
                    return ["code" => 200, "performanceList" => $performanceList];
    
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