<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date', 'food_list', 'weekend', 'holiday', 'holiday_title',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id', 'created_at', 'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'weekend' => 'boolean',
        'holiday' => 'boolean',
        'holiday_title' => 'string',
    ];

    /**
     * Get the comments for the menu.
     */
    public function comments()
    {
        return $this->hasMany(MenuComment::class);
    }
}
