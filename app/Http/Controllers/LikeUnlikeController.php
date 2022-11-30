<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LikeUnlikeController extends Controller
{
    public function like($id)
    {
            $product = Product::find($id);
            $this->like_verification(Auth::id(), $product->id);
            return response()->json([
                'success' => true,
                'is_liked' => $product->liked(),
            ]);
    }

    public function like_verification($userId , $id)
    {

            $like = Product::where('id', $id)->first();
            $likeable=Like::where(['user_id'=>Auth::user()->id,'likeable_id'=>$id])->first();
            if ($likeable) {
                $likeable->delete();
                $like->decrement('like_count', 1);
            } else {
                $user_like=new Like();
                $user_like->user_id= Auth::user()->id;
                $user_like->likeable_id= $id;
                $user_like->save();
                if($like->like_count == 0){
                    $like->like_count=1;
                    $like->save();
                }else{
                    $like->increment('like_count', 1);
                }
            }
    }
}
