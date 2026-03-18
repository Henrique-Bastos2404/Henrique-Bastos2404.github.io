<?php
/**
 * Classe para gerenciar operações de utilizadores na base de dados (adaptada ao esquema `utilizadores`)
 */
class Player {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Cria um novo utilizador (usa a tabela `utilizadores`).
     * Como o frontend não pede email, geramos um email fictício baseado no username.
     */
    public function register($username, $password, $name = null) {
        $username = trim($username);
        $name = $name !== null ? trim($name) : $username;
        
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Todos os campos são obrigatórios'];
        }
        
        if (strlen($username) < 3) {
            return ['success' => false, 'message' => 'Nome de utilizador deve ter pelo menos 3 caracteres'];
        }
        
        if (strlen($password) < 4) {
            return ['success' => false, 'message' => 'A senha deve ter pelo menos 4 caracteres'];
        }
        
        // Verifica se o utilizador já existe (por username)
        $stmt = $this->conn->prepare("SELECT id FROM utilizadores WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Este utilizador já existe'];
        }
        $stmt->close();
        
        // Gerar um email simples (não ideal, mas compatível com o schema atual)
        $email = $username . '@local.invalid';
        $tipo_id = 2; // tipo padrão: Utilizador
        $foto_perfil = '';

        // Hash da password (usamos password_hash moderno)
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // Insere o novo utilizador
        $stmt = $this->conn->prepare("INSERT INTO utilizadores (ativo, tipo_id, email, `password`, username, foto_perfil) VALUES (TRUE, ?, ?, ?, ?, ?)");
        $stmt->bind_param('issss', $tipo_id, $email, $hashedPassword, $username, $foto_perfil);
        
        if ($stmt->execute()) {
            $newId = $this->conn->insert_id;
            $stmt->close();
            return ['success' => true, 'message' => 'Conta criada com sucesso!', 'user_id' => (int)$newId, 'username' => $username];
        } else {
            $err = $stmt->error;
            $stmt->close();
            return ['success' => false, 'message' => 'Erro ao registar utilizador: ' . $err];
        }
    }
    
    /**
     * Autentica um utilizador com username e password
     * Suporta passwords armazenadas com password_hash e SHA2 (legacy)
     */
    public function login($username, $password) {
        $username = trim($username);
        
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Utilizador e senha obrigatórios'];
        }
        
        $stmt = $this->conn->prepare("SELECT id, username, `password` FROM utilizadores WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $stmt->close();
            $stored = $user['password'];

            // Verifica com password_hash
            if (password_verify($password, $stored)) {
                return ['success' => true, 'user_id' => $user['id'], 'username' => $user['username']];
            }

            // Compatibilidade com SHA256 legado (ex.: SHA2(...,256) inserido no SQL de exemplo)
            if (hash('sha256', $password) === $stored) {
                // Re-hash para algoritmo moderno e atualiza DB (opcional)
                $newHash = password_hash($password, PASSWORD_BCRYPT);
                $up = $this->conn->prepare("UPDATE utilizadores SET `password` = ? WHERE id = ?");
                $up->bind_param('si', $newHash, $user['id']);
                $up->execute();
                $up->close();

                return ['success' => true, 'user_id' => $user['id'], 'username' => $user['username']];
            }

            return ['success' => false, 'message' => 'Senha incorreta'];
        } else {
            if ($stmt) $stmt->close();
            return ['success' => false, 'message' => 'Utilizador não encontrado'];
        }
    }
    
    /**
     * Obtém informações do utilizador por ID
     */
    public function getById($user_id) {
        $stmt = $this->conn->prepare("SELECT id, username, foto_perfil, ativo FROM utilizadores WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if (!$user) return null;

        // Normaliza os campos esperados pelo frontend
        return [
            'id' => $user['id'],
            'username' => $user['username'],
            'profile_image' => $user['foto_perfil'] ?: null,
            'is_public' => isset($user['ativo']) ? (bool)$user['ativo'] : true
        ];
    }
    
    /**
     * Obtém informações do utilizador por username
     */
    public function getByUsername($username) {
        $stmt = $this->conn->prepare("SELECT id, username, foto_perfil, ativo FROM utilizadores WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) return null;

        return [
            'id' => $user['id'],
            'username' => $user['username'],
            'profile_image' => $user['foto_perfil'] ?: null,
            'is_public' => isset($user['ativo']) ? (bool)$user['ativo'] : true
        ];
    }
    
    /**
     * Atualiza o username do utilizador
     */
    public function updateUsername($user_id, $new_username) {
        $new_username = trim($new_username);
        
        if (empty($new_username)) {
            return ['success' => false, 'message' => 'Nome de utilizador não pode estar vazio'];
        }
        
        if (strlen($new_username) < 3) {
            return ['success' => false, 'message' => 'Nome de utilizador deve ter pelo menos 3 caracteres'];
        }
        
        // Verificar se o nome já existe
        $stmt = $this->conn->prepare("SELECT id FROM utilizadores WHERE username = ? AND id != ?");
        $stmt->bind_param('si', $new_username, $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Este nome de utilizador já está em uso'];
        }
        $stmt->close();
        
        // Atualizar username (nota: não existe last_username_change no esquema atual)
        $stmt = $this->conn->prepare("UPDATE utilizadores SET username = ? WHERE id = ?");
        $stmt->bind_param('si', $new_username, $user_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Nome de utilizador atualizado com sucesso', 'username' => $new_username];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Erro ao atualizar nome de utilizador'];
        }
    }
    
    /**
     * Atualiza a password do utilizador
     */
    public function updatePassword($user_id, $old_password, $new_password, $confirm_password) {
        if (empty($old_password) || empty($new_password)) {
            return ['success' => false, 'message' => 'Todos os campos são obrigatórios'];
        }
        
        if ($new_password !== $confirm_password) {
            return ['success' => false, 'message' => 'As senhas não coincidem'];
        }
        
        if (strlen($new_password) < 4) {
            return ['success' => false, 'message' => 'A senha deve ter pelo menos 4 caracteres'];
        }
        
        // Verificar password atual
        $stmt = $this->conn->prepare("SELECT `password` FROM utilizadores WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        $stored = $user['password'];
        if (!(password_verify($old_password, $stored) || hash('sha256', $old_password) === $stored)) {
            return ['success' => false, 'message' => 'Senha atual incorreta'];
        }
        
        // Hash da nova password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        
        $stmt = $this->conn->prepare("UPDATE utilizadores SET `password` = ? WHERE id = ?");
        $stmt->bind_param('si', $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Senha atualizada com sucesso'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Erro ao atualizar senha'];
        }
    }
    
    /**
     * Atualiza o avatar do utilizador
     */
    public function updateAvatar($user_id, $avatar) {
        if (empty($avatar)) {
            return ['success' => false, 'message' => 'Avatar não pode estar vazio'];
        }
        
        $avatar_path = 'img/avatars/' . basename($avatar);
        
        $stmt = $this->conn->prepare("UPDATE utilizadores SET foto_perfil = ? WHERE id = ?");
        $stmt->bind_param('si', $avatar_path, $user_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Avatar atualizado com sucesso'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Erro ao atualizar avatar'];
        }
    }
}
?>