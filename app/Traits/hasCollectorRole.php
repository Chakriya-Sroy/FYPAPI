<?php

namespace App\Traits;
use App\Models\UserandRole;
use Illuminate\Support\Facades\Auth;

  trait HasCollectorRole{
    protected function hasCollectorRole(){
        $hasCollectorRole = UserandRole::where('user_id', Auth::user()->id)->get();
        $isCollector = false;
        $collectorId = null;
        foreach ($hasCollectorRole as $role) {
            if ($role->role_id == 2) {
                $isCollector = true;
                $collectorId = $role->user_id;
                break;
            }
        }
        return [
            'isCollector' => $isCollector,
            'collectorId' => $collectorId
        ];
      }
  }

?>