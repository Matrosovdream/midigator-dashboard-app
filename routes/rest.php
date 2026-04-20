<?php

foreach (glob(__DIR__.'/rest/*.php') as $file) {
    require $file;
}
