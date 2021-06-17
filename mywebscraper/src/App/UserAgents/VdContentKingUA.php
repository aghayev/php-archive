<?php

namespace App\UserAgents;

class VdContentKingUA implements UserAgent {

public function getHeader() {
     return array(
        'Accept: */*',
        'Accept-Encoding: gzip,deflate',
        'Host: www.visiondirect.it',
        'User-Agent: Mozilla/5.0 (compatible; ContentKing; +https://whatis.contentkingapp.com)'
    );
  }
}