<?php

/**
 * @covers ItvisionSy\SimpleORM\DataModel
 * @covers ItvisionSy\SimpleORM\RawSQL
 */
class TestCase extends \PHPUnit\Framework\TestCase {

    protected static function db() {
        return [
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'user' => getenv('DB_USER') ?: 'test',
            'pass' => getenv('DB_PASS') ?: 'test',
            'name' => getenv('DB_NAME') ?: 'test'
        ];
    }

    public function setUp() {
        parent::setup();
        $db = static::db();
        Blog::createConnection($db['host'], $db['user'], $db['pass'], $db['name'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
    }

    public function testTruncate() {
        $db = static::db();
        Blog::createConnection($db['host'], $db['user'], $db['pass'], $db['name'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
        Blog::truncate();
        $this->assertEquals(0, Blog::count());
    }

    public function testConnections() {
        //use
        $db = static::db();
        Blog::useConnection(new PDO("mysql:dbname={$db['name']};host={$db['host']}", $db['user'], $db['pass']));
        $this->assertEquals(0, Blog::count());
    }

    /**
     */
    public function testCreateNew() {

        //directly
        $blog = new Blog();
        $this->assertEquals(Blog::LOAD_EMPTY, $blog->getLoadMethod());
        $this->assertNull($blog->getLoadData());
        $blog->title = 'Test';
        $blog->body = 'Test Body';
        $blog->rate = 3.6;
        $blog->save();
        $this->assertNotNull($blog->id());

        //by array
        $blog = new Blog(['title' => 'Test', 'body' => 'Test body'], Blog::LOAD_NEW);
        $this->assertEquals(Blog::LOAD_NEW, $blog->getLoadMethod());
        $this->assertTrue(is_array($blog->getLoadData()));
        $this->assertNotNull($blog->id());
        $blog->delete();

        $blog2 = new Blog();
        $blog2->set($blog->getRaw());
        $blog2->update();
        $this->assertNotNull($blog2->id());
    }

    public function testFetchAll() {
        $blogs = Blog::all();
        $this->assertEquals(2, count($blogs));
    }

    public function testLoad() {

        //by id
        $blog = Blog::retrieveByPK(1);
        $this->assertNotNull($blog);
        $this->assertEquals('Test', $blog->title);

        //static method retrieveBy
        $blog = Blog::retrieveById(2);
        $this->assertNotNull($blog);
    }

    public function testCreatedAndUpdatedAt() {

        $date = Blog::sql("SELECT CURRENT_TIMESTAMP", BLOG::FETCH_FIELD);
        $blog = new Blog(['title' => 'Test', 'body' => 'Test body'], Blog::LOAD_NEW);
        $this->assertEquals($date, $blog->created_at->format('Y-m-d H:i:s'));
        $this->assertEquals($date, $blog->updated_at->format('Y-m-d H:i:s'));
        sleep(2);
        $date = Blog::sql("SELECT CURRENT_TIMESTAMP", BLOG::FETCH_FIELD);
        $blog->save();
        $this->assertEquals($date, $blog->updated_at->format('Y-m-d H:i:s'));
    }

    public function testAccess() {
        $blog = Blog::retrieveByPK(1);
        $this->assertInstanceOf(DateTime::class, $blog->get('updated_at'));
        $this->assertStringMatchesFormat("%d-%d-%d %d:%d:%d", $blog->getRaw('updated_at'));
        $this->assertTrue(is_array($blog->get()));
        $this->assertTrue(is_array($blog->getRaw()));
        $this->assertEquals(count($blog->getRaw()) + 1, count($blog->get()));
    }

    public function testRevert() {
        $blog = Blog::retrieveByField('id', 1, Blog::FETCH_ONE);
        $this->assertFalse($blog->isModified());
        $this->assertEquals('Test', $blog->title);
        $blog->set(['title' => 'Title2']);
        $this->assertEquals('Title2', $blog->title);
        $blog->set('title', 'Title3');
        $this->assertEquals('Title3', $blog->get('title'));
        $this->assertTrue($blog->isModified());
        $this->assertEquals(1, count($blog->modified()));
        $this->assertEquals(2, count($blog->modified()['title']));
        $blog2 = $blog->revert(true);
        $this->assertEquals('Test', $blog2->title);
        $this->assertNotEquals($blog, $blog2);
    }

    public function testDelete() {
        $blog = new Blog(['title' => 'To delete', 'body' => 'To be deleted'], Blog::LOAD_NEW);
        $this->assertNotNull($blog);
        $blog->delete();
        $this->expectException(Exception::class);
        $blog->revert();
    }

    public function testIncorrectIdLoad() {

        //load wrong id
        $this->expectException(Exception::class);
        Blog::retrieveByPK(22);
    }

    public function testTruncateReadOnly() {
        //read only truncate
        $this->expectException(Exception::class);
        ReadBlog::truncate();
    }

    public function testInsertReadOnly() {
        //write new to readonly
        $this->expectException(Exception::class);
        $blog = new ReadBlog(['title' => 'Erroring one', 'body' => 'the body'], ReadBlog::LOAD_NEW);
    }

    public function testUpdateReadOnly() {
        //modify to readonly
        $this->expectException(Exception::class);
        $blog = ReadBlog::retrieveByPK(1);
        $blog->increaseReads();
    }

    public function testDeleteReadonly() {
        //modify to readonly
        $this->expectException(Exception::class);
        $blog = ReadBlog::retrieveByPK(1);
        $blog->delete();
    }

    public function testDeleteNew() {
        //modify to readonly
        $this->expectException(Exception::class);
        $blog = new Blog(['title' => 'Test', 'body' => 'Test'], Blog::LOAD_BY_ARRAY);
        $blog->delete();
    }

    public function testDescribeUnexisted() {
        //modify to readonly
        $this->expectException(Exception::class);
        $entry = new UnexistedTable(null, UnexistedTable::LOAD_EMPTY);
    }

    public function testInvalidSQL() {
        //modify to readonly
        $this->expectException(Exception::class);
        $entry = Blog::sql("SELECT SomeInvalidFunction();");
    }

    public function testInvalidPkValue() {
        //modify to readonly
        $this->expectException(InvalidArgumentException::class);
        $entry = Blog::retrieveByPK([]);
    }

    public function testInvalidStaticCall() {
        //modify to readonly
        $this->expectException(Exception::class);
        $entry = Blog::someMethodDoesNotExist([]);
    }

    public function testInvalidSet() {
        //modify to readonly
        $this->expectException(Exception::class);
        $entry = Blog::retrieveByPK(1);
        $entry->some_invalid_value = 1;
    }

    public function testInvalidExecuteStatement() {
        //modify to readonly
        $this->expectException(Exception::class);
        $entry = new UnexistedTable(['k' => 1], UnexistedTable::LOAD_NEW);
    }

    public function testFilteredCrud() {
        //insert
        $blog = new FilteredBlog(['title' => 'Test', 'body' => ''], FilteredBlog::LOAD_NEW);
        $this->assertTrue($blog->isNew());

        //update
        $blog = FilteredBlog::retrieveByPK(1);
        $blog->body = '';
        $blog->save();
        $blog->revert();
        $this->assertEquals('Test Body', $blog->body);

        //delete
        $blog->delete();
        $blog->revert();
        $this->assertNotEmpty($blog);
    }

}
