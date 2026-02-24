<?php

use App\Enums\EmployeeWorkspaceStatus;
use App\Enums\InvitedByType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_workspaces', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->enum('status', EmployeeWorkspaceStatus::values())->default(EmployeeWorkspaceStatus::Pending->value);
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->string('tenant_name')->nullable();
            $table->string('invited_by')->nullable();
            $table->enum('invited_by_type', InvitedByType::values())->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_workspaces');
    }
};
