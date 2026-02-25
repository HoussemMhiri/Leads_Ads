<?php

namespace App\Http\Controllers\Workspace;

use App\Actions\Workspace\UpdateWorkspaceAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\UpdateWorkspaceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class WorkspaceController extends Controller
{
    public function update(UpdateWorkspaceRequest $request, UpdateWorkspaceAction $action): JsonResponse
    {
        $tenant = tenancy()->tenant;

        $action->execute(
            tenant: $tenant,
            name: $request->validated('name'),
            logo: $request->file('logo'),
        );

        return response()->json([
            'message' => 'Workspace updated successfully.',
            'workspace' => [
                'name' => $tenant->company_name,
                'logo_url' => $tenant->logo_path
                    ? Storage::disk('central_public')->url($tenant->logo_path)
                    : null,
            ],
        ]);
    }
}
