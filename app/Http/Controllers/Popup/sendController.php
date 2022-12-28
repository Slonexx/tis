<?php

namespace App\Http\Controllers\Popup;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TicketController;
use App\Services\ticket\dev_TicketService;
use Illuminate\Http\Request;

class sendController extends Controller
{

    public function SendRequest(Request $request){

        $accountId = $request->accountId;
        $id_entity = $request->id_entity;
        $entity_type = $request->entity_type;

        if ($request->money_card === null) $money_card = 0;
        else $money_card = $request->money_card;
        if ($request->money_cash === null) $money_cash = 0;
        else $money_cash = $request->money_cash;
        $pay_type = $request->pay_type;

        $total = $request->total;

        $position = json_decode(json_encode($request->positions));

        $body = [
            'accountId' => $accountId,
            'id_entity' => $id_entity,
            'entity_type' => $entity_type,

            'money_card' => $money_card,
            'money_cash' => $money_cash,
            'pay_type' => $pay_type,

            'total' => $total,

            'positions' => $position,
        ];

        //dd(($body), json_encode($body));


        try {

            $ticket = json_decode(json_encode((app(dev_TicketService::class)->createTicket($body))));

            return response()->json([
                'message' => $ticket->original->status,
                'code' => $ticket->original->code,

                'id' => $ticket->original->postTicket->data->id,
                'shift' => $ticket->original->postTicket->data->shift,
                'fixed_check' => $ticket->original->postTicket->data->fixed_check,
                'created_at' => $ticket->original->postTicket->data->created_at,
                'link' => $ticket->original->postTicket->data->link,
                'html' => $ticket->original->postTicket->data->html,
            ], 200);

        } catch (\Throwable $e){
            //dd($e->getCode());
            return response()->json($e->getMessage());
        }
    }


}
