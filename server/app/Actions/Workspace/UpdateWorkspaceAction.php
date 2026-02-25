<?php

namespace App\Actions\Workspace;

use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UpdateWorkspaceAction
{
    public function execute(Tenant $tenant, ?string $name = null, ?UploadedFile $logo = null): Tenant
    {
        if ($name !== null) {
            $tenant->company_name = $name;
        }

        if ($logo !== null) {
            if ($tenant->logo_path) {
                Storage::disk('central_public')->delete($tenant->logo_path);
            }

            $slug = Str::slug($tenant->company_name ?? (string) $tenant->id);
            $path = $logo->store("workspaces/{$slug}/logo", 'central_public');
            $tenant->logo_path = $path;
        }

        $tenant->save();

        return $tenant;
    }
}
