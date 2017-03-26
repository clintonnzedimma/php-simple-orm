<?php

/**
 * @covers ItvisionSy\SimpleORM\DataModel
 */
class TestCase extends \PHPUnit\Framework\TestCase {

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        Blog::createConnection('127.0.0.1', 'test', 'test', 'test', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
    }

    protected function setUp() {
        parent::setUp();
    }

    protected function tearDown() {
        parent::tearDown();
    }

    public function testTruncate() {
        Blog::truncate();
        $this->assertEquals(0, Blog::count());
    }

    /**
     */
    public function testCreateNewDirectly() {
        $blog = new Blog();
        $this->assertEquals(Blog::LOAD_EMPTY, $blog->getLoadMethod());
        $this->assertNull($blog->getLoadData());
        $blog->title = 'Test';
        $blog->body = 'TestBody';
        $blog->save();
        $this->assertNotNull($blog->id());
    }

    public function testCreateNewByArray() {
        $blog = new Blog(['title' => 'Test', 'body' => 'Test body'], Blog::LOAD_NEW);
        $this->assertEquals(Blog::LOAD_NEW, $blog->getLoadMethod());
        $this->assertTrue(is_array($blog->getLoadData()));
        $this->assertNotNull($blog->id());
    }

    public function testUseConnection() {
        Blog::useConnection(new PDO("mysql:dbname=test;host=127.0.0.1", 'test', 'test'));
        $this->testCreateNewDirectly();
    }

    public function testCreateConnection() {
        Blog::createConnection('127.0.0.1', 'test', 'test', 'test', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
        $this->testCreateNewDirectly();
    }

    public function testLoadById() {
        $blog = Blog::retrieveByPK(1);
        $this->assertNotNull($blog);
        $this->assertEquals('Test', $blog->title);
        $this->expectException(Exception::class);
        Blog::retrieveByPK(22);
    }
    
    public function testCreatedAndUpdatedAt(){
        $date = date('Y-m-d H:i:s');
        $blog = new Blog(['title' => 'Test', 'body' => 'Test body'], Blog::LOAD_NEW);
        $this->assertEquals($date, $blog->created_at);
        $this->assertEquals($date, $blog->updated_at);
        sleep(2);
        $date = date('Y-m-d H:i:s');
        $blog->save();
        $this->assertEquals($date, $blog->updated_at);
    }

}
