<?php
/*================================================================
*  File Name：Security.php
*  Author：carlziess, chengmo9292@126.com
*  Create Date：2017-02-21 19:32:21
*  Description：
===============================================================*/
use Utility\Strings;
class Security
{
    use Singleton;
    public $cipher = 'AES-128-CBC';
    public $allowedCiphers = [
        'AES-128-CBC' => [16, 16],
        'AES-192-CBC' => [16, 24],
        'AES-256-CBC' => [16, 32],
    ];
    public $kdfHash = 'sha256';
    public $macHash = 'sha256';
    public $authKeyInfo = 'AuthorizationKey';
    public $derivationIterations = 100000;
    public $passwordHashStrategy;
    public $passwordHashCost = 13;

    public function encryptByPassword($data, $password)
    {
        return $this->encrypt($data, true, $password, null);
    }

    public function encryptByKey($data, $inputKey, $info = null)
    {
        return $this->encrypt($data, false, $inputKey, $info);
    }

    public function decryptByPassword($data, $password)
    {
        return $this->decrypt($data, true, $password, null);
    }

    public function decryptByKey($data, $inputKey, $info = null)
    {
        return $this->decrypt($data, false, $inputKey, $info);
    }

    protected function encrypt($data, $passwordBased, $secret, $info)
    {
        if (!extension_loaded('openssl')) {
            throw new Exception('Encryption requires the OpenSSL PHP extension');
        }
        if (!isset($this->allowedCiphers[$this->cipher][0], $this->allowedCiphers[$this->cipher][1])) {
            throw new Exception($this->cipher . ' is not an allowed cipher');
        }
        list($blockSize, $keySize) = $this->allowedCiphers[$this->cipher];
        $keySalt = $this->generateRandomKey($keySize);
        if ($passwordBased) {
            $key = $this->pbkdf2($this->kdfHash, $secret, $keySalt, $this->derivationIterations, $keySize);
        } else {
            $key = $this->hkdf($this->kdfHash, $secret, $keySalt, $info, $keySize);
        }
        $iv = $this->generateRandomKey($blockSize);
        $encrypted = openssl_encrypt($data, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);
        if ($encrypted === false) {
            throw new \yii\base\Exception('OpenSSL failure on encryption: ' . openssl_error_string());
        }
        $authKey = $this->hkdf($this->kdfHash, $key, null, $this->authKeyInfo, $keySize);
        $hashed = $this->hashData($iv . $encrypted, $authKey);
        /*
         * Output: [keySalt][MAC][IV][ciphertext]
         * - keySalt is KEY_SIZE bytes long
         * - MAC: message authentication code, length same as the output of MAC_HASH
         * - IV: initialization vector, length $blockSize
         */
        return $keySalt . $hashed;
    }

    protected function decrypt($data, $passwordBased, $secret, $info)
    {
        if (!extension_loaded('openssl')) {
            throw new Exception('Encryption requires the OpenSSL PHP extension');
        }
        if (!isset($this->allowedCiphers[$this->cipher][0], $this->allowedCiphers[$this->cipher][1])) {
            throw new Exception($this->cipher . ' is not an allowed cipher');
        }
        list($blockSize, $keySize) = $this->allowedCiphers[$this->cipher];
        $keySalt = Strings::byteSubstr($data, 0, $keySize);
        if ($passwordBased) {
            $key = $this->pbkdf2($this->kdfHash, $secret, $keySalt, $this->derivationIterations, $keySize);
        } else {
            $key = $this->hkdf($this->kdfHash, $secret, $keySalt, $info, $keySize);
        }
        $authKey = $this->hkdf($this->kdfHash, $key, null, $this->authKeyInfo, $keySize);
        $data = $this->validateData(Strings::byteSubstr($data, $keySize, null), $authKey);
        if ($data === false) {
            return false;
        }
        $iv = Strings::byteSubstr($data, 0, $blockSize);
        $encrypted = Strings::byteSubstr($data, $blockSize, null);

        $decrypted = openssl_decrypt($encrypted, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);
        if ($decrypted === false) {
            throw new Exception('OpenSSL failure on decryption: ' . openssl_error_string());
        }
        return $decrypted;
    }

