<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class GitController extends Controller
{
    public function list()
    {

        try {

            $token = 222;

            $headers = [
                'Accept' => 'application/vnd.github.v3+json',
            ];

            $client = new Client([
                'headers' => $headers,
                'base_uri' => 'https://api.github.com/'
            ]);

            // RELEASES
            $releases = $client->request('GET', 'repos/drorganvidez/evidentia/releases',[
                'query' => ['token' => $token]
            ]);

            if($releases->getStatusCode() == 403){
                return view('updates.list_error', ['instance' => \Instantiation::instance()]);
            }

            $releases = json_decode($releases->getBody());
            $releases = (array)$releases;

            //  ISSUES
            $issues = $client->request('GET', 'repos/drorganvidez/evidentia/issues');

            if($issues->getStatusCode() == 403){
                return view('updates.list_error', ['instance' => \Instantiation::instance()]);
            }

            $issues = json_decode($issues->getBody());
            $issues = (array)$issues;

            return view('updates.list', ['instance' => \Instantiation::instance(), 'releases' => $releases, 'issues' => $issues]);


        }catch (\Exception $e){
            return view('updates.list_error', ['instance' => \Instantiation::instance()]);
        }

    }
}
