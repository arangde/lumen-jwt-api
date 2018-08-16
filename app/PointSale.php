<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PointSale extends Model
{
    protected $table = 'pointsales';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'member_id', 'item_id', 'point'
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

    public function item()
    {
        return $this->belongsTo('App\Item');
    }
}
