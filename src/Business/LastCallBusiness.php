<?php

namespace App\Business;


class LastCallBusiness {

    static function checkFormat(&$time = 0) {
        if (empty($time)) {
            $time = 0;
        }
        else if (strlen($time) > 10) {
            $time = substr($time, 0, 10);
        }
    }

}