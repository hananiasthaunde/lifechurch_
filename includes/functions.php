<?php
// Arquivo de funções úteis para o sistema Life Church

function connect_db() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Falha na conexão com o banco de dados: " . $conn->connect_error);
    }
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
