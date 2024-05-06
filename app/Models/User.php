<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function receivables(): HasManyThrough
    {
        //Receivable is the final model
        //Customer is the intemidate model
        //User is the first model
        // Customer contain forieng key of first model which is user 
        // Receivable has foreign key of second model which can connect to Customer and customer can connect to user
        return $this->hasManyThrough(Receivable::class, Customer::class);

    }
    public function suppliers():HasMany{
        return $this->hasMany(Supplier::class);
    }
    public function payables():HasManyThrough{
        return $this->hasManyThrough(Payable::class,Supplier::class);
    }
    public function roles(): HasMany
    {
        return $this->hasMany(UserandRole::class);
    }

    public function collector(): HasOne
    {
        return $this->hasOne(Collector::class);
    }
    /** 
     * 
     */
    public function assignedCustomers(): HasManyThrough
    {
        return $this->hasManyThrough(
            Customer::class, // Final Model: The type of model you want to ultimately get, in this case, Customer.
            CustomerAndCollector::class, // Intermediate Model: The model that acts as a link between User (collector) and Customer.
            'collector_id',   // Foreign Key in Intermediate Model: The column in the intermediate table that connects to the User (collector) table.
            'id',   // Local Key: The primary key of the current model (User), used to match with the intermediate table.
            'id',  // Foreign Key: The primary key of the intermediate model (CustomerAndCollector), used to match with the final model (Customer).
            'customer_id'  // Foreign Key in Intermediate Model: The column in the intermediate table that connects to the Customer table.
        );
    }

    public function subscription ():HasOne{
        return $this->hasOne(Subscription::class);
    }
    // public function assignedCustomers()
    // {
    //     return $this->belongsToMany(Customer::class, 'collector_customer', 'collector_id', 'customer_id');
    // }
}
