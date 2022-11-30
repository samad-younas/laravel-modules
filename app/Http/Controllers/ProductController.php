<?php

namespace App\Http\Controllers;

use App\Jobs\TestSendEmail;
use App\Models\Product;
use App\Models\Subcriber;
use Cviebrock\EloquentSluggable\Services\SlugService;
use App\Models\ProductManufacturer;
use App\Models\ProductCategory;
use App\Models\ProductSeries;
use App\Models\Rating;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Validator;
use File;
use Notification;
use App\Notifications\vendorNotification;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function store(Request $request)
    {
       $request->request->add(['categories' => json_decode($request->categories,true),]);
       $request->request->add(['multiple_images' => json_decode($request->multiple_images,true),]);

        $rules = [
            'title' => 'required',
            'description' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
            "categories"    =>   'required|present|array',
            'materials'=>'required',
            'jan_code'=>'required',
            'item_code'=>'required',
        ];

        $validator = Validator::make($request->all(), $rules,[
            'title.required' => 'Title is required.',
            'description.required' => 'Description is required.',
            'image.required' => 'image is required.',
            'categories.required' => 'Categories are required',
            'materials.required' => 'Materials are required.',
            'jan_code.required' => 'jan code is required.',
            'item_code.required' => 'item code is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'success' => false,
            ], 200);
        } else {
            $slug = SlugService::createSlug(Product::class, 'slug', request('title'));
            $product = new Product();
            $product->user_id = Auth::id();
            $product->title = $request->title;
            $product->publisher = $request->publisher;
            $product->materials = $request->materials;
            $product->jan_code = $request->jan_code;
            $product->item_code = $request->item_code;
            $product->slug = $slug;
            $product->description = $request->description;
            $product->status = 'active';
            if ($request->image) {
                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $fileName = rand(1111100000, 1234599999) . '.' . $extension;
                $location = 'storage/products/';
                $file->move($location, $fileName);
                $product->image = $fileName;
            }
            $data = [];
            if ($request->multiple_images) {
                $product->multiple_images = implode(',', $request->multiple_images);
            }
            $product->save();
            // if($product->posted_by=='superadmin')
            // {
            //     $mail_data = [
            //         'product_name' => $product->title
            //     ];

            //     $job = (new TestSendEmail($mail_data))
            //             ->delay(now()->addSeconds(2));
            //     dispatch($job);
            // }
            // $this->sendNotification($product->id,$product->store_id);
            // foreach(json_decode($request->categories) as $category) {
            //     $categoryProduct = new ProductCategory();
            //     $categoryProduct->category_id = $category->id;
            //     $categoryProduct->product_id = $product->id;
            //     $categoryProduct->save();
            // }
            // foreach(json_decode($request->manufacturer) as $manufacture) {
            //     $manufactureProduct = new ProductManufacturer();
            //     $manufactureProduct->manufacturer_id = $manufacture->id;
            //     $manufactureProduct->product_id = $product->id;
            //     $manufactureProduct->save();
            // }
            // foreach(json_decode($request->series) as $series) {
            //     $seriesProduct = new ProductSeries();
            //     $seriesProduct->series_id = $series->id;
            //     $seriesProduct->product_id = $product->id;
            //     $seriesProduct->save();
            // }
            $product->categories()->attach($request->categories);
            return response()->json([
                'success' => true,
            ]);
        }
    }
    public function update(Request $request)
    {
        $request->request->add(['categories' => json_decode($request->categories,true),]);
         $rules = [
             'title' => 'required',
             'description' => 'required',
             "categories"    =>   'required|present|array',
             'materials'=>'required',
             'jan_code'=>'required',
             'item_code'=>'required',
         ];

         $validator = Validator::make($request->all(), $rules,[
             'title.required' => 'Title is required.',
             'description.required' => 'Description is required.',
             'categories.required' => 'Categories are required',
             'materials.required' => 'Materials are required.',
             'jan_code.required' => 'jan code is required.',
             'item_code.required' => 'item code is required.',
         ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'success' => false,
            ], 200);
        } else {
            $slug = SlugService::createSlug(Product::class, 'slug', request('title'));
            $product = Product::find($request->id);
            $product->user_id = Auth::id();
            $product->title = $request->title;
            $product->materials = $request->materials;
            $product->jan_code = $request->jan_code;
            $product->item_code = $request->item_code;
            $product->slug = $slug;
            $product->description = $request->description;
            $product->status = 'active';
            if ($request->image) {
                File::delete(public_path('/storage/products/') . $product->image);
                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $fileName = rand(1111100000, 1234599999) . '.' . $extension;
                $location = 'storage/products/';
                $file->move($location, $fileName);
                $product->image = $fileName;
            }
            if (json_decode($request->multiple_images)) {
                foreach (explode(" ", $product->multiple_images) as $image) {
                    File::delete(public_path('/storage/products/') . $image);
                }
                $product->multiple_images = implode(',', json_decode($request->multiple_images));
            }

            $product->save();
            $product->categories()->sync($request->category);
            return response()->json([
                'success' => true,
            ]);
        }
    }
    public function list()
    {
        return Product::orderBy('created_at', 'DESC')->with('categories')->paginate(10);

    }
    public function edit($id)
    {
        return Product::with('categories')->findOrFail($id);
    }
    public function destroy($id)
    {

        if ($product = Product::findOrFail($id)) {
            if (isset($product->multiple_images)) {
                $images = explode(",", $product->multiple_images);
                foreach ($images as $multiImage) {
                    File::delete(public_path('/storage/products/') . $multiImage);
                }
            }

            File::delete(public_path('/storage/products/') . $product->image);
            $product->categories()->detach();
            $product->manufacturers()->detach();
            $product->series()->detach();
            $product->delete();
            return response()->json([
                'success' => true,
            ]);
        } else {
            return response()->json([
                'success' => false,
            ]);
        }
    }
    public function active_inactive($id)
    {
        if ($product = Product::where('id',$id)->first()) {

            if ($product->status == 'active') {
                $product->status = 'inactive';
            }
            else{
                $product->status = 'active';
            }

            $product->save();
            return response()->json([
                'success' => true,
                'status'=>$product->status
            ]);

        } else {
            return response()->json([
                'success' => false,
            ]);
        }
    }
    // public function sendNotification($id,$store)
    // {
    //     $subcribers = Subcriber::where('store_id',$store)->get();

    //     $details = [
    //         'greeting' => 'Hi Artisan',
    //         'body' => 'This is my first notification from ItSolutionStuff.com',
    //         'thanks' => 'Thank you for using ItSolutionStuff.com tuto!',
    //         'actionText' => 'View My Site',
    //         'actionURL' => url('/'),
    //         'order_id' => 101
    //     ];
    //     foreach ($subcribers as $subcriber) {
    //         $subcriber->product_id = $id;
    //         $subcriber->store_id= $store;
    //         $subcriber->notify(new vendorNotification($subcriber));
    //     }
    // }
}
