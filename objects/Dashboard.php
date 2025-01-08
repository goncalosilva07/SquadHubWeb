<?php

class Dashboard{
    
    private $pdo;
    private $table_name = "CLUB";

    public function __construct($db){
        $this->pdo = $db;
    }

    function getDashboardInitialData($data){
        try {
            if (isset($_SESSION['idClub'])) {
    
                $game = null;
                $scorer = null;
                $assister = null;
                $clubStats = null;
    
                $getNextGameSql = "SELECT * FROM GAME
                                   WHERE date >= CURDATE() AND idClub = ?
                                   ORDER BY date ASC
                                   LIMIT 1;";

                $getBestGoalScorerSql = "SELECT u.name, u.surname, p.goals FROM USER AS u
                                        JOIN PLAYER AS p ON u.id = p.idUser
                                        WHERE u.idClub = ?
                                        ORDER BY p.goals DESC
                                        LIMIT 1;";

                $getBestAssisterSql = "SELECT u.name, u.surname, p.assists FROM USER AS u
                                      JOIN PLAYER AS p ON u.id = p.idUser
                                      WHERE u.idClub = ?
                                      ORDER BY p.assists DESC
                                      LIMIT 1;";

                $getClubStats = "SELECT * FROM CLUB
                                WHERE id = ?;";

                try {

                    //Buscar o próximo jogo
                    $stmt = $this->pdo->prepare($getNextGameSql);

                    $stmt->bindParam(1, $_SESSION['idClub'], PDO::PARAM_INT);
    
                    $stmt->execute();
    
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($row){             
                        $game = [
                            'id' => (int)$row['id'],
                            'idClub' => (int)$row['idClub'],
                            'opponent' => $row['opponent'],
                            'date' => $row['date'],
                            'time' => $row['time'],
                            'competition' => $row['competition'],
                            'local' => $row['local']
                        ];
                    }

                    //Buscar o melhor marcador
                    $stmt = $this->pdo->prepare($getBestGoalScorerSql);

                    $stmt->bindParam(1, $_SESSION['idClub'], PDO::PARAM_INT);
    
                    $stmt->execute();
    
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($row){             
                        $scorer = [
                            'name' => $row['name'],
                            'surname' => $row['surname'],
                            'goals' => $row['goals'],
                        ];
                    }

                    //Buscar o melhor assistente
                    $stmt = $this->pdo->prepare($getBestAssisterSql);

                    $stmt->bindParam(1, $_SESSION['idClub'], PDO::PARAM_INT);
    
                    $stmt->execute();
    
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($row){             
                        $assister = [
                            'name' => $row['name'],
                            'surname' => $row['surname'],
                            'assists' => $row['assists'],
                        ];
                    }

                    //Buscar as estatisticas da equipa
                    $stmt = $this->pdo->prepare($getClubStats);

                    $stmt->bindParam(1, $_SESSION['idClub'], PDO::PARAM_INT);
    
                    $stmt->execute();
    
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($row){             
                        $clubStats = [
                            'name' => $row['name'],
                            'abbreviation' => $row['abbreviation'],
                            'victories' => $row['victories'],
                            'defeats' => $row['defeats'],
                            'draws' => $row['draws'],
                        ];
                    }

                    $dashboardData = [
                        'game' => $game,
                        'scorer' => $scorer,
                        'assister' => $assister,
                        'clubStats' => $clubStats
                    ];

                    return ["code" => 200, "dashboardData" => $dashboardData];
    
                } catch (PDOException $e) {
                    // Em caso de erro, responder com erro
                    return ["code" => 500, "message" => $e->getMessage()];
                }
    
            } else {
                return ["code" => 400, "message" => 'Dados inválidos fornecidos.'];
            }
    
        } catch (Exception $e) {
            // Erro geral na requisição
            return ["code" => 500, "message" => $e->getMessage()];
        }
    }
}
?>