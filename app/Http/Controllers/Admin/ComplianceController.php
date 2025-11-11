<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ComplianceService;
use Illuminate\Http\Request;

class ComplianceController extends Controller
{
    protected $complianceService;

    public function __construct(ComplianceService $complianceService)
    {
        $this->complianceService = $complianceService;
    }

    public function index()
    {
        $report = $this->complianceService->generateComplianceReport();
        return view('admin.compliance.index', compact('report'));
    }

    public function exportUserData(User $user)
    {
        $data = $this->complianceService->exportUserData($user);
        
        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="user-data-' . $user->id . '.json"');
    }

    public function anonymizeUser(Request $request, User $user)
    {
        $this->complianceService->anonymizeUser($user);

        return response()->json([
            'success' => true,
            'message' => 'User data anonymized successfully'
        ]);
    }

    public function deleteUserData(Request $request, User $user)
    {
        $this->complianceService->deleteUserData($user);

        return response()->json([
            'success' => true,
            'message' => 'User data deleted successfully'
        ]);
    }

    public function applyRetention()
    {
        $results = $this->complianceService->applyRetentionPolicy();

        return response()->json([
            'success' => true,
            'message' => 'Retention policy applied',
            'results' => $results
        ]);
    }
}