    public function hkdf($algo, $inputKey, $salt = null, $info = null, $length = 0)
    {
        if (function_exists('hash_hkdf')) {
            $outputKey = hash_hkdf($algo, $inputKey, $length, $info, $salt);
            if ($outputKey === false) {
                throw new Exception('Invalid parameters to hash_hkdf()');
            }

            return $outputKey;
        }
        $test = @hash_hmac($algo, '', '', true);
        if (!$test) {
            throw new Exception('Failed to generate HMAC with hash algorithm: ' . $algo);
        }
        $hashLength = Strings::byteLength($test);
        if (is_string($length) && preg_match('{^\d{1,16}$}', $length)) {
            $length = (int) $length;
        }
        if (!is_int($length) || $length < 0 || $length > 255 * $hashLength) {
            throw new Exception('Invalid length');
        }
        $blocks = $length !== 0 ? ceil($length / $hashLength) : 1;
        if ($salt === null) {
            $salt = str_repeat("\0", $hashLength);
        }
        $prKey = hash_hmac($algo, $inputKey, $salt, true);
        $hmac = '';
        $outputKey = '';
        for ($i = 1; $i <= $blocks; $i++) {
            $hmac = hash_hmac($algo, $hmac . $info . chr($i), $prKey, true);
            $outputKey .= $hmac;
        }
        if ($length !== 0) {
            $outputKey = Strings::byteSubstr($outputKey, 0, $length);
        }
        return $outputKey;
    }

    public function pbkdf2($algo, $password, $salt, $iterations, $length = 0)
    {
        if (function_exists('hash_pbkdf2')) {
            $outputKey = hash_pbkdf2($algo, $password, $salt, $iterations, $length, true);
            if ($outputKey === false) {
                throw new Exception('Invalid parameters to hash_pbkdf2()');
            }
            return $outputKey;
        }
        // todo: is there a nice way to reduce the code repetition in hkdf() and pbkdf2()?
        $test = @hash_hmac($algo, '', '', true);
        if (!$test) {
            throw new Exception('Failed to generate HMAC with hash algorithm: ' . $algo);
        }
        if (is_string($iterations) && preg_match('{^\d{1,16}$}', $iterations)) {
            $iterations = (int) $iterations;
        }
        if (!is_int($iterations) || $iterations < 1) {
            throw new Exception('Invalid iterations');
        }
        if (is_string($length) && preg_match('{^\d{1,16}$}', $length)) {
            $length = (int) $length;
        }
        if (!is_int($length) || $length < 0) {
            throw new Exception('Invalid length');
        }
        $hashLength = Strings::byteLength($test);
        $blocks = $length !== 0 ? ceil($length / $hashLength) : 1;
        $outputKey = '';
        for ($j = 1; $j <= $blocks; $j++) {
            $hmac = hash_hmac($algo, $salt . pack('N', $j), $password, true);
            $xorsum = $hmac;
            for ($i = 1; $i < $iterations; $i++) {
                $hmac = hash_hmac($algo, $hmac, $password, true);
                $xorsum ^= $hmac;
            }
            $outputKey .= $xorsum;
        }
        if ($length !== 0) {
            $outputKey = Strings::byteSubstr($outputKey, 0, $length);
        }
        return $outputKey;
    }

    public function hashData($data, $key, $rawHash = false)
    {
        $hash = hash_hmac($this->macHash, $data, $key, $rawHash);
        if (!$hash) {
            throw new Exception('Failed to generate HMAC with hash algorithm: ' . $this->macHash);
        }
        return $hash . $data;
    }

