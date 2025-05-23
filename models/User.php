<?php

class User
{
    private $db;
    private $id = null;
    private $username = null;
    private $passwordHash = null;
    private $encryptedMasterKey = null;
    private $masterKeyIv = null;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    public function register($username, $plainPassword)
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->fetch()) {
            throw new Exception("Username already in use");
        }

        $passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);
        $masterKey = random_bytes(32);
        $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encryptedMasterKey = openssl_encrypt(
            $masterKey,
            'aes-256-cbc',
            $plainPassword,
            OPENSSL_RAW_DATA,
            $iv
        );

        $insert = $this->db->prepare("
            INSERT INTO users 
            (username, password_hash, encrypted_master_key, master_key_iv)
            VALUES
            (:username, :password_hash, :encrypted_master_key, :master_key_iv)
        ");
        return $insert->execute([
            ':username' => $username,
            ':password_hash' => $passwordHash,
            ':encrypted_master_key' => $encryptedMasterKey,
            ':master_key_iv' => $iv
        ]);
    }

    public function login($username, $plainPassword)
    {
        $stmt = $this->db->prepare("
            SELECT id, password_hash, encrypted_master_key, master_key_iv
            FROM users WHERE username = :username
        ");
        $stmt->execute([':username' => $username]);
        $row = $stmt->fetch();
        if (! $row) {
            return false;
        }

        if (! password_verify($plainPassword, $row['password_hash'])) {
            return false;
        }

        $cipheredKey = $row['encrypted_master_key'];
        $iv = $row['master_key_iv'];
        $masterKey = openssl_decrypt(
            $cipheredKey,
            'aes-256-cbc',
            $plainPassword,
            OPENSSL_RAW_DATA,
            $iv
        );
        if ($masterKey === false) {
            throw new Exception("Failed to decrypt master key");
        }

        $this->id = $row['id'];
        $this->username = $username;
        $this->passwordHash = $row['password_hash'];
        $this->encryptedMasterKey = $cipheredKey;
        $this->masterKeyIv = $iv;

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['user_id'] = $this->id;
        $_SESSION['username'] = $this->username;
        $_SESSION['master_key'] = $masterKey;

        return true;
    }

    public function changePassword($oldPassword, $newPassword)
    {
        if ($this->id === null) {
            throw new Exception("Not logged in");
        }

        $stmt = $this->db->prepare("
            SELECT password_hash, encrypted_master_key, master_key_iv
            FROM users WHERE id = :id
        ");
        $stmt->execute([':id' => $this->id]);
        $row = $stmt->fetch();
        if (! $row) {
            throw new Exception("User not found");
        }

        if (! password_verify($oldPassword, $row['password_hash'])) {
            throw new Exception("Old password is wrong");
        }

        $cipheredKey = $row['encrypted_master_key'];
        $oldIv = $row['master_key_iv'];
        $masterKey = openssl_decrypt(
            $cipheredKey,
            'aes-256-cbc',
            $oldPassword,
            OPENSSL_RAW_DATA,
            $oldIv
        );
        if ($masterKey === false) {
            throw new Exception("Could not decrypt master key");
        }

        $newIv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $newEncryptedMasterKey = openssl_encrypt(
            $masterKey,
            'aes-256-cbc',
            $newPassword,
            OPENSSL_RAW_DATA,
            $newIv
        );
        if ($newEncryptedMasterKey === false) {
            throw new Exception("Failed to re-encrypt master key");
        }

        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $update = $this->db->prepare("
            UPDATE users
            SET password_hash = :new_hash,
                encrypted_master_key = :new_cipher_key,
                master_key_iv = :new_iv
            WHERE id = :id
        ");
        $success = $update->execute([
            ':new_hash' => $newPasswordHash,
            ':new_cipher_key' => $newEncryptedMasterKey,
            ':new_iv' => $newIv,
            ':id' => $this->id
        ]);

        if ($success) {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            $_SESSION['master_key'] = $masterKey;
        }

        return $success;
    }

    public static function logout()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_unset();
        session_destroy();
    }
}
