<?php

define('DEBUG', true);

if(DEBUG){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

function debug($info, $type='log')
{
    if (defined('DEBUG') && DEBUG){
        echo "<script>console.$type(".json_encode($info).");</script>";
    }
}

define('SERVER_FILTE_ROOT', '//ARCA.STORAGE.UA.PT/HOSTING/esan-tesp-ds-paw.web.ua.pt/tesp-ds-g28/');
define('UPLOAD_FOLDER', 'uploads/');


define('UPLOAD_PATH', SERVER_FILTE_ROOT . UPLOAD_FOLDER);
/*
function debug($info = '', $type = 'log')
{
    if (defined('DEBUG') && DEBUG) {
        if (is_array($info) || is_object($info)) {
            // Se for um array ou objeto, codificar com json_encode
            echo "<script>console.$type(" . json_encode($info) . ");</script>";
        } else {
            // Para outros tipos, como string ou número, usar diretamente
            echo "<script>console.$type('" . addslashes($info) . "');</script>";
        }
    }
}
*/

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
// Permitir que qualquer origem (cuidado com isso em produção)
header("Access-Control-Allow-Origin: *");

// Permitir métodos como POST, GET, OPTIONS, etc.
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// Permitir cabeçalhos específicos nas requisições
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Definir as variáveis de conexão

$host = 'mysql-sa.mgmt.ua.pt'; // Host da base de dados
$port = '3306';
$dbname = 'esan-dsg28'; // Nome da base de dados

#$username = 'esan-dsg28-dbo'; // Nome de utilizador da BD Owner
#$password = '7fTAwIbFqeUDF)oV'; // Senha da BD Owner
$username = 'esan-dsg28-web'; // Nome de utilizador da BD
$password = '[1E2z2Oeym03QTkW'; // Senha da BD

//localhost
/*
$host = 'localhost'; // Host da base de dados
$port = '3306';
$dbname = 'squadhub'; // Nome da base de dados
$username = 'root'; // Nome de utilizador da BD Owner
$password = ''; // Senha da BD Owner
*/

// Tentar estabelecer ligação com a base de dados via PDO
try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Definir o modo de erro do PDO para exceções
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Se houver um erro, exibir a mensagem
    die("Erro de ligação: " . $e->getMessage());
}

// Fechar a ligação à base de dados (função para usar opcionalmente)
function fecharLigacao($conn) {
    $conn = null; // Atribuir null para fechar a ligação
}
?>