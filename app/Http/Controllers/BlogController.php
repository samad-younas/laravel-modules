<?php

namespace App\Http\Controllers\Admin;
use Cviebrock\EloquentSluggable\Services\SlugService;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Store;
use App\Mail\MailUserApprovedBlog;
use App\Models\Category;
use App\Models\Subcriber;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Notification;
use App\Notifications\vendorNotification;
use Validator;
use File;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class BlogController extends Controller
{
    public function store(Request $request){
        $rules = [
            'title' => 'required',
            'description' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
            'category_id'=>'required'
        ];
        $validator = Validator::make($request->all(), $rules,[
            'title.required' => 'Title is required',
            'description.required' => 'Description is required',
            'image.required' => 'Image is required',
            'image.max' => 'Image must be size of 2048',
            'category_id.required'=>'Category is required'
        ]);


        if ($validator->fails()){
            return response()->json([
                'errors' => $validator->errors(),
                'success' => false,
            ], 200);
        }
        else
        {  $slug = SlugService::createSlug(Blog::class, 'slug', request('title'));
            $blog = new Blog();
            $blog->user_id = Auth::id();
            $blog->title = $request->title;
            $blog->slug = $slug;
            $blog->description = $request->description;
            $blog->category_id = $request->category_id;
            if(Auth::user()->type=='admin')
            {
                $blog->status = 'active';
            }
            else{
                $blog->status = 'inactive';
            }
            if($request->file('image'))
            {
                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $fileName = rand(1111100000, 1234599999) . '.' . $extension;
                $location = 'storage/blogs/';
                $file->move($location,$fileName);
                $blog->image = $fileName;
            }
            $blog->save();
            return response()->json([
                'success' => true,
            ]);
        }
    }
    public function edit($id){
        return Category::indOrFail($id);
    }
    public function update(Request $request){
        $rules = [
            'title' => 'required',
            'description' => 'required',
            'category_id'=>'required'
        ];
        $validator = Validator::make($request->all(), $rules,[
            'title.required' => 'Title is required',
            'description.required' => 'Description is required',
            'category_id.required'=>'Category is required'
        ]);
        if ($validator->fails()){
            return response()->json([
                'errors' => $validator->errors(),
                'success' => false,
            ], 200);
        }
        else
        {   $slug = SlugService::createSlug(Blog::class, 'slug', request('title'));
            $blog = Blog::find($request->id);
            $blog->title = $request->title;
            $blog->slug = $slug;
            $blog->description = $request->description;
            $blog->category_id = $request->category_id;
            $blog->user_id = Auth::id();
            if(Auth::user()->type=='vendor')
            {
                $blog->status = 'inactive';
            }
            else{
                $blog->status = 'active';

            }
            if($request->file('image'))
            {
                File::delete(public_path('/storage/blogs/').$blog->image);

                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $fileName = rand(111110000, 1234599999) . '.' . $extension;
                $location = 'storage/blogs/';
                $file->move($location,$fileName);
                $blog->image = $fileName;
            }
            $blog->update();
            return response()->json([
                'success' => true,
            ]);
        }
    }
    public function list(){
        if(Auth::user()->type=='admin')
        {
            return Blog::orderBy('created_at', 'DESC')->with('Category')->paginate(10);
        }
        else{
            return Blog::orderBy('created_at', 'DESC')->where('user_id', Auth::id())->with('Category')->paginate(10);
        }
        // if (auth()->user()->type == 'superadmin' || auth()->user()->type == 'admin') {
        //     return Blog::orderBy('created_at', 'DESC')->with('Category')->paginate(10);
        // } else {
        //     return Blog::orderBy('created_at', 'DESC')->where(['posted_by' => 'vendor', 'user_id' => Auth::user()->id])->with('Category')->paginate(10);
        // }
    }
    public function destroy($id){
        if($blog = Blog::find($id)){
            File::delete(public_path('/storage/blogs/').$blog->image);
            $blog->delete();
            return response()->json([
                'success' => true,
            ]);
        }else{
            return response()->json([
                'success' => false,
            ]);
        }
    }
    // public function sendNotification($id)
    // {
    //     $subcribers = Subcriber::all();

    //     foreach ($subcribers as $subcriber) {
    //         $subcriber->blog_id=$id;
    //         $subcriber->notify(new vendorNotification($subcriber));
    //     }
    // }

    public function active_inactive($id)
    {
        if ($blog = Blog::where('id',$id)->first()) {

            if ($blog->status == 'active') {
                $blog->status = 'inactive';
            }
            else{
                $blog->status = 'active';
            }

            $blog->save();
            return response()->json([
                'success' => true,
                'status'=>$blog->status
            ]);

        } else {
            return response()->json([
                'success' => false,
            ]);
        }
    }
}
