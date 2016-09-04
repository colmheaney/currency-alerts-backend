<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use App\Alert;
use Illuminate\Support\Facades\Mail;
use App\Mail\AlertTriggered;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    private function getSymbols() 
    {
      // Get only unique active alerts
      $alerts = Alert::where('status', 'Active')->distinct()->get(['symbol']);
      $symbols_arr = [];

      foreach ($alerts as $alert) 
      {
        if($alert->symbol) {
          array_push($symbols_arr, $alert->symbol);
        }
      }
      return join(', ', $symbols_arr);
    }

    private function generateUrl($symbols_str) {
      $BASE_URL = 'https://query.yahooapis.com/v1/public/yql';

      $yql_query = 'select Rate, id from yahoo.finance.xchange where pair in ("'.$symbols_str.'")';

      $END_URL = '&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback';

      return $BASE_URL . "?q=" . rawurlencode($yql_query) . $END_URL;
    }

    private function handleAlert($alert, $current_rate) 
    {
      if($alert->symbol == $current_rate->id) 
      {
        // check if conditions have been met to trigger alert
        if ($current_rate->Rate <= $alert->lower_rate || 
            $current_rate->Rate >= $alert->upper_rate) {

          // send email and update the alert status
          Mail::to($alert->user->email)->send(new AlertTriggered($alert, $current_rate->Rate));
          $alert->status = "Not active";

          if (!$alert->update()) {
            Log::info("Error updating alert status - alert id: ".$alert->id);
          }
        }
      }
    }

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
      $schedule->call(function () 
      {
        $symbols_str = $this->getSymbols();
        // Only proceed if there are active alerts
        if(!empty($symbols_str)) 
        {
          $yql_query = $this->generateUrl($symbols_str);
          $client = new HttpClient();
          // try to get rates from yahoo finance api
          try {
            $response = $client->get($yql_query);
            $response_code = $response->getStatusCode();
            $response_body = $response->getBody();
            Log::info("Response from Yahoo finance api: ".$response_code."\n");
            Log::info("Response from Yahoo finance api: ".$response_body."\n");

            $rates = json_decode($response_body)->query->results->rate;
            $alerts = Alert::all()->where('status', 'Active');

            foreach($alerts as $alert) 
            {
              // because yql is stupid need to check if only 1 result returned
              // and handle it differently
              if(sizeof($rates) > 1) 
              {
                foreach($rates as $current_rate) 
                {
                  $this->handleAlert($alert, $current_rate);
                }
              } else { // there is only 1 result
                $this->handleAlert($alert, $rates);
              }
            }
          } catch (RequestException $e) {
            Log::info("Error getting results from yahoo: ".$e);
          }
        } else {
          Log::info("No active alerts");
        }
      })->everyFiveMinutes();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
