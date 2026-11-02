<?php

namespace TCG\Voyager\Tests\Unit\Actions;

use TCG\Voyager\Actions\AbstractAction;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Tests\TestCase;

class AbstractActionTest extends TestCase
{
    /**
     * The users DataType instance.
     *
     * @var \TCG\Voyager\Models\DataType
     */
    protected $userDataType;

    /**
     * A dummy user instance.
     *
     * @var \TCG\Voyager\Models\User
     */
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->userDataType = Voyager::model('DataType')->where('name', 'users')->first();
        $this->user = \TCG\Voyager\Models\User::factory()->create();
    }

    /**
     * This test checks that `getRoute` method calls the `getDefaultRoute`
     * method if the given key is empty.
     */
    public function testGetRouteWithEmptyKey()
    {
        // The `getDefaultRoute` method is called as default inside the
        // `getRoute` method to retrieve the route.
        $stub = new class($this->userDataType, $this->user) extends TestAction {
            public function getDefaultRoute()
            {
                return true;
            }
        };

        $this->assertTrue($stub->getRoute($this->userDataType->name));
    }

    /**
     * This test checks that `getRoute` method calls the expected method when a
     * key is given.
     */
    public function testGetRouteWithCustomKey()
    {
        // The key that's passed to the `getRoute` method will be capitalized
        // and putted between 'get' and 'Route'. Calling `getRoute('custom')`
        // will call the `getCustomRoute` method if it's defined.
        $stub = new class($this->userDataType, $this->user) extends TestAction {
            public function getCustomRoute()
            {
                return true;
            }
        };

        $this->assertTrue($stub->getRoute('custom'));
    }

    /**
     * This test checks that `getAttributes` method will give us the expected
     * output.
     */
    public function testConvertAttributesToHtml()
    {
        $stub = new class($this->userDataType, $this->user) extends TestAction {
            public function getAttributes()
            {
                return [
                    'class'   => 'class1 class2',
                    'data-id' => 5,
                    'id'      => 'delete-5',
                ];
            }
        };

        $this->assertEquals('class="class1 class2" data-id="5" id="delete-5"', $stub->convertAttributesToHtml());
    }

    /**
     * This test checks that `shouldActionDisplayOnDataType` method returns true
     * if the action should be displayed for every data type.
     */
    public function testShouldActionDisplayOnDataTypeWithDefaultDataType()
    {
        $stub = new TestAction($this->userDataType, $this->user);

        $this->assertTrue($stub->shouldActionDisplayOnDataType());
    }

    /**
     * This test checks that `shouldActionDisplayOnDataType` method returns true
     * if the action should only be displayed for a specific data type.
     */
    public function testTrueIsReturnedIfDataTypeMatchesTheOneWhereTheActionWasCreatedFor()
    {
        $stub = new class($this->userDataType, $this->user) extends TestAction {
            public function getDataType()
            {
                return $this->dataType->name;
            }
        };

        $this->assertTrue($stub->shouldActionDisplayOnDataType());
    }

    /**
     * This test checks that `shouldActionDisplayOnDataType` method returns false
     * if the action should only be displayed for a specific data type.
     */
    public function testFalseIsReturnedIfDataTypeDoesNotMatchesTheOneWhereTheActionWasCreatedFor()
    {
        $stub = new class($this->userDataType, $this->user) extends TestAction {
            public function getDataType()
            {
                return 'not users'; // different data type
            }
        };

        $this->assertFalse($stub->shouldActionDisplayOnDataType());
    }
}

/**
 * Minimal concrete action, PHPUnit 12 can no longer mock abstract classes.
 */
class TestAction extends AbstractAction
{
    public function getTitle()
    {
        return 'Test';
    }

    public function getIcon()
    {
        return 'voyager-test';
    }

    public function getDefaultRoute()
    {
        return null;
    }
}
