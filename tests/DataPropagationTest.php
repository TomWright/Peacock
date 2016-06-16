<?php


class DataPropagationTest extends TestBase
{

    public function testNoLevel()
    {
        $data = ['name' => 'Tom'];
        $view = $this->factory->layout('test_layout', $data);

        $view->parse();

        $this->assertEquals($view->getData(), $data);
    }

    public function testOneLevel()
    {
        $data = ['name' => 'Tom'];
        $view = $this->factory->layout('test_layout', $data);

        $child = $view->childView('test_view', 'content');

        $view->parse();

        $this->assertEquals($child->getData(), $data);
    }

    public function testTwoLevels()
    {
        $data = ['name' => 'Tom'];
        $view = $this->factory->layout('test_layout', $data);

        $child = $view->childView('test_view', 'content');

        $child2 = $child->childView('test_view', 'content');

        $view->parse();

        $this->assertEquals($child2->getData(), $data);
    }

}