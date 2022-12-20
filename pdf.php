<?php
    $datum = time(); // Date
    $kw = date("W", $datum); // KW
    $year = date("Y"); // Year

    if(date('D') == 'Mon' || date('D') == 'Tue' || date('D') == 'Wed') { 
        $kw = --$kw; // KW minus 1
    }

    if( ini_get('allow_url_fopen') ) {
        // it's enabled, so do something
        echo '<!--https://archiv.wittich.de/epapers/pdf/2421/'.$year.'/'.$kw.'.pdf-->';
        echo '<!-- Day: '.date('D').'--><br>';
        $file = file_get_contents('https://archiv.wittich.de/epapers/pdf/2421/'.$year.'/'.$kw.'.pdf');
        file_put_contents('pdf/'.$kw.'.pdf', $file);
    }
    else {
        // it's not enabled, so do something else
    }
?>