<?php
/**
 * Ficheiro de Configuração Principal - Life Church
 *
 * Este ficheiro contém as configurações da base de dados e as funções essenciais do sistema.
 *
 * @version 1.0
 */

// ** 1. CONFIGURAÇÕES DA BASE DE DADOS MYSQL ** //
// Substitua os valores abaixo pelos que obteve ao criar a base de dados no cPanel (Capítulo 11 do livro).

/** O nome do servidor da base de dados (geralmente 'localhost' em hospedagens cPanel). */
define('DB_HOST', 'localhost');

/** O nome da sua base de dados. O cPanel adiciona um prefixo (ex: lifechurchfinanc_meudb). */
define('DB_NAME', 'lifechurchfinanc_nome_da_sua_db');

/** O nome de utilizador da sua base de dados. O cPanel também adiciona um prefixo. */
define('DB_USER', 'lifechurchfinanc_lf_db');

/** A palavra-passe para o utilizador da base de dados. */
define('DB_PASS', 'm6aqpIg9R0Zkpx4%');


// ** 2. FUNÇÕES ESSENCIAIS DO SISTEMA ** //

/**
 * Cria e retorna uma conexão com a base de dados.
 * A função usa as constantes definidas acima.
 * * @return mysqli|false O objeto da conexão em caso de sucesso, ou false em caso de falha.
 */
function connect_db() {
    // Tenta conectar à base de dados
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Verifica se ocorreu um erro na conexão
    if ($conn->connect_error) {
        // Interrompe a execução e exibe uma mensagem de erro clara.
        // Em produção, pode querer registar este erro num ficheiro em vez de o mostrar ao utilizador.
        error_log("Falha na conexão com o banco de dados: " . $conn->connect_error);
        die("Ocorreu um problema ao conectar com o servidor. Por favor, tente mais tarde.");
    }

    // Define o charset para utf8 para suportar caracteres especiais
    $conn->set_charset('utf8mb4');
    
    return $conn;
}

/**
 * Valida um endereço de e-mail de forma mais completa.
 * 1. Verifica o formato do e-mail.
 * 2. Verifica se o domínio do e-mail possui registos MX, indicando que pode receber e-mails.
 *
 * @param string $email O e-mail a ser validado.
 * @return bool True se o e-mail for válido, False caso contrário.
 */
function is_valid_email($email) {
    // Passo 1: Validar o formato do e-mail.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    // Passo 2: Extrair o domínio do e-mail.
    $domain = substr(strrchr($email, "@"), 1);

    // Passo 3: Verificar se o domínio tem registos MX (Mail Exchange).
    // Isto indica que o domínio está configurado para receber e-mails.
    // A função checkdnsrr pode não estar disponível em todos os sistemas (ex: Windows).
    // Verificamos se a função existe para evitar erros fatais.
    if (function_exists('checkdnsrr')) {
        if (!checkdnsrr($domain, 'MX')) {
            return false; // O domínio não tem registos MX, logo não pode receber e-mails.
        }
    }
    
    // Se passou em todas as verificações, o e-mail é considerado válido.
    return true;
}


// Adicione outras funções úteis aqui, como sanitização de dados, etc.

?>
