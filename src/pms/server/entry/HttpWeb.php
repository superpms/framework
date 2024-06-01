<?php

namespace pms\server\entry;
use pms\contract\ServerEntryInterface;
use pms\server\request\HttpWebRequest;
use pms\server\response\HttpWebResponse;

class HttpWeb extends Common implements ServerEntryInterface
{
    protected string $name = 'http server';

    public function run(){
        $request = new HttpWebRequest();
        $response = new HttpWebResponse();
        $pathinfo = $request->pathinfo();
        $appName = config('http.app_name','api');
        if(str_starts_with($pathinfo,'/'.$appName)){
            (new \pms\server\HttpWeb())->run($request,$response);
        }else{
            $this->sendFile($pathinfo,$response);
        }
    }

}