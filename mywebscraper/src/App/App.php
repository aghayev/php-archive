<?php

namespace App;

use Model\Products;
use Model\Download;
use Model\Logger;
use Utils\DateTimeUtils;

class App
{
     public function run()
     {

         try {
             // {Date_timestamp} used as logger filename and as 2nd subfolder within scapped data path
             $date_timestamp = DateTimeUtils::getCurrentTS();

             // Init logger
             $logger = new Logger($date_timestamp);
             $logger->info('Running vdwebscaper on '.$date_timestamp);

             // Default UserAgent
             $userAgent = new UserAgents\VdContentKingUA();
             $logger->info('Init userAgent');

             // Init Request object
             $request = new HttpRequest();
             $logger->info('Init HttpRequest object');

             // Draft version implements based on products, gets urls from config
             $products = new Products();
             $urls = $products->getUrls();
             $logger->info('Loaded url count: '.count($urls));

             $availableVendors = array_filter(
                 $urls,
                 function ($url) use ($logger, $request, $userAgent, $products, $date_timestamp) {

                     $logger->info('Scraping url: '.$url);
                     $response = $request->get($url, $userAgent);

                     $downloadpath = [];
                     if ($response instanceOf HttpResponse) {

                         // Downloading Scraped Data
                         $downloadpath[] = $products->getDownloadFolder();
                         $downloadpath[] = Products::CLASS_NAME;
                         $downloadpath[] = $products->getProductName($url);
                         $downloadpath[] = $date_timestamp;
                         $downloadpath[] = $response->getXVarnishIp();

                         $download = Download::getInstance();
                         $builtPath = $download->buildPath($downloadpath);
                         $logger->info('Saving data path:'.$builtPath);

                         if (!is_dir($builtPath)) {
                             $download->save($builtPath, $response);
                             $logger->info('Saved');
                         }
                         else {
                             $logger->error('path already exists');
                         }
                     }
                     else {
                         $logger->error('no http response');
                     }
                 }
             );
         } catch (Exception $exception) {
             $logger->error('Error occured:', array('exception' => $exception));
         }
     }
}
