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

    public function setAllAttributesVendor($accountId, $tokenMs)
    {
        $this->attributeService->setAllAttributesMs(['tokenMs'=> $tokenMs, 'accountId'=>$accountId]);
    }

    public function setAllAttributesVendorData($data)
    {
        $this->attributeService->setAllAttributesMs($data);
    }

}
