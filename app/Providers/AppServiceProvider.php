<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->generateSecretsIfMissing();
    }

    protected function generateSecretsIfMissing()
    {
        $secrets = [
            'API_TOKEN'  => Str::random(64),
            'JWT_SECRET' => Str::random(64)
        ];

        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);
        $updated    = false;

        foreach ($secrets as $key => $value) {
            if (!preg_match("/^{$key}=.*/m", $envContent)) {
                $envContent .= `\n{$key}={$value}`;
                $updated     = true;
            }
        }

        if ($updated) {
            file_put_contents($envPath, $envContent);
        }
    }
}
