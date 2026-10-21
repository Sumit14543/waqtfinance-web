<?php
declare(strict_types=1);

class WaqtTOTP
{
    /**
     * Generate a random Base32 secret key (16 characters)
     */
    public static function generateSecret(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 16; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Verify a 6-digit TOTP code against a secret key (with 1-period clock drift allowance)
     */
    public static function verifyCode(string $secret, string $code, int $discrepancy = 1): bool
    {
        $currentTime = time();
        $timeStep = 30;

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $timeSlice = (int) floor($currentTime / $timeStep) + $i;
            if (self::calculateCode($secret, $timeSlice) === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate TOTP code for a specific time slice
     */
    private static function calculateCode(string $secret, int $timeSlice): string
    {
        $secretKey = self::base32Decode($secret);

        // Pack time slice into an 8-byte binary string
        $timeBin = pack('N*', 0) . pack('N*', $timeSlice);

        // HMAC-SHA1 of time slice using the decoded secret key
        $hash = hash_hmac('sha1', $timeBin, $secretKey, true);

        // Dynamic truncation to extract 4 bytes
        $offset = ord($hash[19]) & 0xf;
        $binary = ((ord($hash[$offset]) & 0x7f) << 24)
            | ((ord($hash[$offset + 1]) & 0xff) << 16)
            | ((ord($hash[$offset + 2]) & 0xff) << 8)
            | (ord($hash[$offset + 3]) & 0xff);

        // Get 6-digit code
        $otp = $binary % 1000000;
        return str_pad((string) $otp, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Decode a Base32 string into binary data
     */
    private static function base32Decode(string $base32): string
    {
        $base32 = strtoupper($base32);
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $map = array_flip(str_split($chars));

        $binary = '';
        $buffer = 0;
        $bits = 0;

        foreach (str_split($base32) as $char) {
            if ($char === '=') {
                break;
            }
            if (!isset($map[$char])) {
                continue;
            }

            $buffer = ($buffer << 5) | $map[$char];
            $bits += 5;

            if ($bits >= 8) {
                $bits -= 8;
                $binary .= chr(($buffer >> $bits) & 0xff);
            }
        }

        return $binary;
    }

    /**
     * Generate the otpauth:// URI for QR Code apps
     */
    public static function getQRUri(string $label, string $secret, string $issuer = 'WaqtFinance'): string
    {
        return 'otpauth://totp/' . rawurlencode($issuer . ':' . $label) 
            . '?secret=' . rawurlencode($secret) 
            . '&issuer=' . rawurlencode($issuer);
    }
}
