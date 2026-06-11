<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\Vapid;

class GenerateVapidKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webpush:keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate VAPID public and private keys for Web Push Notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Fix for Windows / XAMPP OpenSSL configuration file path issue
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $possiblePaths = [
                'D:\\xampp\\apache\\conf\\openssl.cnf',
                'C:\\xampp\\apache\\conf\\openssl.cnf',
                'D:\\xampp\\php\\extras\\openssl\\openssl.cnf',
                'C:\\xampp\\php\\extras\\openssl\\openssl.cnf',
                'D:\\xampp\\php\\extras\\ssl\\openssl.cnf',
                'C:\\xampp\\php\\extras\\ssl\\openssl.cnf',
            ];
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    putenv("OPENSSL_CONF={$path}");
                    break;
                }
            }
        }

        try {
            $keys = Vapid::createVapidKeys();
        } catch (\RuntimeException $e) {
            $this->error('Gagal membuat kunci VAPID. Hal ini umum terjadi pada lingkungan Windows/XAMPP karena konfigurasi OpenSSL.');
            $this->warn('Solusi: Jalankan perintah berikut terlebih dahulu di terminal PowerShell Anda:');
            $this->line('  $env:OPENSSL_CONF="D:\xampp\apache\conf\openssl.cnf"; php artisan webpush:keys');
            return 1;
        }

        $pubKey = $keys['publicKey'];
        $privKey = $keys['privateKey'];

        $envFile = base_path('.env');
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);

            if (str_contains($envContent, 'VAPID_PUBLIC_KEY=')) {
                $envContent = preg_replace('/VAPID_PUBLIC_KEY=.*/', 'VAPID_PUBLIC_KEY="' . $pubKey . '"', $envContent);
            } else {
                $envContent .= "\nVAPID_PUBLIC_KEY=\"" . $pubKey . "\"";
            }

            if (str_contains($envContent, 'VAPID_PRIVATE_KEY=')) {
                $envContent = preg_replace('/VAPID_PRIVATE_KEY=.*/', 'VAPID_PRIVATE_KEY="' . $privKey . '"', $envContent);
            } else {
                $envContent .= "\nVAPID_PRIVATE_KEY=\"" . $privKey . "\"";
            }

            file_put_contents($envFile, $envContent);
            $this->info('VAPID keys successfully generated and saved to .env file!');
            $this->line('Public Key: ' . $pubKey);
        } else {
            $this->error('.env file not found.');
            $this->line('VAPID_PUBLIC_KEY="' . $pubKey . '"');
            $this->line('VAPID_PRIVATE_KEY="' . $privKey . '"');
        }
    }
}
