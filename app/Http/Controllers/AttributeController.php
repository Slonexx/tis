<?php

namespace App\Http\Controllers;

use App\Services\AdditionalServices\AttributeService;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    private AttributeService $attributeService;

    /**
     * @param AttributeService $attributeService
     */
    public function __construct(AttributeService $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    public function setAllAttributesVendor(Request $request, $accountId, $tokenMs)
    {
        $data = ['tokenMs'=> $tokenMs, 'accountId'=>$accountId];

        $this->attributeService->setAllAttributesMs($data);
    }

}
