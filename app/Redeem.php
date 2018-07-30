<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Redeem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'member_id', 'point',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        
    ];

    public function member()
    {
        return $this->belongsTo('App\Member');
    }
}
