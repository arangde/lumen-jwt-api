<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Refer extends Model
{
    protected $table = 'refers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'member_id', 'refer_id',
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

    public function referer()
    {
        return $this->hasOne('App\Member', 'id', 'refer_id');
    }
}
