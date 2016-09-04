<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Alert;
use JWTAuth;

class AlertController extends Controller
{
    
    public function __construct()
    {
      $this->middleware('jwt.auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $user = AlertController::getAuthenticatedUser();
      $alerts = Alert::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

      foreach($alerts as $alert) {
        $alert->view_alert = [
          'href' => 'api/v1/alerts/' . $alert->id,
          'method' => 'GET'
        ];
      }

      $response = [
        'msg' => 'List of all alerts',
        'alerts' => $alerts
      ];

      return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $this->validate($request, [
        'from_currency' => 'required',
        'to_currency' => 'required',
        'upper_rate' => 'required',
        'lower_rate' => 'required',
      ]);

      $user = AlertController::getAuthenticatedUser();

      $symbol = $request->input('from_currency') . $request->input('to_currency');

      $alert = new Alert([
        'from_currency' => $request->input('from_currency'),
        'to_currency' => $request->input('to_currency'),
        'upper_rate' => $request->input('upper_rate'),
        'lower_rate' => $request->input('lower_rate'),
        'user_id' => $user->id,
        'status' => 'Active',
        'symbol' => $symbol,
      ]);

      if ($alert->save()) {
        $alert->view_alert = [
          'href' => 'api/v1/alerts/' . $alert->id,
          'method' => 'GET'
        ];
        $message = [
          'msg' => 'Alert created',
          'alert' => $alert
        ];
        return response()->json($alert, 201);
      }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $user = AlertController::getAuthenticatedUser();
      $alert = Alert::findOrFail($id);

      if ($alert->user_id != $user->id) {
        return response()->json(['msg' => "You don't have permission to view this alert"], 401);
      };

      $alert->view_alert = [
        'href' => 'api/v1/alerts',
        'method' => 'GET'
      ];

      $response = [
        'msg' => 'Alert information',
        'alert' => $alert
      ];

      return response()->json($response, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //public function update(Request $request, $id)
    //{
      //$this->validate($request, [
        //'from_currency' => 'required',
        //'to_currency' => 'required',
        //'upper_rate' => 'required',
        //'lower_rate' => 'required',
      //]);

      //$user = AlertController::getAuthenticatedUser();
      //$alert = Alert::findOrFail($id);

      //if ($alert->user_id != $user->id) {
        //return response()->json(['msg' => "You don't have permission to update this alert"], 401);
      //};

      //$alert->from_currency =  $request->input('from_currency');
      //$alert->to_currency =  $request->input('to_currency');
      //$alert->upper_rate = $request->input('upper_rate');
      //$alert->lower_rate =  $request->input('lower_rate');

      //if (!$alert->update()) {
        //return response()->json(['msg' => 'Error during updating'], 404);
      //}

      //$alert->view_alert = [
        //'href' => 'api/v1/alert/'.$alert->id,
        //'method' => 'GET'
      //];

      //$response = [
        //'msg' => 'Alert updated',
        //'alert' => $alert
      //];

      //return response()->json($response, 200);
    //}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $user = AlertController::getAuthenticatedUser();
      $alert = Alert::findOrFail($id);

      if ($alert->user_id != $user->id) {
        return response()->json(['msg' => "You don't have permission to delete this alert"], 401);
      };

      $alert->delete();

      $response = [
        'msg' => 'Alert deleted',
        'create' => [
          'href' => 'api/v1/alerts',
          'method' => 'POST',
          'params' => 'lower_rate, upper_rate, from_currency, to_currency'
        ]
      ];

      return response()->json($response, 200);
    }

    public static function getAuthenticatedUser()
    {
      try {

        if (! $user = JWTAuth::parseToken()->authenticate()) {
          return response()->json(['user_not_found'], 404);
        }

      } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

        return response()->json(['token_expired'], $e->getStatusCode());

      } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

        return response()->json(['token_invalid'], $e->getStatusCode());

      } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

        return response()->json(['token_absent'], $e->getStatusCode());

      }

      // the token is valid and we have found the user via the sub claim
      return $user;
    }

}
