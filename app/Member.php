<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use GenTux\Jwt\JwtPayloadInterface;

class Member extends Model implements AuthenticatableContract, AuthorizableContract, JwtPayloadInterface
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function getPayload()
    {
        return [
            'sub' => $this->id,
            'exp' => time() + 7200,
            'context' => [
                'email' => $this->email,
            ]
        ];
    }

    public function incomes()
    {
        return $this->hasMany('App\Income');
    }

    public function points()
    {
        return $this->hasMany('App\Point');
    }

    public function withdrawals()
    {
        return $this->hasMany('App\Withdrawal');
    }

    public function sales()
    {
        return $this->hasMany('App\Sale');
    }

    public function refers()
    {
        return $this->hasMany('App\Refer', 'refer_id');
    }
}
