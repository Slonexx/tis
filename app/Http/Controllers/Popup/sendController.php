<?php

namespace App\Http\Controllers\Popup;

use App\Clients\KassClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Controller;
use App\Http\Controllers\globalObjectController;
use App\Http\Controllers\TicketController;
use App\Models\zHtmlResponce;
use App\Services\ticket\dev_CreateTicketService;
use App\Services\ticket\dev_TicketService;
use App\Services\ticket\TestTicketService;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class sendController extends Controller
{

    public function SendRequest(Request $request): \Illuminate\Http\JsonResponse
    {

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

            if ($ticket->original->status == 'error'){
                return response()->json($ticket->original);
            } else {
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
            }
        } catch (\Throwable $e){
            return response()->json($e->getMessage());
        }
    }

    public function SendCreateRequest(Request $request){

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

            'money_card' => $money_card,
            'money_cash' => $money_cash,
            'pay_type' => $pay_type,

            'total' => $total,

            'positions' => $position,
        ];

        try {

            $ticket = json_decode(json_encode((app(dev_CreateTicketService::class)->createTicket($body))));

            if ($ticket->original->status == 'error'){
                return response()->json($ticket->original);
            } else {
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
            }
        } catch (\Throwable $e){
            return response()->json($e->getMessage());
        }


    }

    public function DevRequest(Request $request){
        $accountId = $request->accountId;
        $id_entity = $request->object_Id;
        $entity_type = $request->entity_type;

        if ($request->money_card === null) $money_card = 0;
        else $money_card = $request->money_card;
        if ($request->money_cash === null) $money_cash = 0;
        else $money_cash = $request->money_cash;
        $pay_type = $request->pay_type;

        $total = $request->total;

        $position = json_decode(($request->position));

        $body = [
            'accountId' => $accountId,
            'object_Id' => $id_entity,
            'entity_type' => $entity_type,

            'money_card' => $money_card,
            'money_cash' => $money_cash,
            'pay_type' => $pay_type,

            'total' => $total,

            'position' => $position,
        ];

            $ticket = json_decode(json_encode((app(TestTicketService::class)->createTicket($body))));
    }


    public function RequestClose(Request $request, ): \Illuminate\Http\JsonResponse
    {
        $accountId = $request->accountId;
        $SettingBD = new getMainSettingBD($accountId);
        $Config = new globalObjectController();
        $ClientTIS = new KassClient($SettingBD->authtoken);

        try {

            $get_user = $ClientTIS->GETClient($Config->apiURL_ukassa.'auth/get_user/');
            $kassa = $get_user->user_kassas->kassa;

            $body = ['kassa'=> $request->idKassa, 'html_code'=>true];
            $close_shift = $ClientTIS->POSTClient($Config->apiURL_ukassa.'kassa/close_z_shift/', $body);

           return response()->json($close_shift);

        } catch (BadResponseException $e){
            return response()->json(json_decode($e->getResponse()->getBody()->getContents()));
        }


    }


}
