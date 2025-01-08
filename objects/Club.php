<?php
//session_start();
class Club{
    
    private $pdo;
    private $table_name = "CLUB";

    public $id;
    public $idOwner;
    public $name;
    public $abbreviation;
    public $victories;
    public $defeats;
    public $draws;

    public function __construct($db){
        $this->pdo = $db;
    }

    public function create($data){
        try {
            if (isset($data->name, $data->abbreviation)) {
    
                $insertSQL = "INSERT INTO $this->table_name (idOwner, name, abbreviation, victories, defeats, draws) VALUES (?, ?, ?, ?, ?, ?)";

                $clubName = filter_var($data->name, FILTER_SANITIZE_STRING);
                $clubAbbreviation = filter_var($data->abbreviation, FILTER_SANITIZE_STRING);
    
                try {
                    // Iniciar transação
                    $this->pdo->beginTransaction();
    
                    // Preparar a query
                    $stmt = $this->pdo->prepare($insertSQL);
    
                    // Atribuir valores aos parâmetros
                    $stmt->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
                    $stmt->bindParam(2, $clubName, PDO::PARAM_STR);
                    $stmt->bindParam(3, $clubAbbreviation, PDO::PARAM_STR);
                    $stmt->bindValue(4, 0, PDO::PARAM_INT);
                    $stmt->bindValue(5, 0, PDO::PARAM_INT);
                    $stmt->bindValue(6, 0, PDO::PARAM_INT);
    
                    // Executa o INSERT
                    if ($stmt->execute()) {
    
                        // Obter o ID gerado
                        $generatedId = $this->pdo->lastInsertId();
    
                        if ($generatedId > 0) {
                            // Atualiza a tabela USER associando o clube ao utilizador
                            $updateSQL = "UPDATE USER SET idClub = ? WHERE id = ?";
                            $updateStmt = $this->pdo->prepare($updateSQL);
    
                            $updateStmt->bindParam(1, $generatedId, PDO::PARAM_INT);
                            $updateStmt->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
    
                            $rowsAffected = $updateStmt->execute();
    
                            if ($rowsAffected < 1) {
                                // Falha ao associar o clube ao utilizador
                                $this->pdo->rollBack();           
                                return ["code" => 500, "message" => "Falha ao associar o clube ao utilizador."];
                            }
    
                            $_SESSION['idClub'] = $generatedId;
    
                        } else {
                            // Falha ao criar o clube
                            $this->pdo->rollBack();
                            return ["code" => 500, "message" => "Falha ao criar o clube."];
                        }
                    } else {
                        // Falha ao executar o INSERT
                        $this->pdo->rollBack();
                        return ["code" => 500, "message" => "Erro ao executar o INSERT."];
                    }
    
                    // Confirma a transação
                    $this->pdo->commit();            
                    return ["code" => 201, "message" => "Clube criado com sucesso!"];
    
                } catch (PDOException $e) {
                    // Em caso de erro, desfazer as alterações e responder com erro
                    $this->pdo->rollBack();
                    //http_response_code(500);
                    //echo json_encode(['message' => $e->getMessage()]);
                    return ["code" => 500, "mensagem" => $e->getMessage()];
                }
    
            } else {
                // Dados inválidos
                //http_response_code(400);
                //echo json_encode(['message' => 'Dados inválidos fornecidos.']);
                return ["code" => 400, "message" => 'Dados inválidos fornecidos.'];
            }
    
        } catch (Exception $e) {
            // Erro geral na requisição
            return ["code" => 500, "message" => $e->getMessage()];
        }



    }
}
?>