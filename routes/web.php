<?php

use Spatie\ArrayToXml\ArrayToXml;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/city/{id}', function ($id) {
    $route =
        config('services.openweather.url') . 'data/' . config('openweather.weather_api_version', 2.5) . '/weather?q=' . $id . '&appid=' . config('services.openweather.key');
    $response = Http::get($route);
    if($response->status() != 200) {
        $output = response($response->json(), 404);
        return $output;
    }
    $data = json_decode($response,true);
    $result = ArrayToXml::convert($data);
    return response($result)->header('Content-Type', 'application/xml');
});

Route::get('/', function () {
    $location = request()->city ? request()->city : 'Oyo';
    $route =
        config('services.openweather.url') . 'data/' . config('openweather.weather_api_version', 2.5) . '/weather?q=' . $location . '&appid=' . config('services.openweather.key');
    $response = Http::get($route);
    if (
        $response->failed() == true |
        $response->status() != 200
    ) {
        $response = response()->json(['message' => 'not a valid city', 'code' => '404'], 404);
        return $response;
    }
    return  view(
        'welcome',
        [
            'city' => $location,
            'data' => $response
        ]
    );
});
