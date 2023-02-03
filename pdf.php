<?php
    $datum = time(); // Date
    $kw = date("W", $datum); // KW
    $year = date("Y"); // Year
    $kw = (int)$kw; // Integer remove first 0

    //$kw = ++$kw; // 2023 eine KW verschoben

    if(date('D') == 'Mon' || date('D') == 'Tue' || date('D') == 'Wed') { 
        $kw = --$kw; // KW minus 1
    }

    if( ini_get('allow_url_fopen') ) {
        // it's enabled, so do something
        echo '<!--https://archiv.wittich.de/epapers/pdf/2421/'.$year.'/'.$kw.'.pdf-->';
        echo '<!-- Day: '.date('D').'--><br>';
        $file = file_get_contents('https://archiv.wittich.de/epapers/pdf/2421/'.$year.'/'.$kw.'.pdf');
        if (!file_exists('pdf/'.$year)) {
            mkdir('pdf/'.$year, 0777, true);
        }
        file_put_contents('pdf/'.$year.'/'.$kw.'.pdf', $file);
    }
    else {
        // it's not enabled, so do something else
    }
?>