<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnnouncementView extends Model
{
    protected $table = 'announcement_views';
    
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'member_id',
        'announcement_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        
    ];
}
