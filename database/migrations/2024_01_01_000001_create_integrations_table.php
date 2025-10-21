<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create integrations table
        Schema::create(config('integration.table_names.integrations', 'integrations'), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('client_id', 100);
            $table->string('client_secret', 100);
            $table->json('redirect_uris')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            
            // Role-based access
            $table->string('role')->nullable()->comment('Default role for this integration (admin, user, guest, etc.)');
            
            // Permissions management
            $table->json('permissions')->nullable()->comment('Resource-based permissions in JSON format');
            $table->json('allowed_scopes')->nullable()->comment('OAuth scopes allowed for this integration');
            $table->json('default_scopes')->nullable()->comment('Default OAuth scopes granted');
            
            // Security restrictions
            $table->json('ip_whitelist')->nullable()->comment('IP address restrictions and whitelist configuration');
            $table->json('geo_restrictions')->nullable()->comment('Geographic location restrictions');
            $table->json('time_restrictions')->nullable()->comment('Time-based access restrictions');
            
            // Rate limiting
            $table->json('rate_limits')->nullable()->comment('Rate limiting configuration per scope/permission');
            
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Unique and composite indexes with descriptive names
            $table->unique('client_id', 'integrations_client_id_unique');
            $table->index(['user_id', 'status'], 'integrations_user_status_idx');
            $table->index('status', 'integrations_status_idx');
            $table->index('role', 'integrations_role_idx');
            $table->index('created_at', 'integrations_created_at_idx');
        });

        // Create integration_secrets table
        Schema::create(config('integration.table_names.integration_secrets', 'integration_secrets'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_id')->constrained('integrations')->onDelete('cascade');
            $table->string('name');
            $table->string('secret_key', 100);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Unique and composite indexes with descriptive names
            $table->unique('secret_key', 'integration_secrets_secret_key_unique');
            $table->index(['integration_id', 'status'], 'integration_secrets_integration_status_idx');
            $table->index('status', 'integration_secrets_status_idx');
            $table->index('expires_at', 'integration_secrets_expires_at_idx');
            $table->index('last_used_at', 'integration_secrets_last_used_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('integration.table_names.integration_secrets', 'integration_secrets'));
        Schema::dropIfExists(config('integration.table_names.integrations', 'integrations'));
    }
};
