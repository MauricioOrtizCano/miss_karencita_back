<?php

namespace App\Services;

use App\Models\Guardian;
use App\Models\Child;
use Illuminate\Support\Facades\DB;

class LeadService
{
    public function storeLeads(array $data)
    {
        DB::beginTransaction();

        try {
            $restructuredData = [
                'name' => $data['caregiverName'],
                'whatsapp' => $data['caregiverWhatsapp'],
                'email' => $data['caregiverEmail'] ?? null,
                'data_authorization' => $data['caregiverDataAuthorization'],
                'authorization_date' => now(),
                'policy_version' => config('app.policy_version'),
            ];

            $guardian = Guardian::create($restructuredData);

            foreach ($data['children'] as $childData) {
                $child = [
                    'name' => $childData['name'],
                    'age_range' => $childData['ageRange'],
                    'gender' => $childData['gender'] ?? null,
                    'message' => $childData['message'] ?? null,
                    'guardian_id' => $guardian->id,
                ];

                Child::create($child);
            }

            DB::commit();
            return $guardian->load('children');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}