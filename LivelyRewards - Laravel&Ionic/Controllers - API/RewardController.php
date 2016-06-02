<?php

namespace App\Http\Controllers\ApiControllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Reward;

class RewardController extends Controller
{

  function store(Request $request, Response $response)
  {
    $data = $request->except('id','created_at','updated_at');
    //if(! array_key_exists())
    // Do some checking for errors and sanitization
    $reward = new Reward();
    foreach ($data as $key => $value){
      $reward->$key = $value;
    }
    $success = $reward->save();
    if($success){
      $data = array('data'=>$reward->toArray());
      return $response->header('Status',201)->setContent(json_encode($data));
    }
    $error = array('error'=>array('code'=>'GEN-BADREQUEST','message'=>'Bad Request','payload'=>$request->input('data')));
    return $response->header('Status',400)->setContent(json_encode($error));
  }


  function show($id, Response $response)
  {
    $reward = Reward::find($id);
    if($reward != ""){
      $data = array('data'=>$reward->toArray());
      if ( $reward->image ){
        $data['data']['image_url'] = $reward->image->url;
      }
      return json_encode($data);
    }
    $error = array('error'=>array('code'=>'GEN-PIDINVAL','http_code'=>'404','message'=>'Invalid ID','payload'=>$id));
    return json_encode($error);
  }


  function edit($id, Request $request, Response $response)
  {
    $data = $request->except('id','updated_at','created_at');
    $reward = Reward::find($id);
    if($reward != ''){
      foreach ($data as $key => $value){
        $reward->$key = $value;
      }
      $success = $reward->save();
      if($success){
        $data = array('data'=>$reward->toArray());
        return $response->header('Status',201)->setContent(json_encode($data));
      }
      $error = array('error'=>array('code'=>'GEN-BADREQEUST','message'=>'Bad Request','payload'=>$request->input('data')));
      return $response->header('Status',400)->setContent(json_encode($error));
    }
    $error = array('error'=>array('code'=>'GEN-INVALIDID','message'=>'Invalid  ID','payload'=>$id));
    return $response->header('Status',404)->setContent(json_encode($error));
  }


  function update($id, Request $request, Response $response)
  {
    $data = $request->except('id','created_at','updated_at','image_url');
    // Do some checking for errors and sanitization
    $reward = Reward::find($id);
    if ( $reward ){
      foreach ($data as $key => $value){
        $reward->$key = $value;
      }
      $success = $reward->save();
      if($success){
        $data = array('data'=>$reward->toArray());
        return $response->header('Status',201)->setContent(json_encode($data));
      }
      $error = array('error'=>array('code'=>'GEN-BADREQUEST','message'=>'Bad Request','payload'=>$request->input('data')));
      return $response->header('Status',400)->setContent(json_encode($error));
    }
    $error = array('error'=>array('code'=>'GEN-INVALIDID','message'=>'Invalid  ID','payload'=>$id));
    return $response->header('Status',404)->setContent(json_encode($error));
  }
}
