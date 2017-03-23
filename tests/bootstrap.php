<?php

require_once './vendor/autoload.php';

use ItvisionSy\SimpleORM\DataModel;

DataModel::createConnection('127.0.0.1', 'test', 'test', 'test', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));

class Blog extends DataModel {
    
}
