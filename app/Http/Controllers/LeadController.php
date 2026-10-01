<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveLeadRequest;
use App\Services\LeadService;
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{
    private LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    public function storeLeads(SaveLeadRequest $request)
    {
        try {
            $validatedData = $request->validated();
    
            $response = $this->leadService->storeLeads($validatedData);

            return $this->response_success($response, 'Lead stored successfully', 201);
        } catch (\Throwable $e) {
            Log::error('Error storing lead: ', ['exception' => $e]);
            return $this->response_error('An error ocurred while storing the lead', 500);
        }
    }
}
