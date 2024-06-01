<?php

namespace App\Http\Controllers\IOT;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Helpers\MyLib;
use App\Exceptions\MyException;

use App\Model\Room;
use App\Model\RoomActivity;
use App\Http\Resources\RoomActivityResource;
use App\Http\Requests\RoomActivityRequest;

use App\Events\DashboardDataReceived;

use App\Exports\MyReport;
use Excel;
use DB;

class RoomActivityController extends Controller
{

  public const DO_NOT_DISTURB = "Do Not Disturb";
  public const MAKE_UP_ROOM = "Make Up Room";

  public function __construct(Request $request)
  {
  }

  public function store(RoomActivityRequest $request)
  {
    // $n = 1-37 (apartment) / 1-58 (villa)
    // $g = a (apartment) / v (villa)
    // $s = do_not_disturb (Do Not Disturb) / make_up_room (Make Up Room)

    // ?number=$n&group=$g&status=$s
    
    $status=$request->status ?? "";
    if($status=="do_not_disturb") $status = self::DO_NOT_DISTURB;
    if($status=="make_up_room") $status = self::MAKE_UP_ROOM;

    $group = $request->group ?? "";
    if($group=="v") $group = "Villa";
    if($group=="a") $group = "Apartment";
    
    $number = $request->number ?? "";
    
    $room = Room::where("number",$number)->where("group",$group)->first();
    if(!$room){
      return response()->json([
        "message"=>"Room Unregistered",
      ],400);
    }

    $created_at = MyLib::getMillis();

    $exist = RoomActivity::where("room_id",$room->id)->orderBy('created_at',"desc")->first();
    if($exist){
      if($exist->status==$status){
        return response()->json("OK",200);
      }
    }
    
    $insert=[
      "room_id"=>$room->id,
      "status"=>$status,
      "created_at"=>$created_at
    ];

    $model_query=new RoomActivity();
    if ($model_query->insert($insert)) {      
      broadcast(new DashboardDataReceived($insert));
      return response()->json("OK",200);
    }
    return response()->json([
        "message"=>"FAILED"
    ],400);
  }


  public function get(RoomActivityRequest $request)
  {
    // $n = 1-37 (apartment) / 1-58 (villa)
    // $g = a (apartment) / v (villa)
    // ?number=$n&group=$g

    $group = $request->group ?? "";
    if($group=="v") $group = "Villa";
    if($group=="a") $group = "Apartment";
    
    $number = $request->number ?? "";

    $room = Room::where("number",$number)->where("group",$group)->first();
    if(!$room){
      return response()->json([
        "message"=>"Room Unregistered",
      ],400);
    }

    // $roomActivity = RoomActivity::whereIn("room_id",function($q)use($number,$group) {
    //   $q->select("id")->from('rooms')->where("number",$number)->where("group",$group);
    // })->orderBy('created_at',"desc")->first();

    $roomActivity = RoomActivity::where("room_id",$room->id)->orderBy('created_at',"desc")->first();

    $normal = "";
    $do_not_disturb = "d";
    $make_up_room = "m";

    if(!$roomActivity){
      return response()->json($normal,200);
      // return response()->json([
      //   "message"=>"Failed",
      // ],400);
    }

    $status = $roomActivity->status;
    switch ($status) {
      case self::DO_NOT_DISTURB:
        $status=$do_not_disturb;
        break;
      case self::MAKE_UP_ROOM:
        $status=$make_up_room;
        break;
      default:
        $status=$normal;
        break;
    }

    return response()->json($status,200);
  }
}