<?php

require_once './vendor/autoload.php';
require_once './tests/test_classes.php';

Blog::createConnection('127.0.0.1', 'test', 'test', 'test', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
$blog = new Blog(['title' => 'Test enty', 'body' => 'This is a test body with <b>HTML</b>'], Blog::LOAD_NEW);
$blog->updated_at = new DateTime();
$blog->increaseReads();
$blog2 = new FilteredBlog($blog->id, FilteredBlog::LOAD_BY_PK);
$blog2->delete();
