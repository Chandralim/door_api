<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Helpers\MyLib;
use App\Exceptions\MyException;

use App\Model\Room;
use App\Http\Resources\RoomResource;
use App\Http\Requests\RoomRequest;

use App\Exports\MyReport;
use Excel;
use Str;

class RoomController extends Controller
{
  public $admin="";
  public function __construct(Request $request)
  {
    $this->admin = MyLib::admin();
    if ($this->admin->role!=="Super_Admin" ) {
      throw new MyException(["message"=>"Maaf Anda Tidak Punya Otoritas"],400);
    }
  }

  public function index(Request $request,$download=false)
  {

    //======================================================================================================
    // Pembatasan Data hanya memerlukan limit dan offset
    //======================================================================================================

    $limit = 250; // Limit +> Much Data
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


    //======================================================================================================
    // Init Model
    //======================================================================================================
    $model_query = new \App\Model\Room();
    if (!$download) {
      $model_query = $model_query->offset($offset)->limit($limit);
    }

    //======================================================================================================
    // Model Sorting | Example $request->sort = "username:desc,role:desc";
    //======================================================================================================

    if ($request->sort) {
      $sort_lists=[];

      $sorts=explode(",",$request->sort);
      foreach ($sorts as $key => $sort) {
        $side = explode(":",$sort);
        $side[1]=isset($side[1])?$side[1]:'ASC';
        $sort_lists[$side[0]]=$side[1];
      }

      

      if (isset($sort_lists["id"])) {
        $model_query = $model_query->orderBy("id",$sort_lists["id"]);
      }

      // if (isset($sort_lists["title"])) {
      //   $model_query = $model_query->orderBy("title",$sort_lists["title"]);
      // }

      if (isset($sort_lists["number"])) {
        $model_query = $model_query->orderBy("number",$sort_lists["number"]);
        $model_query = $model_query->orderBy("group","asc");
        $model_query = $model_query->orderBy("id","asc");
      }

      if (isset($sort_lists["custome_number"])) {
        $model_query = $model_query->orderBy("custome_number",$sort_lists["custome_number"]);
        $model_query = $model_query->orderBy("group","asc");
        $model_query = $model_query->orderBy("id","asc");
      }

      // if (isset($sort_lists["token"])) {
      //   $model_query = $model_query->orderBy("token",$sort_lists["token"]);
      // }

      // if (isset($sort_lists["location_name"])) {
      //   $model_query = $model_query->orderBy(function($q)use($sort_lists){
      //     $q->from("locations")
      //     ->select("name")
      //     ->whereColumn("id","air_bersih_sensors.location_id");
      //   },$sort_lists["location_name"]);
      // }

      // if (isset($sort_lists["location_is_tenant"])) {
      //   $model_query = $model_query->orderBy(function($q)use($sort_lists){
      //     $q->from("locations")
      //     ->select("is_tenant")
      //     ->whereColumn("id","air_bersih_sensors.location_id");
      //   },$sort_lists["location_is_tenant"]);
      // }

      // if (isset($sort_lists["location_grup"])) {
      //   $model_query = $model_query->orderBy(function($q)use($sort_lists){
      //     $q->from("locations")
      //     ->select("grup")
      //     ->whereColumn("id","air_bersih_sensors.location_id");
      //   },$sort_lists["location_grup"]);
      // }
      
      // if (isset($sort_lists["created_at"])) {
      //   $model_query = $model_query->orderBy("created_at",$sort_lists["created_at"]);
      // }

      // if (isset($sort_lists["updated_at"])) {
      //   $model_query = $model_query->orderBy("updated_at",$sort_lists["updated_at"]);
      // }

      //
      // if (isset($sort_lists["role"])) {
      //   $model_query = $model_query->orderBy(function($q){
      //     $q->from("roles")
      //     ->select("title")
      //     ->whereColumn("id","users.role_id");
      //   },$sort_lists["role"]);
      // }

      // if (isset($sort_lists["admin"])) {
      //   $model_query = $model_query->orderBy(function($q){
      //     $q->from("users as u")
      //     ->select("u.username")
      //     ->whereColumn("u.id","users.id");
      //   },$sort_lists["admin"]);
      // }
    }else {
      $model_query = $model_query->orderBy('created_at','ASC');
    }
    //======================================================================================================
    // Model Filter | Example $request->like = "username:%username,role:%role%,name:role%,";
    //======================================================================================================

    if ($request->like) {
      $like_lists=[];

      $likes=explode(",",$request->like);
      foreach ($likes as $key => $like) {
        $side = explode(":",$like);
        $side[1]=isset($side[1])?$side[1]:'';
        $like_lists[$side[0]]=$side[1];
      }

      // if (isset($like_lists["id"])) {
      //   $model_query = $model_query->orWhere("id","ilike",$like_lists["id"]);
      // }

      // if (isset($like_lists["token"])) {
      //   $model_query = $model_query->orWhere("token","ilike",$like_lists["token"]);
      // }

      // if (isset($like_lists["location_name"])) {
      //   $model_query = $model_query->orWhereIn("location_id",function($q)use($like_lists){
      //     $q->from("locations")
      //     ->select("location_id")
      //     ->where("name",'ilike',$like_lists["location_name"]);
      //   });
      // }

      // if (isset($like_lists["name"])) {
      //   $model_query = $model_query->orWhere("name","ilike",$like_lists["name"]);
      // }
    }

    // ==============
    // Model Filter
    // ==============
    
    if (isset($request->id)) {
      $model_query = $model_query->where("id",'like','%'.$request->id.'%');
    }
    // if (isset($request->token)) {
    //   $model_query = $model_query->where("token",'like','%'.$request->token.'%');
    // }
    // if (isset($request->location_id)) {
    //   $model_query = $model_query->where("location_id",'like','%'.$request->location_id.'%');
    // }

    // if (isset($request->location_name)) {
    //   $model_query = $model_query->whereIn("location_id",function($q)use($request){
    //     $q->from("locations")
    //     ->select("id")
    //     ->where("name",'like','%'.$request->location_name.'%');
    //   });
    // }

    if (isset($request->number)) {
      $model_query = $model_query->where("number",'ilike',$request->number);
    }

    if (isset($request->custome_number)) {
      $model_query = $model_query->where("custome_number",'ilike',$request->custome_number);
    }

    if (isset($request->group)) {
      $model_query = $model_query->where("group",'ilike',$request->group);
    }

    // $toSql =$model_query->toSql();
    // $model_query=$model_query->with("location")->get();
    $model_query=$model_query->get();

    return response()->json([
      "data"=>RoomResource::collection($model_query),
      // "toSql"=>$toSql,
    ],200);
  }

  
  public function show(RoomRequest $request)
  {
    // $model_query = Room::with("location")->find($request->id);
    $model_query = Room::find($request->id);
    return response()->json([
      "data"=>new RoomResource($model_query),
    ],200);
  }

