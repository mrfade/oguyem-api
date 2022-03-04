<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuCommentVote extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'menu_comment_id', 'user_id', 'vote',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Get the comment.
     */
    public function comment() {
        return $this->belongsTo(MenuComment::class);
    }

    /**
     * Get the user.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include upvotes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeUpvotes($query) {
        $query->where('vote', 1);
    }

    /**
     * Scope a query to only include upvotes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeDownvotes($query ) {
        $query->where('vote', -1);
    }

}
