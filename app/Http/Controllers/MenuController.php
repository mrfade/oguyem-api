<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuComment;
use App\Models\MenuCommentImage;
use App\Models\MenuCommentVote;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuController extends Controller {

    private function mapMenu(array $menu) {
        $menu['food_list'] = json_decode($menu['food_list'], true);
        $menu['holiday_title'] = (string) $menu['holiday'] ?? '';
        $menu['holiday'] = (bool) $menu['holiday'];
        return $menu;
    }

    public function showMenu() {
        $days = [];
        $date = new \DateTime();
        // echo intval($date->format('N'));

        for ($i = intval($date->format('N')) - 1; $i >= 0; $i--) {
            $_date = clone $date;
            $_date->sub(new \DateInterval('P' . $i . 'D'));
            array_push($days, $_date->format('Y-m-d'));
        }

        for ($i = 1; $i <= 7 - intval($date->format('N')); $i++) {
            $_date = clone $date;
            $_date->add(new \DateInterval('P' . $i . 'D'));
            array_push($days, $_date->format('Y-m-d'));
        }

        $next_week = new \DateTime(end($days));
        // $next_week->add(new DateInterval('P1D'));

        for ($i = 1; $i <= 7 * 4; $i++) {
            $_date = clone $next_week;
            $_date->add(new \DateInterval('P' . $i . 'D'));
            array_push($days, $_date->format('Y-m-d'));
        }

        $menu = [];
        foreach ($days as $day) {
            $dayMenu = Menu::where('date', $day)->first();
            if ($dayMenu) {
                array_push($menu, $this->mapMenu($dayMenu->toArray()));
            } else {
                array_push($menu, [
                    'date' => $day,
                    'food_list' => [],
                    'weekend' => in_array((new \DateTime($day))->format('N'), ['6', '7']),
                    'holiday' => false,
                    'holiday_title' => '',
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'menu' => $menu,
        ]);
    }

    public function showDateMenu($date) {
        $menu = Menu::where('date', $date)->firstOrFail()->toArray();

        return response()->json([
            'status' => 'success',
            'menu' => $this->mapMenu($menu),
        ]);
    }

    public function showComments($date, Request $request) {
        $menu = Menu::where('date', $date)->firstOrFail();
        $comments = $menu->comments()->orderBy('created_at', 'desc')->get()->map(function ($comment) use ($request) {
            // $comment['user'] = $comment->user->toArray();

            $comment['upvotes'] = MenuCommentVote::where('menu_comment_id', $comment['id'])->upvotes()->count();
            $comment['downvotes'] = MenuCommentVote::where('menu_comment_id', $comment['id'])->downvotes()->count();

            $comment['images'] = $comment->images->map(function ($image) {
                $image['url'] = url(Storage::url('comment_images/' . $image['name'] . '.' . $image['ext']));
                return $image;
            })->toArray();

            $comment['user_vote'] = 0;
            $vote = MenuCommentVote::where('menu_comment_id', $comment['id'])->where('user_id', $request->user()->id)->first();
            if ($vote) {
                $comment['user_vote'] = $vote->vote;
            }

            return $comment;
        })->toArray();
        // $comments = $menu->comments()->orderBy('created_at', 'desc')->get()->toArray();

        // sleep(2);

        return response()->json([
            'status' => 'success',
            'comments' => $comments,
        ]);
    }

    public function showComment($date, $id, Request $request) {
        $menu = Menu::where('date', $date)->firstOrFail();
        $comment = MenuComment::where('id', $id)->where('menu_id', $menu->id)->firstOrFail();

        $comment['upvotes'] = MenuCommentVote::where('menu_comment_id', $comment->id)->upvotes()->count();
        $comment['downvotes'] = MenuCommentVote::where('menu_comment_id', $comment->id)->downvotes()->count();

        $comment['images'] = $comment->images->map(function ($image) {
            $image['url'] = url(Storage::url('comment_images/' . $image['name'] . '.' . $image['ext']));
            return $image;
        })->toArray();

        $comment['user_vote'] = 0;
        $vote = MenuCommentVote::where('menu_comment_id', $comment['id'])->where('user_id', $request->user()->id)->first();
        if ($vote) {
            $comment['user_vote'] = $vote->vote;
        }

        $comment = $comment->toArray();

        return response()->json([
            'status' => 'success',
            'comment' => $comment,
        ], 200);
    }

    public function newComment($date, Request $request) {
        $menu = Menu::where('date', $date)->firstOrFail();

        if ((new \DateTime())->format('Y-m-d') != $date) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only today is allowed to comment.',
            ], 400);
        }

        $path = '';
        $uuid = '';
        $ext = '';
        if ($request->hasFile('image')) {
            if ($request->file('image')->isValid()) {
                $uuid = Str::uuid()->toString();
                $ext = $request->image->extension();
                $path = $request->image->storeAs('public/comment_images', $uuid . '.' . $ext);
            }
        }

        $comment = MenuComment::create([
            'menu_id' => $menu->id,
            'user_id' => $request->user()->id,
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment'),
        ]);

        $comment = $comment->toArray();

        if ($path) {
            $image = MenuCommentImage::create([
                'menu_comment_id' => $comment['id'],
                'name' => $uuid,
                'ext' => $ext,
            ]);

            $comment['images'] = [
                $image->toArray(),
            ];
        }

        return response()->json($comment, 201);
    }

    public function deleteComment($date, $id, Request $request) {
        $comment = MenuComment::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $votes = MenuCommentVote::where('menu_comment_id', $comment->id);

        $images = MenuCommentImage::where('menu_comment_id', $comment->id);
        foreach ($images->get()->toArray() as $image) {
            Storage::delete('public/comment_images/' . $image['name'] . '.' . $image['ext']);
        }

        $votes->delete();
        $images->delete();
        $comment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Comment deleted.',
        ], 200);
    }

    public function voteComment($date, $id, Request $request) {
        $comment = MenuComment::where('id', $id)->firstOrFail();

        $vote_input = $request->input('vote');
        if ($vote_input == 'up') {
            $vote_ = 1;
        } else if ($vote_input == 'down') {
            $vote_ = -1;
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid vote.',
            ], 400);
        }

        $vote = MenuCommentVote::where('user_id', $request->user()->id)
            ->where('menu_comment_id', $comment->id)
            ->first();

        if ($vote) {
            $vote->vote = $vote_;
            $vote->save();
        } else {
            MenuCommentVote::create([
                'menu_comment_id' => $comment->id,
                'user_id' => $request->user()->id,
                'vote' => $vote_,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Vote saved.',
        ], 200);
    }

    // public function update($id, Request $request) {
    //     $author = Author::findOrFail($id);
    //     $author->update($request->all());

    //     return response()->json($author, 200);
    // }

    // public function delete($id) {
    //     Author::findOrFail($id)->delete();
    //     return response('Deleted Successfully', 200);
    // }
}