    public function validateData($data, $key, $rawHash = false)
    {
        $test = @hash_hmac($this->macHash, '', '', $rawHash);
        if (!$test) {
            throw new Exception('Failed to generate HMAC with hash algorithm: ' . $this->macHash);
        }
        $hashLength = Strings::byteLength($test);
        if (Strings::byteLength($data) >= $hashLength) {
            $hash = Strings::byteSubstr($data, 0, $hashLength);
            $pureData = Strings::byteSubstr($data, $hashLength, null);
            $calculatedHash = hash_hmac($this->macHash, $pureData, $key, $rawHash);

            if ($this->compareString($hash, $calculatedHash)) {
                return $pureData;
            }
        }
        return false;
    }

    private $_useLibreSSL;
    private $_randomFile;

    public function generateRandomKey($length = 32)
    {
        if (!is_int($length)) {
            throw new Exception('First parameter ($length) must be an integer');
        }
        if ($length < 1) {
            throw new Exception('First parameter ($length) must be greater than 0');
        }
        // always use random_bytes() if it is available
        if (function_exists('random_bytes')) {
            return random_bytes($length);
        }
        // The recent LibreSSL RNGs are faster and likely better than /dev/urandom.
        // Parse OPENSSL_VERSION_TEXT because OPENSSL_VERSION_NUMBER is no use for LibreSSL.
        // https://bugs.php.net/bug.php?id=71143
        if ($this->_useLibreSSL === null) {
            $this->_useLibreSSL = defined('OPENSSL_VERSION_TEXT')
                && preg_match('{^LibreSSL (\d\d?)\.(\d\d?)\.(\d\d?)$}', OPENSSL_VERSION_TEXT, $matches)
                && (10000 * $matches[1]) + (100 * $matches[2]) + $matches[3] >= 20105;
        }
        // Since 5.4.0, openssl_random_pseudo_bytes() reads from CryptGenRandom on Windows instead
        // of using OpenSSL library. LibreSSL is OK everywhere but don't use OpenSSL on non-Windows.
        if ($this->_useLibreSSL
            || (
                DIRECTORY_SEPARATOR !== '/'
                && substr_compare(PHP_OS, 'win', 0, 3, true) === 0
                && function_exists('openssl_random_pseudo_bytes')
            )
        ) {
            $key = openssl_random_pseudo_bytes($length, $cryptoStrong);
            if ($cryptoStrong === false) {
                throw new Exception(
                    'openssl_random_pseudo_bytes() set $crypto_strong false. Your PHP setup is insecure.'
                );
            }
            if ($key !== false && Strings::byteLength($key) === $length) {
                return $key;
            }
        }

        // mcrypt_create_iv() does not use libmcrypt. Since PHP 5.3.7 it directly reads
        // CryptGenRandom on Windows. Elsewhere it directly reads /dev/urandom.
        if (function_exists('mcrypt_create_iv')) {
            $key = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            if (Strings::byteLength($key) === $length) {
                return $key;
            }
        }

        // If not on Windows, try to open a random device.
        if ($this->_randomFile === null && DIRECTORY_SEPARATOR === '/') {
            // urandom is a symlink to random on FreeBSD.
            $device = PHP_OS === 'FreeBSD' ? '/dev/random' : '/dev/urandom';
            // Check random device for special character device protection mode. Use lstat()
            // instead of stat() in case an attacker arranges a symlink to a fake device.
            $lstat = @lstat($device);
            if ($lstat !== false && ($lstat['mode'] & 0170000) === 020000) {
                $this->_randomFile = fopen($device, 'rb') ?: null;

                if (is_resource($this->_randomFile)) {
                    // Reduce PHP stream buffer from default 8192 bytes to optimize data
                    // transfer from the random device for smaller values of $length.
                    // This also helps to keep future randoms out of user memory space.
                    $bufferSize = 8;

                    if (function_exists('stream_set_read_buffer')) {
                        stream_set_read_buffer($this->_randomFile, $bufferSize);
                    }
                    // stream_set_read_buffer() isn't implemented on HHVM
                    if (function_exists('stream_set_chunk_size')) {
                        stream_set_chunk_size($this->_randomFile, $bufferSize);
                    }
                }
            }
        }

        if (is_resource($this->_randomFile)) {
            $buffer = '';
            $stillNeed = $length;
            while ($stillNeed > 0) {
                $someBytes = fread($this->_randomFile, $stillNeed);
                if ($someBytes === false) {
                    break;
                }
                $buffer .= $someBytes;
                $stillNeed -= Strings::byteLength($someBytes);
                if ($stillNeed === 0) {
                    // Leaving file pointer open in order to make next generation faster by reusing it.
                    return $buffer;
                }
            }
            fclose($this->_randomFile);
            $this->_randomFile = null;
        }
        throw new Exception('Unable to generate a random key');
    }

