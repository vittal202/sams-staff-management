<?php
class User
{
  public $error = "";
  private $pdo = null;
  private $stmt = null;

  function __construct($pdo = null)
  {
    if ($pdo) {
      $this->pdo = $pdo;
    } else {
      require_once __DIR__ . '/../config/db_simple.php';
      $this->pdo = $pdo;
    }
    require_once __DIR__ . '/../config/jwt_config.php';
  }

  function __destruct()
  {
    $this->stmt = null;
    $this->pdo = null;
  }

  function query($sql, $data = null)
  {
    $this->stmt = $this->pdo->prepare($sql);
    $this->stmt->execute($data);
  }

  function fetch($sql, $data = null)
  {
    $this->query($sql, $data);
    return $this->stmt->fetch();
  }

  function fetchAll($sql, $data = null)
  {
    $this->query($sql, $data);
    return $this->stmt->fetchAll();
  }

  // REGISTER
  function register($username, $email, $password)
  {
    try {
      if ($this->findByEmail($email)) {
        $this->error = "Email already registered";
        return false;
      }
      if ($this->findByUsername($username)) {
        $this->error = "Username already taken";
        return false;
      }
      return $this->create($username, $email, $password);
    } catch (Exception $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }

  // GET USER BY EMAIL
  function getByEmail($email)
  {
    return $this->findByEmail($email);
  }

  // GET USER BY USERNAME
  function getByUsername($username)
  {
    return $this->findByUsername($username);
  }

  // GET USER BY ID
  function getById($id)
  {
    return $this->findById($id);
  }

  // FIND USER BY USERNAME
  public function findByUsername($username)
  {
    $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch();
  }

  // FIND USER BY EMAIL
  public function findByEmail($email)
  {
    $stmt = $this->pdo->prepare("
      SELECT u.*, COALESCE(u.job_title, r.role_name) AS role_name
      FROM users u 
      LEFT JOIN roles r ON u.role_id = r.id 
      WHERE u.email = ?
    ");
    $stmt->execute([$email]);
    return $stmt->fetch();
  }

  // FIND USER BY ID
  public function findById($id)
  {
    $stmt = $this->pdo->prepare("
      SELECT u.*, COALESCE(u.job_title, r.role_name) AS role_name
      FROM users u 
      LEFT JOIN roles r ON u.role_id = r.id 
      WHERE u.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  // CREATE (Formerly Wrapper)
  public function create($username, $email, $password)
  {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$username, $email, $hashed, $username]);
  }

  // LOGIN (JWT remains)
  function login($email, $password)
  {
    // (A) GET USER
    $user = $this->findByEmail($email);
    if ($user === false) {
      $this->error = "Invalid email or password";
      return false;
    }

    // (B) VERIFY PASSWORD
    if (!password_verify($password, $user["password"])) {
      $this->error = "Invalid email or password";
      return false;
    }

    // (C) GENERATE JWT TOKEN
    require_once __DIR__ . '/../../vendor/autoload.php';
    $payload = [
      "iat" => time(),
      "exp" => time() + JWT_EXPIRY,
      "data" => [
        "id" => $user["id"],
        "username" => $user["username"],
        "email" => $user["email"]
      ]
    ];
    return \Firebase\JWT\JWT::encode($payload, JWT_SECRET, JWT_ALGO);
  }

  // VALIDATE JWT
  function validate($jwt)
  {
    require_once __DIR__ . '/../../vendor/autoload.php';
    try {
      $decoded = \Firebase\JWT\JWT::decode($jwt, new \Firebase\JWT\Key(JWT_SECRET, JWT_ALGO));
      return (array) $decoded->data;
    } catch (Exception $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }

  // PERSISTENT LOGIN: STORE TOKEN
  public function storeToken($userId, $token, $expiry)
  {
    // Delete old tokens for this user
    $stmt = $this->pdo->prepare("DELETE FROM tokens WHERE user_id = ?");
    $stmt->execute([$userId]);

    // Insert new token
    $stmt = $this->pdo->prepare("INSERT INTO tokens (user_id, token, expiry) VALUES (?, ?, ?)");
    return $stmt->execute([$userId, $token, $expiry]);
  }

  // PERSISTENT LOGIN: VALIDATE TOKEN
  public function validateToken($token)
  {
    $stmt = $this->pdo->prepare("SELECT user_id FROM tokens WHERE token = ? AND expiry > NOW()");
    $stmt->execute([$token]);
    return $stmt->fetchColumn();
  }

  // PERSISTENT LOGIN: DELETE TOKEN
  public function deleteToken($token)
  {
    $stmt = $this->pdo->prepare("DELETE FROM tokens WHERE token = ?");
    return $stmt->execute([$token]);
  }

  // --- GLOBAL LOGOUT / SESSION TRACKING ---

  // ADD SESSION RECORD
  public function addSession($userId, $sessionId)
  {
    $stmt = $this->pdo->prepare("INSERT INTO user_sessions (user_id, session_id) VALUES (?, ?)");
    return $stmt->execute([$userId, $sessionId]);
  }

  // CHECK IF SESSION ID IS VALID FOR USER
  public function isValidSession($userId, $sessionId)
  {
    $stmt = $this->pdo->prepare("SELECT id FROM user_sessions WHERE user_id = ? AND session_id = ?");
    $stmt->execute([$userId, $sessionId]);
    return $stmt->fetch() ? true : false;
  }

  // DELETE A SPECIFIC SESSION
  public function deleteSession($sessionId)
  {
    $stmt = $this->pdo->prepare("DELETE FROM user_sessions WHERE session_id = ?");
    return $stmt->execute([$sessionId]);
  }

  // DELETE ALL SESSIONS FOR A USER (Global Logout)
  public function deleteAllSessions($userId)
  {
    $stmt = $this->pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?");
    return $stmt->execute([$userId]);
  }

  // ALIAS FOR BACKWARD COMPATIBILITY
  public function createSession($userId, $sessionId)
  {
    return $this->addSession($userId, $sessionId);
  }
  public function deleteAllUserSessions($userId)
  {
    return $this->deleteAllSessions($userId);
  }
}
?>