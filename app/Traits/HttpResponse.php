<?php 

namespace App\Traits;

trait HttpResponse
{
    protected function success($date,$message=null,$code=200){
    return response()->json([
        'status'=>"Request was successful",
        'message'=>$message,
        'data'=>$date,
    ],$code);
  }
  protected function error($date,$message=null,$code=400){
      return response()->json([
          'status'=>"Request was not successful",
          'message'=>$message,
          'data'=>$date,
      ],$code);
  }
}

?>