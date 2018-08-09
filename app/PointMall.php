<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PointMall extends Model
{
    protected $table = 'points_mall';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'member_id', 'item_name', 'item_point',
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
