<?php

namespace App\Http\Controllers;

use GoCardlessPro\Resources\RedirectFlow;
use Illuminate\Http\Request;

class GoCardlessController extends Controller
{
    public function index()
    {
    
        $client = new \GoCardlessPro\Client(array(
            'access_token' => env('GOCARDLESS_ACCESS_TOKEN'),
            'environment'  => \GoCardlessPro\Environment::SANDBOX
        ));
        //dd($client->subscriptions()->list());
        //dd($client->customers()->list());
        $redirectFlow =  $client->redirectFlows()->create([
            "params" => 
            [
                "description" => "Testing Purpose",
                // Not the access token
                "session_token" => "client_redirected",
                "success_redirect_url" => route('redirection'),           
            ],
        ]);
        if($redirectFlow){
            return redirect($redirectFlow->redirect_url);
        }
        // dd($subscription->id);
        return view('welcome');
    }

    public function redirection(Request $request)
    {
        $client = new \GoCardlessPro\Client(array(
            'access_token' => env('GOCARDLESS_ACCESS_TOKEN'),
            'environment'  => \GoCardlessPro\Environment::SANDBOX
        ));
        $redir =  $client->redirectFlows()->complete($request->redirect_flow_id, [
            "params" => ["session_token" => "client_redirected"]
        ]);
        if($redir)
        {
            //return redirect($redir->confirmation_url);
            return redirect()->route('subscription',[$redir->links->mandate]);
        }
        //dd($redir->links->mandate,$redir->links->customer,$redir->confirmation_url);
    }
    public function subscription(Request $request, $mandate_id)
    {
        $client = new \GoCardlessPro\Client(array(
            'access_token' => env('GOCARDLESS_ACCESS_TOKEN'),
            'environment'  => \GoCardlessPro\Environment::SANDBOX
        ));
        $subscription = $client->subscriptions()->create([
            "params" => [
                "amount" => 1500, // 15 GBP in pence
                "currency" => "USD",
                "interval_unit" => "monthly",
                "day_of_month" => "1",
                "links" => [
                    "mandate" => $mandate_id    
                                 // Mandate ID from the last section
                ],
                "metadata" => [
                    "subscription_number" => "ABC1233454"
                ]
            ],
            "headers" => [
                "Idempotency-Key" => "random_subscription_specific_string"
            ]
          ]);
       // return redirect($request->confirmation_url);

        dd($subscription);
    }
}
