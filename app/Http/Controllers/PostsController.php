<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use App\Models\Post;

class PostsController extends Controller
{
    public function index()
    {
        return Inertia::render('Posts/Index', [
            'filters' => Request::all('search','status'),
            'posts' => Post::get(Request::all('page','search','status')),
            'page' => Request::get('page'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Posts/Create', [
            'categories' => Post::categories()
        ]);
    }

    public function store()
    {
        Request::validate([
            'title' => ['required'],
            'content' => ['required'],
            'status' => ['required'],
            'categories' => ['nullable'],
            'tags' => ['nullable'],
            'featured_media' => ['nullable']
        ]);
        if(Post::store(Request::all('title','content','status','categories','tags', 'featured_media'))){
            return Redirect::route('posts')->with('success', 'مطلب با موفقیت ثبت گردید.');
        }
        return Redirect::back()->with('error', 'خطا در ذخیره سازی');
    }

    public function edit($id)
    {
        $post = Post::edit($id);
        return Inertia::render('Posts/Edit',[
            'post' => $post,
            'categories' => Post::categories(),
            'tags' => Post::tags(),
        ]);
    }

    public function update($post)
    {
        Request::validate([
            'title' => ['required'],
            'content' => ['required'],
            'status' => ['required'],
            'categories' => ['nullable'],
            'tags' => ['nullable'],
            'featured_media' => ['nullable']
        ]);
        if(Post::updates($post, Request::all('title','content','status','categories', 'tags', 'featured_media'))){
            return Redirect::back()->with('success', 'مطلب با موفقیت ویرایش گردید');
        }
        return Redirect::back()->with('error', 'خطا در ویرایش اطلاعات');
    }

    public function destroy($post)
    {
        if(Post::destroy($post)){
            return Redirect::back()->with('success', 'مطلب با موفقیت حذف گردید');
        }
        return Redirect::back()->with('error', 'خطا در حذف مطلب');
    }

    public function restore($post)
    {
        if(Post::restore($post)){
            return Redirect::back()->with('success', 'مطلب با موفقیت بازیابی گردید');
        }
        return Redirect::back()->with('error', 'خطا در بازیابی مطلب');
    }

    public function store_category()
    {
        Request::validate([
            'name' => ['required'],
            'parent' => ['nullable'],
        ]);
        if(Post::store_category(Request::all('name', 'parent'))){
            return Redirect::back()->with('success', 'دسته بندی با موفقیت اضافه شد.');
        }
        return Redirect::back()->with('error', 'خطا در ذخیره سازی');
    }

    public function store_tag()
    {
        Request::validate([
            'name' => ['required'],
        ]);
        return Post::store_tag(Request::all('name'));
    }

    public function search_tag($q)
    {
        return Post::search_tag($q);
    }

    public function store_media()
    {
       // dd( $_FILES['image']);
        //dd(Request::file('image'));
        Request::validate([
            'file' => ['required', 'image'],
        ]);
        return Post::store_media(Request::file('file'));
    }
}
