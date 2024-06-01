<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Helpers\MyLib;
use App\Exceptions\MyException;

use App\Model\Room;
use App\Http\Resources\RoomActivityResource;

use App\Exports\MyReport;
use Excel;
use DB;

class DashboardController extends Controller
{
  
  public function index(Request $request)
  {
    $this->admin = MyLib::admin();
    
    $new_query = DB::table("rooms");
    $new_query->leftJoin(DB::raw("
    (
      select ra.room_id, ra.status, ra.created_at from room_activities as ra, 
      (
        select distinct room_id as room_id,max(created_at) as created_at from room_activities group by room_id 
      ) as ra_filter 
      where ra.room_id = ra_filter.room_id and ra.created_at = ra_filter.created_at 
    ) as ra_final"),function($join){
      $join->on("rooms.id","=","ra_final.room_id");
      // $join->on("air_limbah_sensors.id","=","alfm_als_id");
    });
    $new_query->select('rooms.id as id','rooms.number as number','rooms.custome_number as custome_number','rooms.group as group','ra_final.room_id as room_id','ra_final.status as status','ra_final.created_at as created_at');
    $new_query=$new_query->orderBy("id");
    $new_query=$new_query->get();
    // return response()->json($new_query->toSql() ,200);

    $r_data =[];

    foreach ($new_query as $key => $n_q) {
      array_push($r_data,[
        "id"=>$n_q->id,
        "number"=>$n_q->number,
        "custome_number"=>$n_q->custome_number,
        "group"=>$n_q->group,
        "room_id"=>$n_q->room_id,
        "status"=>$n_q->status ?? '',
        "created_at"=>$n_q->created_at,
      ]);
    }
    
    return response()->json($r_data ,200);
  }


  public function getDataByRoom(Request $request,$download=false)
  {
    $this->admin = MyLib::admin();

    $rules = [
      'id' => 'required|exists:\App\Model\Room,id',
      // 'period' => 'required|in:Minutes,Hourly,Daily,Weekly,Monthly',
      'date_from'=>"required|date_format:Y-m-d H:i:s",
      'date_to'=>"required|date_format:Y-m-d H:i:s|after:date_from",
    ];

    $messages=[
      'id.required' => 'ID is required',
      'id.exists' => 'ID not listed',

      // 'period.required' => 'Period is required',
      // 'period.exists' => 'Please Selected the Period',

      'date_from.required' => 'Date From is required',
      'date_from.date_format' => 'Please Select Date From',
      
      'date_to.required' => 'Date To is required',
      'date_to.date_format' => 'Please Select Date To',
      'date_to.after'=>'Date To must after Date From',

    ];

    $validator = \Validator::make($request->all(),$rules,$messages);

    if ($validator->fails()) {
      throw new ValidationException($validator);
    }

    if($download){
      if(!isset($request->_TimeZoneOffset)){
        throw new MyException(
        [ 
          "message"=>"Please Refresh Your Page, TimeZoneOffset Required",
        ]
        ,400);

      }     
      $timeZoneOffset = $request->_TimeZoneOffset;
    }

    //======================================================================================================
    // Pembatasan Data hanya memerlukan limit dan offset
    //======================================================================================================

    $limit = 30; // Limit +> Much Data
    if (isset($request->limit)) {
      if ($request->limit <= 250) {
        $limit = $request->limit;
      }else {
        throw new MyException("Max Limit 250");
      }
    }

    $offset = isset($request->offset) ? (int) $request->offset : 0; // example offset 400 start from 401

    //======================================================================================================
    // Jika Halaman Ditentutkan maka $offset akan disesuaikan
    //======================================================================================================
    if (isset($request->page)) {
      $page =  (int) $request->page;
      $offset = ($page*$limit)-$limit;
    }

    $id = $request->id;

    $date_from = $request->date_from;
    $date_to = $request->date_to;

    $diffMillis = 3600000;
    $d_from = MyLib::utcMillis(date("Y-m-d H",MyLib::manualMillis($date_from) / 1000).":00:00");
    $d_to = MyLib::utcMillis(date("Y-m-d H",MyLib::manualMillis($date_to) / 1000).":00:00");

    $d_from_ori = $d_from;
    $d_to_ori = $d_to;

    if(gettype($d_from_ori) != "string" ) $d_from_ori = date("Y-m-d H:i:s", $d_from_ori / 1000);
    if(gettype($d_to_ori) != "string" ) $d_to_ori = date("Y-m-d H:i:s", $d_to_ori / 1000);

    $d_to += $diffMillis;

    if($d_from >= $d_to){
      throw new MyException([
        "message"=>"Date Not Right Please Check Again"
      ]);
    }


     //======================================================================================================
    // Init Model
    //======================================================================================================
    $model_query = \App\Model\RoomActivity::where("room_id",$id)->whereBetween("created_at",[$d_from,$d_to]);
    if (!$download) {
      $model_query = $model_query->offset($offset)->limit($limit);
    }

    $model_query=$model_query->with('room')->orderBy('created_at','desc')->get();

    return response()->json([
      "data"=>RoomActivityResource::collection($model_query),
    ],200);
  }
}