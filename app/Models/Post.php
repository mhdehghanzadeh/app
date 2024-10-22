<?php

namespace App\Models;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Morilog\Jalali\Jalalian;

class Post extends Model
{
    use HasFactory;

    public static function get($filters)
    {
       
        $client = new Client(['http_errors' => false]);
        $response = $client->request('GET', env('BASE_URL') . '/wp-json/wp/v2/posts?_embed&&per_page=10&page=' . 
        ($filters['page'] ? $filters['page'] : 1) . 
        ($filters['search'] ? ('&search='.$filters['search']) : '') . 
        (str_contains($filters['status'], 'publish') ? '&status[]=publish' : '') .
        (str_contains($filters['status'], 'draft') ? '&status[]=draft' : '') .
        (str_contains($filters['status'], 'trash') ? '&status[]=trash' : '') 
         , [
            'headers' => [
                'Authorization' => 'Basic ' . env('WORDPRES_TOKEN'),
            ],
        ]);
        $posts = collect(json_decode($response->getBody()));
        $pages = $response->getHeader('X-WP-TotalPages')[0];
        $posts->transform(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title->rendered,
                'link' => $post->link,
                'status' => $post->status,
                'categories' => get_object_vars($post->_embedded)['wp:term'][0],
                'created_at' => $post->date ? Jalalian::forge($post->date)->format('H:i Y/m/d') : null
            ];
        });
        $links = [];
        for ($i = 1; $i <= $pages; $i++) {
            array_push($links, array('url'=> '/posts?page='.$i, 'label'=> $i, 'active'=> $filters['page'] == $i ? true : false));
        }
        array_unshift($links, array('url'=> $filters['page'] > 1 ? ("/posts?page=" . $filters['page'] - 1) : null, 'label'=> '<div class="flex place-items-center"><i class="ri-arrow-right-line ml-1"></i> قبلی </div>', 'active' => $filters['page'] > 1 ? true : false));
        array_push($links, array('url'=> $filters['page'] < $pages ? ("/posts?page=" . $filters['page'] + 1) : null, 'label'=> '<div class="flex place-items-center"> بعدی <i class="ri-arrow-left-line mr-1"></i></div>', 'active'=> $filters['page'] < $pages ? true : false));
        $posts = [
            'data' => $posts,
            'links' => $links
        ];
        return $posts;
    }

    public static function store($data)
    {
        $client = new Client(['http_errors' => false]);
        $response = $client->request('POST', env('BASE_URL') . '/wp-json/wp/v2/posts' , [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . env('WORDPRES_TOKEN'),
            ],
            'json' => $data,
        ]);
        $result = json_decode($response->getBody());
        if($result->id){
            return true;
        }
        return false;
    }

    public static function edit($id)
    {
        $client = new Client(['http_errors' => false]);
        $response = $client->request('POST', env('BASE_URL') . '/wp-json/wp/v2/posts/' . $id . '?_embed' , [
            'headers' => [
                'Authorization' => 'Basic ' . env('WORDPRES_TOKEN'),
            ],
        ]);
        $post = json_decode($response->getBody());
        //dd($post);
        $post = [
            'id' => $post->id,
            'title' => $post->title->rendered,
            'content' => $post->content->rendered,
            'status' => $post->status,
            'categories' => $post->categories,
            'tags' => $post->tags,
            'featured_media' => get_object_vars($post->_embedded)['wp:featuredmedia'][0]->source_url,
            'selectedTags' => get_object_vars($post->_embedded)['wp:term'][1]
        ];
        return $post;
    }


    public static function updates($id, $data)
    {
        $client = new Client(['http_errors' => false]);
        $response = $client->request('POST', env('BASE_URL') . '/wp-json/wp/v2/posts/' . $id , [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . env('WORDPRES_TOKEN'),
            ],
            'json' => $data,
        ]);
        $result = json_decode($response->getBody());
        if ($response->getStatusCode() == 200) {
            return true;
        } else {
            return false;
        }
    }


    public static function destroy($id)
    {
        $client = new Client(['http_errors' => false]);
        $response = $client->request('delete', env('BASE_URL') . '/wp-json/wp/v2/posts/' . $id , [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . env('WORDPRES_TOKEN'),
            ],
        ]);
        $result = json_decode($response->getBody());
        if ($response->getStatusCode() == 200) {
            return true;
        } else {
            return false;
        }
    }


    public static function restore($id)
    {
        $data = [
            'status' => 'publish'
        ];
        $client = new Client(['http_errors' => false]);
        $response = $client->request('PUT', env('BASE_URL') . '/wp-json/wp/v2/posts/' . $id , [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . env('WORDPRES_TOKEN'),
            ],
            'json' => $data,
        ]);
        $result = json_decode($response->getBody());
        if ($response->getStatusCode() == 200) {
            return true;
        } else {
            return false;
        }
    }


    public static function categories()
    {
        $client = new Client(['http_errors' => false]);
        $response = $client->request('GET', env('BASE_URL') . '/wp-json/wp/v2/categories?per_page=100' , [
            'headers' => [
                'Authorization' => 'Basic ' . env('WORDPRES_TOKEN'),
            ],
        ]);
        $categories = collect(json_decode($response->getBody()));
        $categories->transform(function ($category) {
            return [
                'id' => $category->id,
                'label' => $category->name,
                'parent' => $category->parent,
            ];
        });
        return $categories;
    }


    public static function tags()
    {
        $client = new Client(['http_errors' => false]);
        $response = $client->request('GET', env('BASE_URL') . '/wp-json/wp/v2/tags' , [
            'headers' => [
                'Authorization' => 'Basic ' . env('WORDPRES_TOKEN'),
            ],
        ]);
        $tags = collect(json_decode($response->getBody()));
        $tags->transform(function ($tag) {
            return [
                'value' => $tag->id,
                'name' => $tag->name,
            ];
        });
        return $tags;
    }

    public static function store_category($data)
    {
        $client = new Client(['http_errors' => false]);
        $response = $client->request('POST', env('BASE_URL') . '/wp-json/wp/v2/categories' , [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . env('WORDPRES_TOKEN'),
            ],
            'json' => $data,
        ]);
        $result = json_decode($response->getBody());
        if($result->id){
            return true;
        }
        return false;
    }

    public static function store_tag($data)
    {
        $client = new Client(['http_errors' => false]);
        $response = $client->request('POST', env('BASE_URL') . '/wp-json/wp/v2/tags' , [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . env('WORDPRES_TOKEN'),
            ],
            'json' => $data,
        ]);
        $result = json_decode($response->getBody());
        if($result->id){
            return $result;
        }
        return false;
    }

    public static function search_tag($q)
    {
        $client = new Client(['http_errors' => false]);
        $response = $client->request('GET', env('BASE_URL') . '/wp-json/wp/v2/tags?search=' . $q , [
            'headers' => [
                'Authorization' => 'Basic ' . env('WORDPRES_TOKEN'),
            ],
        ]);
        $result = json_decode($response->getBody());
        return $result;
    }

    public static function store_media($image)
    {
        $client = new Client(['http_errors' => false]);
        $response = $client->request('POST', env('BASE_URL') . '/wp-json/wp/v2/media' , [
            'headers' => [
                'Authorization' => 'Basic ' . env('WORDPRES_TOKEN'),
            ],
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($image->getPathname(), 'r'),
                    'filename' => $image->getClientOriginalName(),
                ],
            ],
        ]);
        return $response->getBody();
    }


}
