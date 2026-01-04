<?php

namespace Modules\Accounting\DTO;


class JournalEntryData{

    public function __construct(

        public $header,
        public $lines,
        
    ){}


}