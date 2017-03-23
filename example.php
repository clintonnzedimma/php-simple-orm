<?php

use ItvisionSy\SimpleORM\DataModel;

// Include the SimpleOrm class
include './vendor/autorun.php';

// Tell SimpleOrm to use the connection you just created.
DataModel::createConnection('localhost','root','','test', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));

// Define an object that relates to a table.
class Blog extends DataModel { }

// Create an entry.
$entry = new Blog;
$entry->title = 'Hello';
$entry->body = 'World!';
$entry->save();

// Use the object.
printf("%s\n", $entry->title); // prints 'Hello';

// Dump all the fields in the object.
print_r($entry->get());

// Retrieve a record from the table.
$entry = Blog::retrieveByPK($entry->id()); // by primary key

// Retrieve a record from the table using another column.
$entry = Blog::retrieveByTitle('Hello', SimpleOrm::FETCH_ONE); // by field (subject = hello)

// Update the object.
$entry->body = 'Mars!';
$entry->save();

// Delete the record from the table.
$entry->delete();

/*

vm1:/home/alex.joyce/SimpleOrm# php example.php 
Hello
Array
(
    [id] => 1
    [title] => Hello
    [body] => World!
)
vm1:/home/alex.joyce/SimpleOrm# php example.php 
Hello
Array
(
    [id] => 2
    [title] => Hello
    [body] => World!
)

*/
