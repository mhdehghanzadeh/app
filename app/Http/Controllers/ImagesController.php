<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use League\Glide\Responses\LaravelResponseFactory;
use League\Glide\ServerFactory;

class ImagesController extends Controller
{
    public function show(Filesystem $filesystem, Request $request, $path)
    {
        $server = ServerFactory::create([
            'response' => new LaravelResponseFactory($request),
            'source' => $filesystem->getDriver(),
            'cache' => $filesystem->getDriver(),
            'cache_path_prefix' => '.glide-cache',
        ]);

        return $server->getImageResponse($path, $request->all());
    }

    public function upload(Request $request)
    {
        if ($request->hasFile('image')) {
            $image = $request->file('image');

            // Generate unique filename
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();

            // Store image on disk
            $image->storeAs('public/images', $filename);

            // Return URL to uploaded image
            return response()->json([
                'url' => asset('storage/images/' . $filename)
            ]);
        }

        return response()->json([
            'error' => 'No image was provided'
        ], 400);
    }
}
