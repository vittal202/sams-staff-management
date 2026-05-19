<?php
// encryption.php - AES-256-CBC Encryption Library

// IMPORTANT: In a production environment, this key should be stored in an environment variable 
// or a secure configuration file, not hardcoded in the script.
define('ENCRYPTION_KEY', 'your-32-character-secret-key-1234'); // 32 bytes for AES-256

/**
 * Encrypts a string using AES-256-CBC.
 * 
 * @param string $data The plaintext data to encrypt.
 * @return string The base64-encoded IV + ciphertext.
 */
function encrypt($data) {
    if (empty($data)) return $data;

    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
    
    // Return IV + encrypted data, base64 encoded for safe storage in session/cookies
    return base64_encode($iv . $encrypted);
}

/**
 * Decrypts a string using AES-256-CBC.
 * 
 * @param string $data The base64-encoded IV + ciphertext.
 * @return string|false The decrypted plaintext or false on failure.
 */
function decrypt($data) {
    if (empty($data)) return $data;

    $data = base64_decode($data);
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    
    $iv = substr($data, 0, $iv_length);
    $encrypted = substr($data, $iv_length);
    
    return openssl_decrypt($encrypted, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
}
?>
