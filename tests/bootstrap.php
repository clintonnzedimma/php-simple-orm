<?php

require_once './vendor/autoload.php';

use ItvisionSy\SimpleORM\DataModel;

class Blog extends DataModel {
    
    protected static $createdAtColumn=true;
    protected static $updatedAtColumn=true;

}

die(ItvisionSy\SimpleORM\RawSQL::make(1));