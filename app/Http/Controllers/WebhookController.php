<?php


namespace App\Http\Controllers;


use App\Models\Webhook;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function webhookAction(Request $request)
    {
        $data = $request->all();
        Webhook::create(
            [
                'data' => $data,
            ]
        );
    }
}
