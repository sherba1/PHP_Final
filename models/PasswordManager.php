<?php

class PasswordManager
{
    private $db;
    private $masterKey;
    private $userId;

    public function __construct($dbConnection, $masterKey, $userId)
    {
        $this->db = $dbConnection;
        $this->masterKey = $masterKey;
        $this->userId = $userId;
    }

    public function generatePassword($options)
    {
        $length = $options['length'];
        $countLower = $options['countLower'];
        $countUpper = $options['countUpper'];
        $countDigits = $options['countDigits'];
        $countSpecial = $options['countSpecial'];

        if ($countLower + $countUpper + $countDigits + $countSpecial !== $length) {
            throw new Exception("Sum of parts does not equal total length");
        }

        $lowerChars = 'abcdefghijklmnopqrstuvwxyz';
        $upperChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digitChars = '0123456789';
        $specialChars = '!@#$%^&*()-_=+[]{}<>?';

        $passwordChars = [];

        for ($i = 0; $i < $countLower; $i++) {
            $passwordChars[] = $lowerChars[random_int(0, strlen($lowerChars) - 1)];
        }
        for ($i = 0; $i < $countUpper; $i++) {
            $passwordChars[] = $upperChars[random_int(0, strlen($upperChars) - 1)];
        }
        for ($i = 0; $i < $countDigits; $i++) {
            $passwordChars[] = $digitChars[random_int(0, strlen($digitChars) - 1)];
        }
        for ($i = 0; $i < $countSpecial; $i++) {
            $passwordChars[] = $specialChars[random_int(0, strlen($specialChars) - 1)];
        }

        shuffle($passwordChars);

        return implode('', $passwordChars);
    }

    public function encryptPassword($plainPassword)
    {
        $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $cipherText = openssl_encrypt(
            $plainPassword,
            'aes-256-cbc',
            $this->masterKey,
            OPENSSL_RAW_DATA,
            $iv
        );
        if ($cipherText === false) {
            throw new Exception("Encryption failed");
        }
        return ['cipher_text' => $cipherText, 'iv' => $iv];
    }

    public function decryptPassword($cipherText, $iv)
    {
        $plain = openssl_decrypt(
            $cipherText,
            'aes-256-cbc',
            $this->masterKey,
            OPENSSL_RAW_DATA,
            $iv
        );
        if ($plain === false) {
            throw new Exception("Decryption failed");
        }
        return $plain;
    }

    public function saveEntry($siteName, $cipherText, $iv)
    {
        $stmt = $this->db->prepare("
            INSERT INTO password_entries
            (user_id, site_name, encrypted_password, password_iv)
            VALUES
            (:uid, :site, :enc_pass, :iv)
        ");
        return $stmt->execute([
            ':uid' => $this->userId,
            ':site' => $siteName,
            ':enc_pass' => $cipherText,
            ':iv' => $iv
        ]);
    }

    public function getEntries()
    {
        $stmt = $this->db->prepare("
            SELECT id, site_name, encrypted_password, password_iv, created_at
            FROM password_entries
            WHERE user_id = :uid
            ORDER BY created_at DESC
        ");
        $stmt->execute([':uid' => $this->userId]);
        $rows = $stmt->fetchAll();

        $results = [];
        foreach ($rows as $row) {
            $plainPass = $this->decryptPassword(
                $row['encrypted_password'],
                $row['password_iv']
            );
            $results[] = [
                'id' => $row['id'],
                'siteName' => $row['site_name'],
                'password' => $plainPass,
                'created' => $row['created_at']
            ];
        }
        return $results;
    }

    public function deleteEntry($entryId)
    {
        $stmt = $this->db->prepare("
            DELETE FROM password_entries
            WHERE id = :id AND user_id = :uid
        ");
        return $stmt->execute([
            ':id' => $entryId,
            ':uid' => $this->userId
        ]);
    }

    public function updateEntry($entryId, $newPlainPassword)
    {
        $encData = $this->encryptPassword($newPlainPassword);
        $stmt = $this->db->prepare("
            UPDATE password_entries
            SET encrypted_password = :enc_pass, password_iv = :iv
            WHERE id = :id AND user_id = :uid
        ");
        return $stmt->execute([
            ':enc_pass' => $encData['cipher_text'],
            ':iv' => $encData['iv'],
            ':id' => $entryId,
            ':uid' => $this->userId
        ]);
    }
}
