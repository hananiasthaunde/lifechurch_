<?php
// --- INÍCIO DO BLOCO DE DEPURAÇÃO ---
// O objetivo é descobrir exatamente em que ponto o script falha.

// 1. Ativar buffer de saída para evitar erros de "headers already sent"
ob_start();

// 2. Ativar a exibição de TODOS os erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
echo "<strong>Debug Login - Passo 1:</strong> Exibição de erros ativada.<br>";

// 3. Iniciar a sessão
session_start();
echo "<strong>Debug Login - Passo 2:</strong> Sessão iniciada.<br>";

// 4. Incluir ficheiros
echo "<strong>Debug Login - Passo 3:</strong> Tentando incluir config.php...<br>";
require_once __DIR__ . '/../includes/config.php';
echo "<strong>Debug Login - Passo 3.1:</strong> config.php incluído.<br>";

echo "<strong>Debug Login - Passo 4:</strong> Tentando incluir functions.php...<br>";
require_once __DIR__ . '/../includes/functions.php';
echo "<strong>Debug Login - Passo 4.1:</strong> functions.php incluído.<br>";

$error = '';
$success_message = '';

// Verifica se há uma mensagem de sucesso vinda do registo
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
    echo "<strong>Debug Login - Info:</strong> Mensagem de sucesso do registo encontrada.<br>";
}

// Verifica se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "<strong>Debug Login - Passo 5:</strong> Formulário submetido (POST).<br>";
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        echo "<strong>Debug Login - Passo 6:</strong> Email e Senha recebidos: " . htmlspecialchars($email) . "<br>";

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Formato de email inválido.';
            echo "<strong style='color:red;'>Debug Login - ERRO:</strong> Formato de email inválido.<br>";
        } else {
            echo "<strong>Debug Login - Passo 7:</strong> Conectando ao DB...<br>";
            $conn = connect_db();
            
            if ($conn) {
                echo "<strong>Debug Login - Passo 8:</strong> Conexão com DB OK. Preparando a query...<br>";
                $stmt = $conn->prepare("SELECT id, name, email, password, role, church_id, is_approved FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    echo "<strong>Debug Login - Passo 9:</strong> Utilizador encontrado na base de dados.<br>";
                    $user = $result->fetch_assoc();
                    
                    if (password_verify($password, $user['password'])) {
                        echo "<strong>Debug Login - Passo 10:</strong> Senha correta.<br>";
                        
                        if ($user['is_approved'] == 0) {
                            $error = 'A sua conta está pendente de aprovação por um administrador.';
                            echo "<strong>Debug Login - AVISO:</strong> Conta não aprovada.<br>";
                        } else {
                            echo "<strong>Debug Login - Passo 11:</strong> Conta aprovada. Definindo sessão...<br>";
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_name'] = $user['name'];
                            $_SESSION['user_role'] = $user['role'];
                            $_SESSION['church_id'] = $user['church_id'];
                            
                            $role = $user['role'];
                            echo "<strong>Debug Login - Passo 12:</strong> Sessão definida. Role: '" . htmlspecialchars($role) . "'. Redirecionando...<br>";
                            echo "--- Se vir esta mensagem e nada acontecer, o erro está no ficheiro de destino. ---<br>";

                            // Limpa qualquer saída antes do redirecionamento
                            ob_end_clean(); 
                            
                            // Redirecionamento
                            switch ($role) {
                                case 'lider':
                                    header('Location: ../admin/celulas.php');
                                    break;
                                case 'master_admin':
                                case 'pastor_principal':
                                case 'pastor':
                                case 'membro':
                                default:
                                    header('Location: ../admin/dashboard.php');
                                    break;
                            }
                            exit; // ESSENCIAL depois de um header('Location')
                        }
                    } else {
                        $error = 'Email ou senha incorretos.';
                        echo "<strong style='color:red;'>Debug Login - ERRO:</strong> Senha incorreta.<br>";
                    }
                } else {
                    $error = 'Email ou senha incorretos.';
                    echo "<strong style='color:red;'>Debug Login - ERRO:</strong> Utilizador não encontrado.<br>";
                }
                $stmt->close();
                $conn->close();
            } else {
                $error = "Erro ao conectar à base de dados.";
                echo "<strong style='color:red;'>Debug Login - ERRO FATAL:</strong> Falha na conexão com o DB.<br>";
            }
        }
    } else {
        $error = 'Por favor, preencha todos os campos.';
        echo "<strong>Debug Login - AVISO:</strong> Campos em falta.<br>";
    }
}

// Se o script continuar, significa que houve um erro e não houve redirecionamento.
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Life Church - Login</title>
    <script src="https://cdn.tailwindcss.com/3.4.1"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: { primary: "#1A73E8", secondary: "#4285F4" },
            borderRadius: { button: "8px" },
          },
        },
      };
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.min.css" rel="stylesheet"/>
    <style>
      body { font-family: 'Roboto', sans-serif; background: linear-gradient(135deg, #F8FAFF 0%, #EEF2FF 100%); min-height: 100vh; }
      .floating-label { position: absolute; pointer-events: none; left: 40px; top: 18px; transition: 0.2s ease all; }
      .input-field:focus ~ .floating-label, .input-field:not(:placeholder-shown) ~ .floating-label { top: 8px; font-size: 0.75rem; color: #1A73E8; }
      .input-field:focus { border-color: #1A73E8; }
      .login-card { box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); animation: fadeIn 0.5s ease-out; }
      @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
      input:-webkit-autofill, input:-webkit-autofill:hover, input:-webkit-autofill:focus, input:-webkit-autofill:active {
          -webkit-box-shadow: 0 0 0 30px white inset !important;
          box-shadow: 0 0 0 30px white inset !important;
      }
    </style>
  </head>
  <body class="flex items-center justify-center p-4">
    <div class="login-card bg-white rounded-2xl w-full max-w-md p-8">
      <div class="text-center mb-8">
        <h1 class="font-['Pacifico'] text-3xl text-primary mb-2">Life Church</h1>
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Bem-vindo(a)</h2>
        <p class="text-gray-500">Faça login para continuar</p>
      </div>
        
      <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>
      
      <?php if ($success_message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($success_message); ?></span>
        </div>
      <?php endif; ?>

      <form class="space-y-6" method="POST" action="login.php">
        <div class="relative"><i class="ri-mail-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i><input type="email" id="email" name="email" class="input-field w-full h-14 pl-10 pr-3 pt-6 pb-2 bg-white border border-gray-300 rounded focus:outline-none" placeholder=" " required /><label for="email" class="floating-label text-gray-500 text-sm">Email</label></div>
        <div class="relative"><i class="ri-lock-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i><input type="password" id="password" name="password" class="input-field w-full h-14 pl-10 pr-3 pt-6 pb-2 bg-white border border-gray-300 rounded focus:outline-none" placeholder=" " required /><label for="password" class="floating-label text-gray-500 text-sm">Senha</label></div>
        <button type="submit" class="w-full h-12 bg-primary text-white font-medium rounded-button whitespace-nowrap flex items-center justify-center hover:bg-blue-700">Entrar</button>
      </form>

      <div class="mt-8 text-center"><p class="text-gray-600 text-sm">Não tem uma conta? <a href="register.php" class="text-primary font-medium hover:underline">Registe-se</a></p></div>
    </div>
  </body>
</html>
