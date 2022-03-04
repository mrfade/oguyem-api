<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuCommentImage extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'menu_comment_id', 'name', 'ext',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [ ];


    /**
     * Get the comment.
     */
    public function comment()
    {
        return $this->belongsTo(MenuComment::class);
    }

}
