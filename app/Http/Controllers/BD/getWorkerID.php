<?php

namespace App\Http\Controllers\BD;

use App\Http\Controllers\Controller;
use App\Models\wordersModel;
use Illuminate\Http\Request;

class getWorkerID extends Controller
{
    public string $id;
    public mixed $access;

    public function __construct($id)
    {
        $find = wordersModel::query()->where('id', $id)->first();
        try {
            $result = $find->getAttributes();
        } catch (\Throwable $e) {
            $result = [
                'id' => $id,
                'access' => null,
            ];
        }
        $this->id = $result['id'];
        $this->access = $result['access'];
    }
}
