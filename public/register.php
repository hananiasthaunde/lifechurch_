<?php
// --- INÍCIO DO FICHEIRO ÚNICO E INDEPENDENTE ---
// O objetivo é eliminar todos os erros de 'require_once' e de sintaxe em outros ficheiros.

// 1. Ativar a exibição de erros é a nossa prioridade.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Iniciar a sessão.
session_start();

// --- Conteúdo do config.php foi movido para aqui ---
// !! IMPORTANTE !! Verifique se estes dados estão 100% corretos com os do seu cPanel.
define('DB_HOST', 'localhost');
define('DB_NAME', 'lifechurchfinanc_lifechurch_db1');
define('DB_USER', 'lifechurchfinanc_lf_db');
define('DB_PASS', 'm6aqpIg9R0Zkpx4%');

// --- Conteúdo do functions.php foi movido para aqui ---
function connect_db() {
    // Usar @ para suprimir o aviso padrão e lidar com o erro manualmente.
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        // Em vez de morrer, vamos retornar false para que o código principal possa mostrar um erro amigável.
        error_log("Falha na conexão: " . $conn->connect_error);
        return false;
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

function is_valid_email($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $domain = substr(strrchr($email, "@"), 1);
    if (function_exists('checkdnsrr') && !checkdnsrr($domain, 'MX')) {
        return false;
    }
    return true;
}

// --- Lógica Principal da Página ---
$error = '';
$churches = [];
$conn = connect_db();

if ($conn) {
    $result_churches = $conn->query("SELECT id, name FROM churches ORDER BY name ASC");
    if ($result_churches) {
        while($row = $result_churches->fetch_assoc()) {
            $churches[] = $row;
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $city = $_POST['city'] ?? '';
        $role = $_POST['role'] ?? 'membro';
        $church_id = $_POST['church_id'] ?? null;
        
        if ($name && $email && $password && $phone && $city && $role && $church_id) {
            if (!is_valid_email($email)) {
                $error = 'Por favor, insira um endereço de email válido.';
            } else {
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $error = 'Este email já está registado.';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt_insert = $conn->prepare("INSERT INTO users (name, email, password, phone, city, role, church_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt_insert->bind_param("ssssssi", $name, $email, $hashed_password, $phone, $city, $role, $church_id);
                    
                    if ($stmt_insert->execute()) {
                        $_SESSION['success_message'] = 'Registo realizado com sucesso! A sua conta está pendente de aprovação.';
                        header('Location: login.php');
                        exit;
                    } else {
                        $error = 'Erro ao registar. Tente novamente.';
                    }
                }
            }
        } else {
            $error = 'Por favor, preencha todos os campos obrigatórios.';
        }
    }
} else {
    $error = "Erro Crítico: Não foi possível estabelecer conexão com a base de dados. Verifique as credenciais.";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Life Church - Registo</title>
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
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.min.css" />
    <style>
      body { font-family: 'Roboto', sans-serif; background: linear-gradient(135deg, #F8FAFF 0%, #EEF2FF 100%); min-height: 100vh; }
      .floating-label { position: absolute; pointer-events: none; left: 40px; top: 18px; transition: 0.2s ease all; }
      .input-field:focus ~ .floating-label, .input-field:not(:placeholder-shown) ~ .floating-label { top: 8px; font-size: 0.75rem; color: #1A73E8; }
      select.input-field ~ .floating-label, select.input-field:not([value=""]) ~ .floating-label { top: 8px; font-size: 0.75rem; color: #1A73E8; }
      .input-field:focus { border-color: #1A73E8; }
      .input-field { transition: border-color 0.2s ease; }
      .register-card { box-shadow: 0 4px_20px rgba(0, 0, 0, 0.08); animation: fadeIn 0.5s ease-out; }
      @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
      .submit-btn { transition: background-color 0.2s ease, transform 0.1s ease; }
      .submit-btn:hover { background-color: #0D62D1; }
      .submit-btn:active { transform: scale(0.98); }
      input:-webkit-autofill, input:-webkit-autofill:hover, input:-webkit-autofill:focus, input:-webkit-autofill:active { -webkit-box-shadow: 0 0 0 30px white inset !important; box-shadow: 0 0 0 30px white inset !important; }
    </style>
  </head>
  <body class="flex items-center justify-center p-4">
    <div class="register-card bg-white rounded-2xl w-full max-w-md p-8">
      <div class="text-center mb-8">
        <h1 class="font-['Pacifico'] text-3xl text-primary mb-2">Life Church</h1>
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Crie a sua Conta</h2>
        <p class="text-gray-500">Preencha os dados para se registar</p>
      </div>
        
      <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <form class="space-y-6" method="POST" action="register.php">
        <div class="relative">
          <i class="ri-user-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input type="text" id="name" name="name" class="input-field w-full h-14 pl-10 pr-3 pt-6 pb-2 bg-white border border-gray-300 rounded focus:outline-none" placeholder=" " required />
          <label for="name" class="floating-label text-gray-500 text-sm">Nome Completo</label>
        </div>
        <div class="relative">
          <i class="ri-mail-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input type="email" id="email" name="email" class="input-field w-full h-14 pl-10 pr-3 pt-6 pb-2 bg-white border border-gray-300 rounded focus:outline-none" placeholder=" " required />
          <label for="email" class="floating-label text-gray-500 text-sm">Email</label>
        </div>
        <div class="relative">
          <i class="ri-lock-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input type="password" id="password" name="password" class="input-field w-full h-14 pl-10 pr-3 pt-6 pb-2 bg-white border border-gray-300 rounded focus:outline-none" placeholder=" " required />
          <label for="password" class="floating-label text-gray-500 text-sm">Senha</label>
        </div>
        <div class="relative">
          <i class="ri-phone-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input type="text" id="phone" name="phone" class="input-field w-full h-14 pl-10 pr-3 pt-6 pb-2 bg-white border border-gray-300 rounded focus:outline-none" placeholder=" " required />
          <label for="phone" class="floating-label text-gray-500 text-sm">Telefone</label>
        </div>
        <div class="relative">
          <i class="ri-community-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input type="text" id="city" name="city" class="input-field w-full h-14 pl-10 pr-3 pt-6 pb-2 bg-white border border-gray-300 rounded focus:outline-none" placeholder=" " required />
          <label for="city" class="floating-label text-gray-500 text-sm">Cidade</label>
        </div>
        <div class="relative">
            <i class="ri-user-star-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <select id="role" name="role" class="input-field w-full h-14 pl-10 pr-3 pt-6 pb-2 border border-gray-300 rounded focus:outline-none bg-white" required>
                <option value="membro">Membro</option>
                <option value="lider">Líder</option>
                <option value="pastor">Pastor</option>
            </select>
            <label for="role" class="floating-label text-gray-500 text-sm">Função na Igreja</label>
        </div>
        <div class="relative">
            <i class="ri-church-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <select id="church_id" name="church_id" class="input-field w-full h-14 pl-10 pr-3 pt-6 pb-2 border border-gray-300 rounded focus:outline-none bg-white" required>
                <option value="" disabled selected>Selecione a sua igreja</option>
                <?php foreach($churches as $church): ?>
                    <option value="<?php echo $church['id']; ?>"><?php echo htmlspecialchars($church['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <label for="church_id" class="floating-label text-gray-500 text-sm">Igreja</label>
        </div>
        <button type="submit" class="submit-btn w-full h-12 bg-primary text-white font-medium rounded-button whitespace-nowrap flex items-center justify-center">
          Registar
        </button>
      </form>
      <div class="mt-8 text-center">
        <p class="text-gray-600 text-sm">
          Já tem uma conta?
          <a href="login.php" class="text-primary font-medium hover:underline">Faça login</a>
        </p>
      </div>
    </div>
  </body>
</html>
