<?php

class Training{
    
    private $pdo;
    private $table_name = "TRAINING";

    public function __construct($db){
        $this->pdo = $db;
    }

    function getTrainingSessions($data){
        try {
            if (isset($_SESSION['idClub'])) {
    
                // Lista que armazenará os jogos
                $trainingList = [];
    
                $query = "SELECT * FROM TRAINING WHERE idClub = :idClub";
    
                try {
                    // Preparar a query
                    $stmt = $this->pdo->prepare($query);
    
                    // Atribuir valores aos parâmetros
                    $stmt->bindParam(':idClub', $_SESSION['idClub'], PDO::PARAM_INT);
    
                    // Executar a query
                    $stmt->execute();
    
                    // Buscar os resultados
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        // Criar um objeto Game a partir dos dados da tabela
                        $training = [
                            'id' => (int)$row['id'],
                            'idClub' => (int)$row['idClub'],
                            'trainingType' => $row['trainingType'],
                            'date' => $row['date'],
                            'startTime' => $row['startTime'],
                            'endTime' => $row['endTime'],
                        ];
    
                        // Adicionar o jogo à lista
                        $trainingList[] = $training;
                    }
    
                    return ["code" => 200, "trainingList" => $trainingList];
    
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

    function addTrainingSession($data){
        try {
            if (isset($data->trainingType, $data->date, $data->startTime, $data->endTime, $_SESSION['idClub'])) {
    
                $trainingType = filter_var($data->trainingType, FILTER_SANITIZE_STRING);
                $date = filter_var($data->date, FILTER_SANITIZE_STRING);
                $startTime = filter_var($data->startTime, FILTER_SANITIZE_STRING);
                $endTime = filter_var($data->endTime, FILTER_SANITIZE_STRING);
    
                $insertSql = "INSERT INTO TRAINING (idClub, trainingType, date, startTime, endTime) VALUES (?, ?, ?, ?, ?)";
                $checkAvailabilityTrainingSql = "SELECT * FROM TRAINING WHERE idClub = ? and date = ?;";
                $checkAvailabilityGameSql = "SELECT COUNT(*) FROM GAME WHERE idClub = ? and date = ?;";
    
                $dateConvert = DateTime::createFromFormat('Y-m-d', $date);
                $startTimeConvert = new DateTime($startTime);
                $endTimeConvert = new DateTime($endTime);

                if ($dateConvert < new DateTime('today')) {
                    return ["code" => 400, "message" => "Não é possível criar um treino para a data introduzida."];
                }
    
                if($startTimeConvert >= $endTimeConvert){
                    return ["code" => 400, "message" => "Conflito de horas."];
                }
    
                try {
    
                    //Verificar disponibilidade das datas
                    $stmt = $this->pdo->prepare($checkAvailabilityGameSql);
                    $stmt->bindParam(1, $_SESSION['idClub'], PDO::PARAM_INT);
                    $stmt->bindParam(2, $date, PDO::PARAM_STR);
                    $stmt->execute();
    
                    $count = $stmt->fetchColumn();
    
                    if ($count > 0){
                        return ["code" => 400, "message" => "Data indisponível. Jogo agendado para a data selecionada."];
                    }else{
    
                        $stmt = $this->pdo->prepare($checkAvailabilityTrainingSql);
                        $stmt->bindParam(1, $_SESSION['idClub'], PDO::PARAM_INT);
                        $stmt->bindParam(2, $date, PDO::PARAM_STR);
                        $stmt->execute();

                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                        if (!empty($rows)){
                            foreach ($rows as $row){
    
                                $rowStartTime = new DateTime($row['startTime']);
                                $rowEndTime = new DateTime($row['endTime']);
    
                                if (($startTimeConvert >= $rowStartTime && $startTimeConvert <= $rowEndTime) || ($endTimeConvert >= $rowStartTime && $endTimeConvert <= $rowEndTime)){
                                    return ["code" => 400, "message" => "Conflito de Treinos."];
                                }
                            }
                        }
    
                        // Preparar a query
                        $stmt = $this->pdo->prepare($insertSql);
    
                        // Atribuir valores aos parâmetros
                        $stmt->bindParam(1, $_SESSION['idClub'], PDO::PARAM_INT);
                        $stmt->bindParam(2, $trainingType, PDO::PARAM_STR);
                        $stmt->bindParam(3, $date, PDO::PARAM_STR);
                        $stmt->bindParam(4, $startTime, PDO::PARAM_STR);
                        $stmt->bindParam(5, $endTime, PDO::PARAM_STR);
    
                        // Executar a query
                        $stmt->execute();
   
                        //Query para obter todos os jogadores e depois enviar notificação do agendamento do treino
                        $getPlayersSql = "SELECT * FROM USER WHERE idClub = ? AND role = 3";

                        $stmt = $this->pdo->prepare($getPlayersSql);
                        $stmt->bindParam(1, $_SESSION['idClub'], PDO::PARAM_INT);
                        $stmt->execute();

                        //Notificações
                        $sqlNotification = "INSERT INTO NOTIFICATIONS (idClub, idUser, title, description, isInvite, isActive) VALUES (?, ?, ?, ?, ?, ?)";
                        $stmtNotification = $this->pdo->prepare($sqlNotification);

                        foreach ($stmt->fetchAll() as $row){
                            $stmtNotification->bindParam(1, $_SESSION['idClub'], PDO::PARAM_INT);
                            $stmtNotification->bindValue(2, (int)$row['id'], PDO::PARAM_INT);
                            $stmtNotification->bindValue(3, 'Agendamento de Treino', PDO::PARAM_STR);
                            $stmtNotification->bindValue(4, $trainingType .' agendado para o dia '. $date, PDO::PARAM_STR);
                            $stmtNotification->bindValue(5, false, PDO::PARAM_BOOL);
                            $stmtNotification->bindValue(6, true, PDO::PARAM_BOOL);
                            $stmtNotification->execute();
                        }
                        
                        return ["code" => 201, "message" => "Treino inserido com sucesso!"];
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

    function deleteTrainingSession($data){
        try {
            // Recebe os dados JSON do corpo da requisição
            $jsonData = file_get_contents('php://input');
            $data = json_decode($jsonData);
    
            if (isset($data->idTraining, $_SESSION['idClub'])) {
    
                $idTraining = filter_var($data->idTraining, FILTER_VALIDATE_INT);
                //DElete treino
                $sqlDeleteTraining = "DELETE FROM TRAINING WHERE id = ? AND idClub = ?;";
                //Delete performances
                $sqlDeleteTrainingPerformance = "DELETE FROM TRAINING_PERFORMANCE WHERE idTraining = ?;";
                
                try {
                    //Delete todas as performances do treino
                    $stmt = $this->pdo->prepare($sqlDeleteTrainingPerformance);
                    $stmt->bindParam(1, $idTraining, PDO::PARAM_INT);
                    $stmt->execute();

                    //Delete treino
                    $stmt = $this->pdo->prepare($sqlDeleteTraining);
                    $stmt->bindParam(1, $idTraining, PDO::PARAM_INT);
                    $stmt->bindParam(2, $_SESSION['idClub'], PDO::PARAM_INT);
                    $stmt->execute();

                    return ["code" => 200, "message" => "Treino removido com sucesso!"];
    
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

    function getPlayersTrainingsRatings($data){
        try {
            if (isset($_SESSION['idClub'], $data->idTraining)) {
    
                $idTraining = filter_var($data->idTraining, FILTER_VALIDATE_INT);
    
                $playerRatingList = [];
    
                $sql = "SELECT u.id as idUser, u.idClub, u.name, u.surname, tp.id as idTrainingPerformance, tp.idTraining, tp.pontuation, tp.description
                        FROM USER AS u
                        LEFT JOIN TRAINING_PERFORMANCE AS tp 
                            ON u.id = tp.idUser 
                            AND tp.idClub = ?
                            AND tp.idTraining = ?
                        WHERE u.idClub = ?
                        AND u.role = 3;";
    
                try {
    
                    $stmt = $this->pdo->prepare($sql);
    
                    // Atribuir valores aos parâmetros
                    $stmt->bindParam(1, $_SESSION['idClub'], PDO::PARAM_INT);
                    $stmt->bindParam(2, $idTraining, PDO::PARAM_INT);
                    $stmt->bindParam(3, $_SESSION['idClub'], PDO::PARAM_INT);
    
                    // Executar a query
                    $stmt->execute();
    
                    $rows = $stmt->fetchAll();
    
                    if (!empty($rows)){
                        foreach ($rows as $row){
                            $playerRating = [
                                'idUser' => (int)$row['idUser'],
                                'idTraining' => (int)$row['idTraining'],
                                'idClub' => (int)$row['idClub'],
                                'name' => $row['name'],
                                'surname' => $row['surname'],
                                'idTrainingPerformance' => (int)$row['idTrainingPerformance'],
                                'pontuation' => $row['pontuation'],
                                'description' => $row['description'],
                            ];
                            $playerRatingList[] = $playerRating;
                        }
                    }
    
                    return ["code" => 200, "playerRatingList" => $playerRatingList];
    
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

    function submitPlayersTrainingsRatings($data){
        try {
            if (isset($_SESSION['idClub'], $data->idTraining)) {
    
                $ratingListCreate = $data->ratingListCreate;
                $ratingListUpdate = $data->ratingListUpdate;
                $ratingListDelete = $data->ratingListDelete;
                $idTraining = filter_var($data->idTraining, FILTER_VALIDATE_INT);
    
                $sqlCreate = "INSERT INTO TRAINING_PERFORMANCE (idTraining, idClub, idUser, pontuation, description) VALUES (?, ?, ?, ?, ?)";
                $sqlUpdate = "UPDATE TRAINING_PERFORMANCE SET pontuation = ?, description = ? WHERE id = ? AND idClub = ? AND idUser = ? AND idTraining = ?;";
                $sqlDelete = "DELETE FROM TRAINING_PERFORMANCE WHERE id = ? AND idClub = ? AND idUser = ? AND idTraining = ?;";
    
                try {
    
                    //Criar
                    if (!empty($ratingListCreate)){
                        foreach ($ratingListCreate as $rating){
                            $idUser = filter_var($rating->idUser, FILTER_VALIDATE_INT);
                            $pontuation = filter_var($rating->pontuation, FILTER_SANITIZE_STRING);
                            $description = filter_var($rating->description, FILTER_SANITIZE_STRING);
    
                            $stmt = $this->pdo->prepare($sqlCreate);
                            $stmt->bindParam(1, $idTraining, PDO::PARAM_INT);
                            $stmt->bindParam(2, $_SESSION['idClub'], PDO::PARAM_INT);
                            $stmt->bindParam(3, $idUser, PDO::PARAM_INT);
                            $stmt->bindParam(4, $pontuation, PDO::PARAM_STR);
                            $stmt->bindParam(5, $description, PDO::PARAM_STR);
                            $stmt->execute();
                        }
                    }
    
                    //Update
                    if (!empty($ratingListUpdate)){
                        foreach ($ratingListUpdate as $rating){
    
                            $idTrainingPerformance = filter_var($rating->idTrainingPerformance, FILTER_VALIDATE_INT);
                            $idUser = filter_var($rating->idUser, FILTER_VALIDATE_INT);
                            $pontuation = filter_var($rating->pontuation, FILTER_SANITIZE_STRING);
                            $description = filter_var($rating->description, FILTER_SANITIZE_STRING);
    
                            $stmt = $this->pdo->prepare($sqlUpdate);
                            $stmt->bindParam(1, $pontuation, PDO::PARAM_STR);
                            $stmt->bindParam(2, $description, PDO::PARAM_STR);
                            $stmt->bindParam(3, $idTrainingPerformance, PDO::PARAM_INT);
                            $stmt->bindParam(4, $_SESSION['idClub'], PDO::PARAM_INT);
                            $stmt->bindParam(5, $idUser, PDO::PARAM_INT);
                            $stmt->bindParam(6, $idTraining, PDO::PARAM_INT);
                            $stmt->execute();
                        }
                    }
    
                    //Remover
                    if (!empty($ratingListDelete)){
                        foreach ($ratingListDelete as $rating){
    
                            $idTrainingPerformance = filter_var($rating->idTrainingPerformance, FILTER_VALIDATE_INT);
                            $idUser = filter_var($rating->idUser, FILTER_VALIDATE_INT);
                            $pontuation = filter_var($rating->pontuation, FILTER_SANITIZE_STRING);
                            $description = filter_var($rating->description, FILTER_SANITIZE_STRING);
    
                            $stmt = $this->pdo->prepare($sqlDelete);
                            $stmt->bindParam(1, $idTrainingPerformance, PDO::PARAM_INT);
                            $stmt->bindParam(2, $_SESSION['idClub'], PDO::PARAM_INT);
                            $stmt->bindParam(3, $idUser, PDO::PARAM_INT);
                            $stmt->bindParam(4, $idTraining, PDO::PARAM_INT);
                            $stmt->execute();
                        }
                    }
    
                    return ["code" => 200, "message" => "Dados introduzidos e atualizados com sucesso."];
    
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