<?php

namespace App\Actions\Security;

use App\Models\System\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Enable2FAAction
{
    public function execute(User $user): array
    {
        $secret = $this->generateSecret();
        $qrCode = $this->generateQRCode($user->email, $secret);

        $user->update([
            'two_fa_enabled' => false,
            'two_fa_secret' => encrypt($secret),
        ]);

        return [
            'secret' => $secret,
            'qr_code' => $qrCode,
            'backup_codes' => $this->generateBackupCodes($user),
        ];
    }

    public function verify(User $user, string $code): bool
    {
        $secret = decrypt($user->two_fa_secret);
        $time = floor(time() / 30);

        for ($i = -1; $i <= 1; $i++) {
            $expectedCode = $this->generateTOTPCode($secret, $time + $i);
            if (hash_equals($code, $expectedCode)) {
                return true;
            }
        }

        return false;
    }

    private function generateSecret(): string
    {
        return Str::random(32);
    }

    private function generateQRCode(string $email, string $secret): string
    {
        return "otpauth://totp/ERP%20POS:{$email}?secret={$secret}&issuer=ERP%20POS";
    }

    private function generateBackupCodes(User $user): array
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $code = Str::random(8);
            $codes[] = $code;

            DB::table('two_fa_backup_codes')->create([
                'user_id' => $user->id,
                'code' => hash('sha256', $code),
                'used_at' => null,
            ]);
        }

        return $codes;
    }

    private function generateTOTPCode(string $secret, int $time): string
    {
        $secretBinary = base64_decode($secret);
        $hmac = hash_hmac('sha1', pack('N*', 0, $time), $secretBinary, true);
        $offset = ord(substr($hmac, -1)) & 0x0F;
        $code = (unpack('N', substr($hmac, $offset, 4))[1] & 0x7FFFFFFF) % 1000000;

        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }
}
