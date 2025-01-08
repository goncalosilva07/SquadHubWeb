<?php
class Game{
    
    private $pdo;
    private $table_name = "GAME";

    public $id;
    public $idClub;
    public $opponent;
    public $date;
    public $time;
    public $competition;
    public $local;

    public function __construct($db){
        $this->pdo = $db;
    }

    function getGames($data){
        try {
            if (isset($_SESSION['idClub'])) {
    
                // Lista que armazenará os jogos
                $gamesList = [];
    
                $query = "SELECT * FROM $this->table_name WHERE idClub = :idClub
                        ORDER BY date DESC";
    
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
                        $game = [
                            'id' => (int)$row['id'],
                            'idClub' => (int)$row['idClub'],
                            'opponent' => $row['opponent'],
                            'date' => $row['date'],
                            'time' => $row['time'],
                            'competition' => $row['competition'],
                            'local' => $row['local']
                        ];
    
                        // Adicionar o jogo à lista
                        $gamesList[] = $game;
                    }
    
                    return ["code" => 200, "gamesList" => $gamesList];
    
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

    function addGame($data){
        try {
            if (isset($data->opponent, $data->date, $data->time, $data->local, $data->competition, $_SESSION['idClub'])) {
    
                $opponent = filter_var($data->opponent, FILTER_SANITIZE_STRING);
                $date = filter_var($data->date, FILTER_SANITIZE_STRING);
                $time = filter_var($data->time, FILTER_SANITIZE_STRING);
                $local = filter_var($data->local,FILTER_SANITIZE_STRING);
                $competition = filter_var($data->competition, FILTER_SANITIZE_STRING);
    
                $insertSql = "INSERT INTO $this->table_name (idClub, opponent, competition, local, date, time) VALUES (?, ?, ?, ?, ?, ?)";
                $checkAvailabilitySql = "SELECT COUNT(*) FROM $this->table_name WHERE idClub = ? and date = ?;";
                $checkAvailabilityTrainingSql = "SELECT COUNT(*) FROM TRAINING WHERE idClub = ? and date = ?;";
    
                $dateConvert = DateTime::createFromFormat('Y-m-d', $date);
    
                if ($dateConvert < new DateTime('today')) {
                    return ["code" => 400, "message" => "Não é possível criar um jogo para a data introduzida."];
                    die;
                }

                try {
    
                    //Verificar disponibilidade das datas
                    $stmt = $this->pdo->prepare($checkAvailabilitySql);
                    $stmt->bindParam(1, $_SESSION['idClub'], PDO::PARAM_INT);
                    $stmt->bindParam(2, $date, PDO::PARAM_STR);
                    $stmt->execute();
    
                    $count = $stmt->fetchColumn();
    
                    if ($count > 0){
                        return ["code" => 400, "message" => "Data indisponível. Jogo já agendado para a data selecionada."];
                        die;
                    }else{
    
                        $stmt = $this->pdo->prepare($checkAvailabilityTrainingSql);
                        $stmt->bindParam(1, $_SESSION['idClub'], PDO::PARAM_INT);
                        $stmt->bindParam(2, $date, PDO::PARAM_STR);
                        $stmt->execute();
    
                        $count = $stmt->fetchColumn();
    
                        if ($count > 0) {
                            return ["code" => 400, "message" => "Data indisponível. Treino já agendado para a data selecionada."];
                            die;
                        }
    
                        // Preparar a query
                        $stmt = $this->pdo->prepare($insertSql);
    
                        // Atribuir valores aos parâmetros
                        $stmt->bindParam(1, $_SESSION['idClub'], PDO::PARAM_INT);
                        $stmt->bindParam(2, $opponent, PDO::PARAM_STR);
                        $stmt->bindParam(3, $competition, PDO::PARAM_STR);
                        $stmt->bindParam(4, $local, PDO::PARAM_STR);
                        $stmt->bindParam(5, $date, PDO::PARAM_STR);
                        $stmt->bindParam(6, $time, PDO::PARAM_STR);
    
                        // Executar a query
                        $stmt->execute();

                        return ["code" => 201, "message" => "Jogo inserido com sucesso!"];
                    }
    
                    //echo json_encode($row);
    
                } catch (PDOException $e) {
                    return ["code" => 500, "message" => $e->getMessage()];
                }
    
            } else {
                return ["code" => 400, "message" => 'Dados inválidos fornecidos.'];
            }
    
        } catch (Exception $e) {
            return ["code" => 500, "message" => $e->getMessage()];
        }
    }

    function deleteGame($data){
        try {
            if (isset($data->idGame)) {
    
                $idGame = filter_var($data->idGame, FILTER_VALIDATE_INT);
                $idGameStatistics = null;
                $idGameCall = null;
                
                //Delete estatisticas
                //Saber o id na tabela estatisticas
                $sqlGameStatisticsInfo = "SELECT * FROM GAME_STATISTICS WHERE idGame = ?;";
                $sqlDeleteGameGoalsAssists = "DELETE FROM GAME_GOALS_ASSISTS WHERE idGameStatistics = ?;";
                $sqlDeleteGameStatistics = "DELETE FROM GAME_STATISTICS WHERE idGame = ?;";

                //Delete convocatoria
                $sqlGameCallInfo = "SELECT * FROM GAME_CALL WHERE idGame = ?;";
                $sqlDeleteGameCallPlayers = "DELETE FROM GAME_CALL_PLAYERS WHERE idGameCall = ?;";
                $sqlDeleteGameCall = "DELETE FROM GAME_CALL WHERE idGame = ?;";

                //Delete Game
                $sqlDeleteGame = "DELETE FROM $this->table_name WHERE id = ? AND idClub = ?;";


                try {
      
                    //Delete estatisticas
                    $stmt = $this->pdo->prepare($sqlGameStatisticsInfo);
                    $stmt->bindParam(1, $idGame, PDO::PARAM_INT);
                    $stmt->execute();

                    $row = $stmt->fetch();

                    if ($row){
                        $idGameStatistics = $row['id'];

                        //Delete Game Goals and Assists
                        $stmt = $this->pdo->prepare($sqlDeleteGameGoalsAssists);
                        $stmt->bindParam(1, $idGameStatistics, PDO::PARAM_INT);
                        $stmt->execute();
    
                        //Delete Game estatisticas
                        $stmt = $this->pdo->prepare($sqlDeleteGameStatistics);
                        $stmt->bindParam(1, $idGame, PDO::PARAM_INT);
                        $stmt->execute();
                    }

                    //Delete convocatoria
                    $stmt = $this->pdo->prepare($sqlGameCallInfo);
                    $stmt->bindParam(1, $idGame, PDO::PARAM_INT);
                    $stmt->execute();

                    $row = $stmt->fetch();

                    if ($row){
                        $idGameCall = $row['id'];

                        //Delete Game Goals and Assists
                        $stmt = $this->pdo->prepare($sqlDeleteGameCallPlayers);
                        $stmt->bindParam(1, $idGameCall, PDO::PARAM_INT);
                        $stmt->execute();
    
                        //Delete Game estatisticas
                        $stmt = $this->pdo->prepare($sqlDeleteGameCall);
                        $stmt->bindParam(1, $idGame, PDO::PARAM_INT);
                        $stmt->execute();
                    }

                    //Delete Game
                    $stmt = $this->pdo->prepare($sqlDeleteGame);
                    $stmt->bindParam(1, $idGame, PDO::PARAM_INT);
                    $stmt->bindParam(2, $_SESSION['idClub'], PDO::PARAM_INT);
                    $stmt->execute();
    
                    return ["code" => 200, "message" => "Jogo removido com sucesso!"];
    
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

    function getGameCallData($data){
        try {
            if (isset($_SESSION['idClub'], $data->idGame)) {
          
                $idGame = filter_var($data->idGame, FILTER_VALIDATE_INT);
                
                $idGameCall = null;
                $playersList = [];

                $getGameCallSql = "SELECT * FROM GAME_CALL WHERE idGame = ?;";
                $getGameCallPlayersSql = "SELECT gcp.id, gcp.idGameCall, gcp.idUser, gcp.position, u.name, u.surname FROM GAME_CALL_PLAYERS gcp
                                        JOIN USER AS u ON gcp.idUser = u.id  
                                        WHERE idGameCall = ?;";
    
                try {

                    //Buscar o id da convocatória
                    $stmt = $this->pdo->prepare($getGameCallSql);
                    $stmt->bindParam(1, $idGame, PDO::PARAM_INT);
                    $stmt->execute();
    
                    $row = $stmt->fetch();

                    if ($row){
                        $idGameCall = (int)$row['id'];
                    }else{
                        //isCreated serve para saber se a convocatoria ja foi criada ou não
                        return ["code" => 200, "isCreated" => false, "message" => "Jogo sem convocatória"];
                        exit();
                    }

                    $stmt = $this->pdo->prepare($getGameCallPlayersSql);
                    $stmt->bindParam(1, $idGameCall, PDO::PARAM_INT);
                    $stmt->execute();

                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        $player = [
                            'id' => (int)$row['id'],
                            'idGameCall' => (int)$row['idGameCall'],
                            'idUser' => (int)$row['idUser'],
                            'position' => $row['position'],
                            'name' => $row['name'],
                            'surname' => $row['surname'],
                        ];
    
                        $playersList[] = $player;
                    }

                    return ["code" => 200, "isCreated" => true, "playersList" => $playersList];
    
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
    
    function submitGameCall($data){
        try {
            if (isset($data->idGame, $data->playersList)) {
    
                $idGame = filter_var($data->idGame, FILTER_VALIDATE_INT);
                $idGameCall = null;
                $getGameCallSql = "SELECT * FROM GAME_CALL WHERE idGame = ?;";

                try {
                    //Buscar o id da convocatória
                    $stmt = $this->pdo->prepare($getGameCallSql);
                    $stmt->bindParam(1, $idGame, PDO::PARAM_INT);
                    $stmt->execute();
    
                    $row = $stmt->fetch();

                    if ($row){
                        $idGameCall = (int)$row['id'];

                        //Apagar todos os jogadores associados a convocatoria que estavam na bd
                        $sql = "DELETE FROM GAME_CALL_PLAYERS WHERE idGameCall = ?;";

                        $stmt = $this->pdo->prepare($sql);
                        $stmt->bindParam(1, $idGameCall, PDO::PARAM_INT);
                        $stmt->execute();

                    }else{

                        $sql = "INSERT INTO GAME_CALL (idGame) VALUES (?);";

                        $stmt = $this->pdo->prepare($sql);
                        $stmt->bindParam(1, $idGame, PDO::PARAM_INT);
                        $stmt->execute();

                        $idGameCall = $this->pdo->lastInsertId();      
                    }
      
                    $sqlGetGameInfo = "SELECT * FROM GAME WHERE id = ?;";

                    $stmt = $this->pdo->prepare($sqlGetGameInfo);
                    $stmt->bindParam(1, $idGame, PDO::PARAM_INT);
                    $stmt->execute();

                    $row = $stmt->fetch();

                    $game_opponent = null;
                    $game_date = null;

                    if ($row){
                        $game_opponent = $row['opponent'];
                        $game_date = $row['date'];
                    }else{
                        return ["code" => 400, "message" => "Erro ao tentar aceder as informações do jogo."];
                    }

                    //Adicionar os jogadores
                    $sql = "INSERT INTO GAME_CALL_PLAYERS (idGameCall, idUser, position) VALUES (?, ?, ?);";
                    $stmt = $this->pdo->prepare($sql);

                    //Notificações
                    $sqlNotification = "INSERT INTO NOTIFICATIONS (idClub, idUser, title, description, isInvite, isActive) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmtNotification = $this->pdo->prepare($sqlNotification);

                    foreach($data->playersList as $player){

                        $idUser = filter_var($player->id, FILTER_VALIDATE_INT);
                        $position = filter_var($player->position, FILTER_SANITIZE_SPECIAL_CHARS);

                        $stmt->bindParam(1, $idGameCall, PDO::PARAM_INT);
                        $stmt->bindParam(2, $idUser, PDO::PARAM_INT);
                        $stmt->bindParam(3, $position, PDO::PARAM_STR);
                        $stmt->execute();

                        //Enviar Notificação para o jogador
                        $stmtNotification->bindParam(1, $_SESSION['idClub'], PDO::PARAM_INT);
                        $stmtNotification->bindParam(2, $idUser, PDO::PARAM_INT);
                        $stmtNotification->bindValue(3, 'Convocatória', PDO::PARAM_STR);
                        $stmtNotification->bindValue(4, 'Foi convocado para o jogo frente ao ' . $game_opponent . ' no dia ' . $game_date, PDO::PARAM_STR);
                        $stmtNotification->bindValue(5, false, PDO::PARAM_BOOL);
                        $stmtNotification->bindValue(6, true, PDO::PARAM_BOOL);
                        $stmtNotification->execute();
                    }

                    return ["code" => 200, "message" => "Convocatória efetuada com sucesso."];

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

    function getGameStatistics($data){
        try {
            if (isset($_SESSION['idClub'], $data->idGame)) {

                $idGame = filter_var($data->idGame, FILTER_VALIDATE_INT);
                $idGameCall = null;

                $game = null;
                $statistics = null;

                $playerList = [];
                $scorerList = [];

                $getGameSql = "SELECT * FROM GAME WHERE id = ? AND idClub = ? LIMIT 1;";
                $getGameStatisticsSql = "SELECT * FROM GAME_STATISTICS WHERE idGame = ? LIMIT 1;";         
                $getGameCallPlayers = "SELECT gcp.id, gcp.idGameCall, gcp.idUser, gcp.position, u.name, u.surname FROM GAME_CALL_PLAYERS gcp
                                        JOIN USER AS u ON gcp.idUser = u.id  
                                        WHERE idGameCall = ?;";
                $getGameCall = "SELECT * FROM GAME_CALL WHERE idGame = ?;";

                $getGameGoals = "SELECT gg.id, gg.idGameStatistics, gg.idUser, gg.goals, gg.assists, u.name, u.surname FROM GAME_GOALS_ASSISTS gg
                                JOIN USER AS u ON gg.idUser = u.id  
                                WHERE idGameStatistics = ?;";
    
                try {

                    $stmt = $this->pdo->prepare($getGameSql);
                    $stmt->bindParam(1, $idGame, PDO::PARAM_INT);
                    $stmt->bindParam(2, $_SESSION['idClub'], PDO::PARAM_INT);
                    $stmt->execute();
    
                    $row = $stmt->fetch();
            
                    if($row){
                        $game = [
                            'id' => (int)$row['id'],
                            'idClub' => (int)$row['idClub'],
                            'opponent' => $row['opponent'],
                            'date' => $row['date'],
                            'time' => $row['time'],
                            'competition' => $row['competition'],
                            'local' => $row['local']
                        ];

                    }else{
                        return ["code" => 400, "message" => 'Erro. Problemas ao aceder a informação do jogo.']; 
                    }


                    $stmt = $this->pdo->prepare($getGameStatisticsSql);
                    $stmt->bindParam(1, $idGame, PDO::PARAM_INT);
                    $stmt->execute();

                    $row = $stmt->fetch();

                    if($row){
                        $statistics = [
                            'id' => (int)$row['id'],
                            'idGame' => (int)$row['idGame'],
                            'goals_scored' => (int)$row['goals_scored'],
                            'goals_conceded' => (int)$row['goals_conceded'],
                            'ball_posession' => (int)$row['ball_posession'],
                            'shots' => (int)$row['shots'],
                            'shots_goal' => (int)$row['shots_goal'],
                            'fouls' => (int)$row['fouls'],
                            'passes' => (int)$row['passes']
                        ];                  
                    }

                    $stmt = $this->pdo->prepare($getGameCall);
                    $stmt->bindParam(1, $idGame, PDO::PARAM_INT);
                    $stmt->execute();

                    $row = $stmt->fetch();

                    if ($row){
                        $idGameCall = $row['id'];

                        $stmt = $this->pdo->prepare($getGameCallPlayers);
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

                        if ($statistics != null){
                            $stmt = $this->pdo->prepare($getGameGoals);
                            $stmt->bindParam(1, $statistics['id'], PDO::PARAM_INT);
                            $stmt->execute();
    
                            foreach ($stmt->fetchAll() as $row){
                                $player = [
                                    'id' => $row['id'],
                                    //id - Id do utilziador
                                    'idUser' => $row['idUser'],
                                    'name' => $row['name'] . " " . $row['surname'],
                                    'goals' => $row['goals'],
                                    'assists' => $row['assists'],
                                    'isSave' => true //serve para saber se estes dados já estavam guardados na BD
                                ];
                                $scorerList[] = $player;
                            }
                        }
                    }
     
                    $gameStatistics = [
                        'gameData' => $game,
                        'gamePlayers' => $playerList,
                        'gameScorers' => $scorerList,
                        'gameStatistics' => $statistics
                    ];

                    return ["code" => 200, "gameStatistics" => $gameStatistics];
    
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
    
    function submitGameStatistics($data){
        try {
            if (isset($data->idGame, $data->goals_scored, $data->goals_conceded, 
            $data->ball_posession, $data->shots, $data->shots_goal,
            $data->fouls, $data->passes)) {
    
                $idGame = filter_var($data->idGame, FILTER_VALIDATE_INT);
                $goals_scored = filter_var($data->goals_scored, FILTER_VALIDATE_INT);
                $goals_conceded = filter_var($data->goals_conceded, FILTER_VALIDATE_INT);
                $ball_posession = filter_var($data->ball_posession, FILTER_VALIDATE_INT);
                $shots = filter_var($data->shots, FILTER_VALIDATE_INT);
                $shots_goal = filter_var($data->shots_goal, FILTER_VALIDATE_INT);
                $fouls = filter_var($data->fouls, FILTER_VALIDATE_INT);
                $passes = filter_var($data->passes, FILTER_VALIDATE_INT);



                if (isset($data->idGameStatistics)){

                    try{

                        $idGameStatistics = filter_var($data->idGameStatistics, FILTER_VALIDATE_INT);

                        //Verificar o resultado anterior para depois saber se é preciso adiconar ou alterar as vitorias, empates e derrotas do clube
                        $getLastGameResultSql = "SELECT goals_scored, goals_conceded FROM GAME_STATISTICS WHERE id = ?";
                        
                        $stmt = $this->pdo->prepare($getLastGameResultSql);
                        $stmt->bindParam(1, $idGameStatistics, PDO::PARAM_INT);
                        $stmt->execute();

                        $row = $stmt->fetch();
                        $lastGoalsScored = $row['goals_scored'];
                        $lastGoalsConceded = $row['goals_conceded'];

                        $updateClubLastResult = null;

                        if ($lastGoalsScored > $lastGoalsConceded){    
                            $updateClubLastResult = "UPDATE CLUB SET victories = victories - 1 WHERE id = ?;";                      
                        }else if ($lastGoalsScored < $lastGoalsConceded){
                            $updateClubLastResult = "UPDATE CLUB SET defeats = defeats - 1 WHERE id = ?;"; 
                        }else if ($lastGoalsScored == $lastGoalsConceded){
                            $updateClubLastResult = "UPDATE CLUB SET draws = draws - 1 WHERE id = ?;"; 
                        }

                        $stmt = $this->pdo->prepare($updateClubLastResult);
                        $stmt->bindParam(1, $_SESSION['idClub'], PDO::PARAM_INT);
                        $stmt->execute();

                        $sql = "UPDATE GAME_STATISTICS
                                SET goals_scored = ?, goals_conceded = ?, ball_posession = ?, shots = ?,
                                shots_goal = ?, fouls = ?, passes = ?
                                WHERE id = ? AND idGame = ?;";
    
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->bindParam(1, $goals_scored, PDO::PARAM_INT);
                        $stmt->bindParam(2, $goals_conceded, PDO::PARAM_INT);
                        $stmt->bindParam(3, $ball_posession, PDO::PARAM_INT);
                        $stmt->bindParam(4, $shots, PDO::PARAM_INT);
                        $stmt->bindParam(5, $shots_goal, PDO::PARAM_INT);
                        $stmt->bindParam(6, $fouls, PDO::PARAM_INT);
                        $stmt->bindParam(7, $passes, PDO::PARAM_INT);
                        $stmt->bindParam(8, $idGameStatistics, PDO::PARAM_INT);
                        $stmt->bindParam(9, $idGame, PDO::PARAM_INT);
                        $stmt->execute();

                        $sqlUpdateClub = null;

                        if ($goals_scored > $goals_conceded){
                            $sqlUpdateClub = "UPDATE CLUB SET victories = victories + 1 WHERE id = ?;";                          
                        }else if ($goals_scored < $goals_conceded){
                            $sqlUpdateClub = "UPDATE CLUB SET defeats = defeats + 1 WHERE id = ?;";
                        }else if ($goals_scored == $goals_conceded){
                            $sqlUpdateClub = "UPDATE CLUB SET draws = draws + 1 WHERE id = ?;";
                        }

                        $stmt = $this->pdo->prepare($sqlUpdateClub);
                        $stmt->bindParam(1, $_SESSION['idClub'], PDO::PARAM_INT);
                        $stmt->execute();

                        $sql = "DELETE FROM GAME_GOALS_ASSISTS WHERE idGameStatistics = ?;";
    
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->bindParam(1, $idGameStatistics, PDO::PARAM_INT);
                        $stmt->execute();
    
                        $sql = "INSERT INTO GAME_GOALS_ASSISTS (idGameStatistics, idUser, goals, assists)
                                VALUES (?, ?, ?, ?);";
                        $sqlUpdateGoalsAndAssists = "UPDATE PLAYER SET goals = goals + ?, assists = assists + ? WHERE idUser = ?;";

                        $stmt = $this->pdo->prepare($sql);
                        $stmtUpdate = $this->pdo->prepare($sqlUpdateGoalsAndAssists);
    
                        foreach ($data->scorerList as $player){
                            //echo $idGameStatistics;
                            $stmt->bindValue(1, $idGameStatistics, PDO::PARAM_INT);
                            $stmt->bindValue(2, $player->idUser, PDO::PARAM_INT);
                            $stmt->bindValue(3, $player->goals, PDO::PARAM_INT);
                            $stmt->bindValue(4, $player->assists, PDO::PARAM_INT);
                            $stmt->execute();

                            $stmtUpdate->bindValue(1, $player->goals, PDO::PARAM_INT);
                            $stmtUpdate->bindValue(2, $player->assists, PDO::PARAM_INT);
                            $stmtUpdate->bindValue(3, $player->idUser, PDO::PARAM_INT);
                            $stmtUpdate->execute();
                        }

                        return ["code" => 200, "message" => "Estatísticas do jogo atualizadas com sucesso."];

                    } catch (PDOException $e) {
                        return ["code" => 500, "message" => $e->getMessage()];
                    }

                }else{

                    try{

                        $sql = "INSERT INTO GAME_STATISTICS (idGame, goals_scored, goals_conceded, ball_posession, shots, shots_goal, fouls, passes)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?);";

                        $stmt = $this->pdo->prepare($sql);
                        $stmt->bindParam(1, $idGame, PDO::PARAM_INT);
                        $stmt->bindParam(2, $goals_scored, PDO::PARAM_INT);
                        $stmt->bindParam(3, $goals_conceded, PDO::PARAM_INT);
                        $stmt->bindParam(4, $ball_posession, PDO::PARAM_INT);
                        $stmt->bindParam(5, $shots, PDO::PARAM_INT);
                        $stmt->bindParam(6, $shots_goal, PDO::PARAM_INT);
                        $stmt->bindParam(7, $fouls, PDO::PARAM_INT);
                        $stmt->bindParam(8, $passes, PDO::PARAM_INT);
                        $stmt->execute();

                        $idGameStatistics = $this->pdo->lastInsertId();

                        $sqlUpdateClub = null;

                        if ($goals_scored > $goals_conceded){
                            $sqlUpdateClub = "UPDATE CLUB SET victories = victories + 1 WHERE id = ?;";                          
                        }else if ($goals_scored < $goals_conceded){
                            $sqlUpdateClub = "UPDATE CLUB SET defeats = defeats + 1 WHERE id = ?;";
                        }else if ($goals_scored == $goals_conceded){
                            $sqlUpdateClub = "UPDATE CLUB SET draws = draws + 1 WHERE id = ?;";
                        }

                        $stmt = $this->pdo->prepare($sqlUpdateClub);
                        $stmt->bindParam(1, $_SESSION['idClub'], PDO::PARAM_INT);
                        $stmt->execute();

                        $sql = "INSERT INTO GAME_GOALS_ASSISTS (idGameStatistics, idUser, goals, assists)
                                VALUES (?, ?, ?, ?);";
                        $sqlUpdateGoalsAndAssists = "UPDATE PLAYER SET goals = goals + ?, assists = assists + ? WHERE idUser = ?;";
    
                        $stmt = $this->pdo->prepare($sql);
                        $stmtUpdate = $this->pdo->prepare($sqlUpdateGoalsAndAssists);
    
                        foreach ($data->scorerList as $player){
                            $stmt->bindValue(1, $idGameStatistics, PDO::PARAM_INT);
                            $stmt->bindValue(2, $player->idUser, PDO::PARAM_INT);
                            $stmt->bindValue(3, $player->goals, PDO::PARAM_INT);
                            $stmt->bindValue(4, $player->assists, PDO::PARAM_INT);
                            $stmt->execute();

                            $stmtUpdate->bindValue(1, $player->goals, PDO::PARAM_INT);
                            $stmtUpdate->bindValue(2, $player->assists, PDO::PARAM_INT);
                            $stmtUpdate->bindValue(3, $player->idUser, PDO::PARAM_INT);
                            $stmtUpdate->execute();
                        }

                        return ["code" => 200, "message" => "Estatísticas do jogo submetidas com sucesso."];

                    }catch (PDOException $e) {
                        return ["code" => 500, "message" => $e->getMessage()];
                    }
                }

            } else {
                return ["code" => 400, "message" => "Dados inválidos fornecidos."];
            }
    
        } catch (Exception $e) {
            return ["code" => 500, "message" => $e->getMessage()];
        }
    }

    function deleteScorer($data){
        try {
            if (isset($data->id, $data->idUser, $data->goals, $data->assists)) {
    
                $id = filter_var($data->id, FILTER_VALIDATE_INT);
                $idUser = filter_var($data->idUser, FILTER_VALIDATE_INT);
                $goals = filter_var($data->goals, FILTER_VALIDATE_INT);
                $assists = filter_var($data->assists, FILTER_VALIDATE_INT);


                $deleteScorerSql = "DELETE FROM GAME_GOALS_ASSISTS WHERE id = ?;";
                $subtractGASql = "UPDATE PLAYER SET goals = goals - ?, assists = assists - ? WHERE idUser = ?;";
                
                try {
      
                    //Delete o jogador na tabela GAME_GOALS_ASSISTS
                    $stmt = $this->pdo->prepare($deleteScorerSql);
                    $stmt->bindParam(1, $id, PDO::PARAM_INT);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0){
                        //Subtrair o numero de golos e assistencias do jogador
                        $stmt = $this->pdo->prepare($subtractGASql);
                        $stmt->bindParam(1, $goals, PDO::PARAM_INT);
                        $stmt->bindParam(2, $assists, PDO::PARAM_INT);
                        $stmt->bindParam(3, $idUser, PDO::PARAM_INT);
                        $stmt->execute();
                    }else{
                        return ["code" => 400, "message" => "Erro ao remover o jogador!"];
                    }

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
}
?>