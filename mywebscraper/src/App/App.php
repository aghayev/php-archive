<?php

namespace App;

use Model\Products;
use Model\IO;
use Utils\DateTimeUtils;

class App
{
     public function run()
     {

         try {

             // Draft version implements based on products, gets urls from config
             $products = new Products();
             $urls = $products->getUrls();

             // Getting current timestamp
             $timestamp = DateTimeUtils::getCurrentTS();

             // Init Request object
             $request = new HttpRequest();

             // Default UserAgent
             $userAgent = new UserAgents\VdContentKingUA();

             $availableVendors = array_filter(
                 $urls,
                 function ($url) use ($request, $userAgent, $products) {
                     $response = $request->get($url, $userAgent);

                     if ($response instanceOf HttpResponse) {
                        //echo $response->getXVarnishIp();
                         //echo $products;

                         //$io = new IO();
                         //$io->save('products');
                         echo $response->getContent();exit;
                     }
                 }
             );
         } catch (Exception $e) {
             return "Error occured: {$e->getMessage()}" . PHP_EOL;
         }
     }
}