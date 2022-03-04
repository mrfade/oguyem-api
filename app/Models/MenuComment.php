<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuComment extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'menu_id', 'user_id', 'rating', 'comment',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Get the user.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the images.
     */
    public function images() {
        return $this->hasMany(MenuCommentImage::class);
    }

    /**
     * Get the votes for the comment.
     */
    public function votes() {
        return $this->hasMany(MenuCommentVote::class);
    }

}
