<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User; // Your User model
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class IssueApiTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dscms:issue-token
                            {userIdOrEmail : The ID or email of the user to issue the token for}
                            {tokenName : A descriptive name for the token (e.g., java-server-processor)}
                            {--abilities= : Comma-separated list of abilities/permissions for the token (e.g., read:documents,update:verification)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Issue a new API token for a specified user with optional abilities.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userIdOrEmail = $this->argument('userIdOrEmail');
        $tokenName = $this->argument('tokenName');
        $abilitiesInput = $this->option('abilities');

        $abilities = [];
        if (!empty($abilitiesInput)) {
            $abilities = array_map('trim', explode(',', $abilitiesInput));
        }

        // Find the user by ID or email
        $user = User::where('id', $userIdOrEmail)->orWhere('email', $userIdOrEmail)->first();

        if (!$user) {
            $this->error("User with ID or email '{$userIdOrEmail}' not found.");
            return Command::FAILURE;
        }

        // Validate token name (optional, but good practice)
        if (Str::length($tokenName) < 3 || Str::length($tokenName) > 100) {
            $this->error("Token name must be between 3 and 100 characters.");
            return Command::FAILURE;
        }

        // Create the token
        try {
            $token = $user->createToken($tokenName, $abilities);

            $this->info("API Token issued successfully for user: {$user->name} ({$user->email})");
            $this->line("Token Name: <comment>{$tokenName}</comment>");
            if (!empty($abilities)) {
                $this->line("Abilities: <comment>" . implode(', ', $abilities) . "</comment>");
            }
            $this->line("Your Plain Text Token (copy this value, it will not be shown again):");
            $this->warn($token->plainTextToken);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to issue token: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
