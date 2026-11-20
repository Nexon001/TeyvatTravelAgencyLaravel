<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {email : Email the administrator logs in with} {--name=Director : Display name}';

    protected $description = 'Create an administrator account, or promote an existing account to administrator';

    public function handle(): int
    {
        $email = Str::lower(trim((string) $this->argument('email')));

        $existing = User::where('email', $email)->first();

        if ($existing !== null) {
            if ($existing->role === 'admin') {
                $this->components->info($email.' is already an administrator.');

                return self::SUCCESS;
            }

            if (! $this->confirm($email.' already has a customer account. Make it an administrator?')) {
                return self::FAILURE;
            }

            $existing->forceFill(['role' => 'admin'])->save();
            $this->components->info($email.' is now an administrator.');

            return self::SUCCESS;
        }

        // The password is typed here and never stored in a file, seeder or .env.
        $password = (string) $this->secret('Password (min 8 characters)');
        $confirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make(
            ['email' => $email, 'password' => $password],
            ['email' => ['required', 'email', 'max:100'], 'password' => ['required', Password::min(8)]],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        if ($password !== $confirmation) {
            $this->components->error('The two passwords do not match.');

            return self::FAILURE;
        }

        $user = new User([
            'name' => (string) $this->option('name'),
            'email' => $email,
            'password' => Hash::make($password),
        ]);
        $user->forceFill(['role' => 'admin', 'email_verified_at' => now()])->save();

        $this->components->info('Administrator '.$email.' created.');

        return self::SUCCESS;
    }
}
