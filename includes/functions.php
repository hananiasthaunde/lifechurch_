<?php
/**
 * Ficheiro de Funções Essenciais - Life Church
 *
 * Este ficheiro contém as funções reutilizáveis do sistema.
 * Ele NÃO deve conter configurações de base de dados.
 */

/**
 * Cria e retorna uma conexão com a base de dados.
 * A função usa as constantes definidas no ficheiro config.php.
 * @return mysqli|false O objeto da conexão em caso de sucesso, ou false em caso de falha.
 */
function connect_db() {
    // Tenta conectar à base de dados usando as constantes do config.php
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Verifica se ocorreu um erro na conexão
    if ($conn->connect_error) {
        error_log("Falha na conexão com o banco de dados: " . $conn->connect_error);
        return false; // Retorna false para que o script que a chamou possa lidar com o erro.
    }

    // Define o charset para utf8 para suportar caracteres especiais
    $conn->set_charset('utf8mb4');
    
    return $conn;
}

/**
 * Valida um endereço de e-mail de forma mais completa.
 * @param string $email O e-mail a ser validado.
 * @return bool True se o e-mail for válido, False caso contrário.
 */
function is_valid_email($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $domain = substr(strrchr($email, "@"), 1);

    if (function_exists('checkdnsrr')) {
        if (!checkdnsrr($domain, 'MX')) {
            return false;
        }
    }
    
    return true;
}

// Adicione outras funções úteis aqui.
?>
