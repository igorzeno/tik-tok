<?php

namespace App\Http\Controllers;

use App\Models\Musics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function music($url)
    {
        //dd($url);
        if(!$url) return ['success' => false, 'error' => 'Пустая ссылка'];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://parser.peak.promo',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => 'action=getMusic&url='.$url,
        ));

        $response = curl_exec($curl);
        return $response;
    }

    public function get_music(Request $request)
    {
        return $this->music($request->url);
    }

    public function add_music(Request $request)
    {
        $data = $request->except('_token');
        $track = json_decode($this->music($data['url']));

        if(!$track) return ['success' => false, 'error' => $track];

        $music = $track->music;

        $create = Musics::create([
            'title' => $data['title'],
            'author' => $data['author'],
            'album' => $data['album'],
            'image' => $this->putInStorage('public/covers/'.$this->getEndUrl($music->coverLarge, '/'), $music->coverLarge),
            'url' => $data['url'],
        ]);

        if($create) {
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => 'Не удалось создать трек'];
        }


    }

    public function music_list()
    {
        $data = Musics::get();
        foreach($data as $item) {
            $item->image = asset(Storage::url($item->image));
        }

        return $data;
    }

    public function putInStorage($path, $url)
    {
        if(!Storage::exists($path)) {
            $contents = file_get_contents($url);
            Storage::put($path, $contents);
            Artisan::call('storage:link');
        }
        return $path;
    }

    public function getEndUrl($url, $del)
    {
        $res = parse_url($url, PHP_URL_PATH);
        $url = explode($del, utf8_encode($res));

        return end($url);
    }
}