    public function generateRandomString($length = 32)
    {
        if (!is_int($length)) {
            throw new Exception('First parameter ($length) must be an integer');
        }
        if ($length < 1) {
            throw new Exception('First parameter ($length) must be greater than 0');
        }
        $bytes = $this->generateRandomKey($length);
        return substr(Strings::base64UrlEncode($bytes), 0, $length);
    }

    public function generatePasswordHash($password, $cost = null)
    {
        if ($cost === null) {
            $cost = $this->passwordHashCost;
        }
        if (function_exists('password_hash')) {
            /* @noinspection PhpUndefinedConstantInspection */
            return password_hash($password, PASSWORD_DEFAULT, ['cost' => $cost]);
        }
        $salt = $this->generateSalt($cost);
        $hash = crypt($password, $salt);
        // strlen() is safe since crypt() returns only ascii
        if (!is_string($hash) || strlen($hash) !== 60) {
            throw new Exception('Unknown error occurred while generating hash.');
        }
        return $hash;
    }

    public function validatePassword($password, $hash)
    {
        if (!is_string($password) || $password === '') {
            throw new Exception('Password must be a string and cannot be empty.');
        }
        if (!preg_match('/^\$2[axy]\$(\d\d)\$[\.\/0-9A-Za-z]{22}/', $hash, $matches)
            || $matches[1] < 4
            || $matches[1] > 30
        ) {
            throw new Exception('Hash is invalid.');
        }
        if (function_exists('password_verify')) {
            return password_verify($password, $hash);
        }
        $test = crypt($password, $hash);
        $n = strlen($test);
        if ($n !== 60) {
            return false;
        }
        return $this->compareString($test, $hash);
    }

    protected function generateSalt($cost = 13)
    {
        $cost = (int) $cost;
        if ($cost < 4 || $cost > 31) {
            throw new Exception('Cost must be between 4 and 31.');
        }
        // Get a 20-byte random string
        $rand = $this->generateRandomKey(20);
        // Form the prefix that specifies Blowfish (bcrypt) algorithm and cost parameter.
        $salt = sprintf('$2y$%02d$', $cost);
        // Append the random salt data in the required base64 format.
        $salt .= str_replace('+', '.', substr(base64_encode($rand), 0, 22));
        return $salt;
    }

    public function compareString($expected, $actual)
    {
        $expected .= "\0";
        $actual .= "\0";
        $expectedLength = Strings::byteLength($expected);
        $actualLength = Strings::byteLength($actual);
        $diff = $expectedLength - $actualLength;
        for ($i = 0; $i < $actualLength; $i++) {
            $diff |= (ord($actual[$i]) ^ ord($expected[$i % $expectedLength]));
        }
        return $diff === 0;
    }

    public function maskToken($token)
    {
        // The number of bytes in a mask is always equal to the number of bytes in a token.
        $mask = $this->generateRandomKey(Strings::byteLength($token));
        return Strings::base64UrlEncode($mask . ($mask ^ $token));
    }

    public function unmaskToken($maskedToken)
    {
        $decoded = Strings::base64UrlDecode($maskedToken);
        $length = Strings::byteLength($decoded) / 2;
        // Check if the masked token has an even length.
        if (!is_int($length)) {
            return '';
        }
        return Strings::byteSubstr($decoded, $length, $length) ^ Strings::byteSubstr($decoded, 0, $length);
    }
}
