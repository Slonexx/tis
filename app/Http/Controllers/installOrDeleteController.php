<?php

namespace App\Http\Controllers;

use App\Services\Settings\InstallOrDeleteService;
use Illuminate\Http\Request;

class installOrDeleteController extends Controller
{
    private InstallOrDeleteService $InstallOrDeleteService;

    public function __construct(InstallOrDeleteService $InstallOrDeleteService)
    {
        $this->InstallOrDeleteService = $InstallOrDeleteService;
    }

    public function insert(Request $request)
    {
        $data = $request->validate([
            "tokenMs" => 'required|string',
            "accountId" => "required|string",
        ]);

        $this->InstallOrDeleteService->insert($data);

    }
}
