<?php

namespace Litepie\Integration\Console\Commands;

use Illuminate\Console\Command;
use Litepie\Integration\Models\Integration;

class ListIntegrationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'integration:list 
                            {--user= : Filter by user ID}
                            {--status= : Filter by status (active/inactive)}
                            {--limit=10 : Limit the number of results}';

    /**
     * The console command description.
     */
    protected $description = 'List all integrations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = Integration::query();

        if ($userId = $this->option('user')) {
            $query->forUser($userId);
        }

        if ($status = $this->option('status')) {
            $query->where('status', $status);
        }

        $limit = (int) $this->option('limit');
        $integrations = $query->limit($limit)->get();

        if ($integrations->isEmpty()) {
            $this->info('No integrations found.');
            return Command::SUCCESS;
        }

        $headers = ['ID', 'Name', 'Client ID', 'Status', 'User ID', 'Created At'];
        $rows = $integrations->map(function ($integration) {
            return [
                $integration->id,
                $integration->name,
                $integration->client_id,
                $integration->status,
                $integration->user_id,
                $integration->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        $this->table($headers, $rows);

        return Command::SUCCESS;
    }
}
