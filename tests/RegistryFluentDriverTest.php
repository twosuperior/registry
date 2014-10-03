<?php namespace Twosuperior\Registry\Tests;

use Mockery as m;
use Twosuperior\Registry\Drivers\Fluent;

class RegistryFluentDriverTest extends \PHPUnit_Framework_TestCase {

    protected $app;

    protected $query;

    public function setUp()
    {
        $this->app = array(
            'config' => m::mock('Config'),
            'db' => m::mock('DB')
        );

        $this->query = m::mock('DB\Query');
    }

    public function tearDown()
    {
        unset($this->app);
        unset($this->query);

        m::close();
    }

    public function testAllMethod()
    {
        $registry = $this->getInstance();

        $this->app['db']->shouldReceive('table')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('key', '=', 'twosuperior')->andReturn($this->query)->shouldReceive('first')->andReturn($this->getStub());

        $this->assertTrue($registry->all());
    }
	
    public function testGetMethod()
    {
        $registry = $this->getInstance();

        $this->app['db']->shouldReceive('table')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('key', '=', 'twosuperior')->andReturn($this->query)->shouldReceive('first')->andReturn($this->getStub());

        $this->assertEquals(array('foo' => 'bar'), $registry->get('twosuperior'));
        $this->assertEquals('bar', $registry->get('twosuperior.foo'));
    }

    public function testGetMethodReturnNull()
    {
        $registry = $this->getInstance();

        $this->app['db']->shouldReceive('table')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('key', '=', 'foobar')->andReturn($this->query)->shouldReceive('first')->andReturn(null);

        $this->assertEquals(null, $registry->get('foobar'));
    }

    public function testStoreMethod()
    {
        $key = 'twosuperior';
        $value = json_encode('bar');

        $registry = $this->getInstance();

        $this->app['db']->shouldReceive('table')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('key', '=', 'twosuperior')->andReturn($this->query)->shouldReceive('first')->andReturn(null);
        $this->query->shouldReceive('insert')->with(array('key' => $key, 'value' => $value));

        $this->assertTrue($registry->store('twosuperior', 'bar'));
    }

    public function testOverwriteMethod()
    {
        $registry = $this->getInstance();

        $this->app['db']->shouldReceive('table')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('key', '=', 'twosuperior')->andReturn($this->query)->shouldReceive('first')->andReturn($this->getStub());
        $this->query->shouldReceive('update')->with(array('value' => json_encode(array('foo' => 'foobar'))))->andReturn(true);

        $this->assertTrue($registry->overwrite('twosuperior.foo', 'foobar'));
    }

    /**
     * @expectedException Exception
     */
    public function testOverwriteMethodThrowException()
    {
        $registry = $this->getInstance();

        $this->app['db']->shouldReceive('table')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('key', '=', 'foobar')->andReturn($this->query)->shouldReceive('first')->andReturn(null);

        $registry->overwrite('foobar');
    }

    public function testForgetDeleteMethod()
    {
        $registry = $this->getInstance();

        $this->app['db']->shouldReceive('table')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('key', '=', 'twosuperior')->andReturn($this->query)->shouldReceive('first')->andReturn($this->getStub());
        $this->query->shouldReceive('delete')->andReturn(true);

        $this->assertTrue($registry->forget('twosuperior'));
    }

    public function testForgetUpdateMethod()
    {
        $registry = $this->getInstance();

        $this->app['db']->shouldReceive('table')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('key', '=', 'twosuperior')->andReturn($this->query)->shouldReceive('first')->andReturn($this->getStub());
        $this->query->shouldReceive('update')->with(array('value' => json_encode(array())))->andReturn(true);

        $this->assertTrue($registry->forget('twosuperior.foo'));
    }

    /**
     * @expectedException Exception
     */
    public function testForgetMethodThrowException()
    {
        $registry = $this->getInstance();

        $this->app['db']->shouldReceive('table')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('key', '=', 'foobar')->andReturn($this->query)->shouldReceive('first')->andReturn(null);

        $registry->forget('foobar');
    }

    public function testFlushMethod()
    {
        $registry = $this->getInstance();

        $this->app['db']->shouldReceive('table')->andReturn($this->query);
        $this->query->shouldReceive('truncate')->andReturn(true);

        $this->assertTrue($registry->flush());
    }

    public function testDumpMethod()
    {
        $registry = $this->getInstance();

        $this->app['db']->shouldReceive('table')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('key', '=', 'twosuperior')->andReturn($this->query)->shouldReceive('first')->andReturn($this->getStub());

        $this->assertEquals(array('foo' => 'bar'), $registry->dump('twosuperior'));
    }

    protected function getInstance()
    {
        $this->app['config']->shouldReceive('get')->with('twosuperior/registry::table', 'registry')->andReturn('registry');
        return new Fluent($this->app);
    }

    protected function getStub()
    {
        $stub = m::mock('stub');
        $stub->key = 'twosuperior';
        $stub->value = json_encode(array('foo' => 'bar'));

        return $stub;
    }

}