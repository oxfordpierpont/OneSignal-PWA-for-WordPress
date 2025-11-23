<?php
/**
 * Encryption Class for API Keys
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OneSignal PWA Encryption Class
 */
class OneSignal_PWA_Encryption {

    /**
     * Encryption method
     */
    const METHOD = 'AES-256-CBC';

    /**
     * Get encryption key
     *
     * @return string
     */
    private function get_key() {
        // Use WordPress salts for encryption key
        $key = wp_salt('secure_auth') . wp_salt('logged_in') . wp_salt('nonce');
        return substr(hash('sha256', $key), 0, 32);
    }

    /**
     * Encrypt data
     *
     * @param string $data
     * @return string
     */
    public function encrypt($data) {
        if (empty($data)) {
            return '';
        }

        $key = $this->get_key();
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::METHOD));
        $encrypted = openssl_encrypt($data, self::METHOD, $key, 0, $iv);

        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * Decrypt data
     *
     * @param string $data
     * @return string
     */
    public function decrypt($data) {
        if (empty($data)) {
            return '';
        }

        $key = $this->get_key();
        $parts = explode('::', base64_decode($data), 2);

        if (count($parts) !== 2) {
            return '';
        }

        list($encrypted_data, $iv) = $parts;
        return openssl_decrypt($encrypted_data, self::METHOD, $key, 0, $iv);
    }
}
