<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Validator;
use File;

class CategoryController extends Controller
{
    public function store(Request $request){
        $rules = [
            'name' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ];
        $validator = Validator::make($request->all(), $rules,[
            'name.required' => 'Name is required.',
            'image.required' => 'Image is required',
            'image.max' => 'Image size must not be larger then 2048',
        ]);

        if ($validator->fails()){
            return response()->json([
                'errors' => $validator->errors(),
                'success' => false,
            ], 200);
        }
        else
        {   $slug = SlugService::createSlug(Category::class, 'slug', request('name'));
            $category = new Category();
            // Auth::id();
            $category->name = $request->name;
            $category->slug = $slug;
            if($request->file('image'))
            {
                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $fileName = rand(1111100000, 1234599999) . '.' . $extension;
                $location = 'storage/categories/';
                $file->move($location,$fileName);
                $category->image = $fileName;
            }
            $category->save();
            return response()->json([
                'success' => true,
            ]);
        }
    }
    public function edit($id){
        return Category::findOrFail($id);
    }
    public function update(Request $request){

        $rules = [
            'name' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules,[
            'name.required' => 'Name is required.',
        ]);
        if ($validator->fails()){
            return response()->json([
                'errors' => $validator->errors(),
                'success' => false,
            ], 200);
        }
        else
        {   $slug = SlugService::createSlug(Category::class, 'slug', request('name'));
            $category = Category::find($request->id);
            $category->name = $request->name;
            $category->slug = $slug;
            if($request->file('image'))
            {
                File::delete(public_path('/storage/categories/').$category->image);

                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $fileName = rand(1111100000, 1234599999) . '.' . $extension;
                $location = 'storage/categories/';
                $file->move($location,$fileName);
                $category->image = $fileName;
            }
            $category->update();
            return response()->json([
                'success' => true,
            ]);
        }
    }
    public function list(){
        return Category::orderBy('created_at', 'DESC')->paginate(10);
    }
    public function destroy($id){
        if($categories = Category::find($id)){
            File::delete(public_path('/storage/categories/').$categories->image);
            $categories->delete();
            return response()->json([
                'success' => true,
            ]);
        }else{
            return response()->json([
                'success' => false,
            ]);
        }
    }

}
