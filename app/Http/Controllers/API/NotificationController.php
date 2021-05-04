<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\API\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Notification::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_id_user' => 'required',
            'to_id_user' => 'required',
            'message' => 'required',
        ]);
        if($validator->fails()){
            return [
                'message' => 'Validation error',
                'error' => $validator->errors(),
                'code' => 500,
            ];
        }
        $response = DB::insert("
        insert into
        notification (from_id_user, to_id_user, message)
        values (?, ?, ?)
        ", [
            $request->from_id_user,
            $request->to_id_user,
            $request->message,
        ]);
        if(!$response){
            return [
                'message' => 'Database insert error',
                'code' => 500,
            ];
        }
        return [
            'message' => 'Insert data success',
            'code' => 200,
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $response = $this->getById($id);
        if(!$response){
            return [
                'message' => 'Data not found',
                'code' => 500,
            ];
        }
        return $response;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function showAllSendBy($id_user){
        return Notification::where('from_id_user', $id_user)->get();
    }

    public function showAllSendTo($id_user){
        return Notification::where('to_id_user', $id_user)->get();
    }

    public function setAsRead($id){
        return DB::table('notification')->where('id_notification', $id)->update(['read' => 'true']);
    }

    private function getById($id){
        return Notification::where('id_notification', $id)->first();
    }
}