  public function store(RoomRequest $request)
  {
    // $model_query->token=Str::random(200).MyLib::getMillis().$this->admin->id;
    $this->admin = MyLib::admin();
    $model_query=new Room();
    $model_query->group=$request->group;
    $model_query->number=$request->number;
    $model_query->custome_number=$request->custome_number;
    $model_query->created_at=MyLib::getMillis();
    $model_query->updated_at=MyLib::getMillis();
    $model_query->admin_id=$this->admin->id;
    if ($model_query->save()) {
      return response()->json([
          "message"=>"Proses tambah data berhasil",
      ],200);
    }
    return response()->json([
        "message"=>"Proses tambah data gagal"
    ],400);
  }

  public function update(RoomRequest $request)
  {
    $model_query = Room::find($request->id);
    $model_query->group=$request->group;
    $model_query->number=$request->number;
    $model_query->custome_number=$request->custome_number;
    $model_query->updated_at=MyLib::getMillis();
    $model_query->admin_id=$this->admin->id;
    if ($model_query->save()) {
        return response()->json([
            "message"=>"Proses ubah data berhasil",
        ],200);
    }
    return response()->json([
        "message"=>"Proses ubah data gagal"
    ],400);
  }


    // public function download(Request $request)
    // {
    //   $this->admin = MyLib::admin();
    
    //   $data = json_decode(json_encode($this->index($request,true)),true)["original"]["data"];
    
    //   $date = new \DateTime();
    //   $filename=$date->format("YmdHis").'-Room_list';
    
    //   $mime=MyLib::mime("xlsx");
    //   $bs64=base64_encode(Excel::raw(new MyReport($data,'report.Room_list'), $mime["exportType"]));
    
    //   $result =[
    //     "contentType"=>$mime["contentType"],
    //     "data"=>$bs64,
    //     "dataBase64"=>$mime["dataBase64"].$bs64,
    //     "filename"=>$filename
    //   ];
    //   return $result;
    //   // return $data;
    // }
}
