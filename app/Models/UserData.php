<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;

class UserData extends Model implements AuthenticatableContract
{
    use HasFactory, HasApiTokens, Authenticatable;

    protected $table = 'user_data';

    // Allow mass assignment for these fields
    protected $fillable = ['email_id', 'password'];

    public function preferences()
    {
        return $this->hasOne(Preference::class, 'user_id');
    }
}
