<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAndCollector extends Model
{
    use HasFactory;
    protected $table="collector_customer";
    protected $fillable=["customer_id","collector_id"];

    public function collector():BelongsTo{
        return $this->belongsTo(Collector::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
