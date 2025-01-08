<?php

class Injury{
    
    private $pdo;
    private $table_name = "PLAYER_INJURY";

    public function __construct($db){
        $this->pdo = $db;
    }

    function getInjuries($data){
        try {
            if (isset($_SESSION['idClub'])) {
    
                $injuryList = [];

                $sql = "SELECT u.id as idUser, u.name, u.surname, pi.id, pi.injury, pi.startDate, pi.endDate FROM PLAYER_INJURY AS pi
                        JOIN USER AS u ON pi.idUser = u.id
                        WHERE u.idClub = ?;";

                try {
                    // Preparar a query
                    $stmt = $this->pdo->prepare($sql);
    
                    // Atribuir valores aos parâmetros
                    $stmt->bindParam(1, $_SESSION['idClub'], PDO::PARAM_INT);
    
                    // Executar a query
                    $stmt->execute();
    
                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        $injury = [
                            'id' => (int)$row['id'],
                            'injury' => $row['injury'],
                            'startDate' => $row['startDate'],
                            'endDate' => $row['endDate'],
                            'idUser' => $row['idUser'],
                            'name' => $row['name'],
                            'surname' => $row['surname']
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
}
?>