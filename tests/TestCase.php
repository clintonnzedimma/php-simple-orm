<?php

class TestCase extends PHPUnit_Framework_TestCase{
    
    public function testCreate(){
        $blog = new Blog();
        $blog->title='Test';
        $blog->body = 'TestBody';
        $blog->save();
        $this->assertNotNull($blog->id());
    }
    
}