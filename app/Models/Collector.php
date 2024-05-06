<?php

namespace App\Models;

use App\Http\Controllers\Merchance\CustomerController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Collector extends Model
{
    use HasFactory;
    protected $table="collectors";
    protected $fillable=["collector_id","user_id"];

    public function user():BelongsTo{
        return $this->belongsTo(User::class);
    }

    public function collectorCustomers(): HasMany
    {
        return $this->hasMany(CustomerAndCollector::class);
    }

    public function customers(): HasManyThrough //not yet finalize
    {
        return $this->hasManyThrough(Customer::class, User::class);
    }
}
