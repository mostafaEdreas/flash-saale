<?php

namespace App\Http\Controllers;

abstract class Controller
{
   protected function response(array|object $data =[] ,array $paginatoin =[],string $message = '',bool $success=true ,int $status = 200 ,array $headers = [])
    {
        $readyData = [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            ...$addtinal
            
            
        ];
        return response()->json($data, $status ,$headers);
    }

   
}
