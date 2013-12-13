<?php

class MyPlugin_Entity_Table_Foo extends ORM_Table
{
    protected function setTableDefinition()
    {
        $this->setTableName('foo');
        $this->hasColumns(array(
            'id' => array(
                'type' => 'integer',
                'unsigned' => true,
                'autoincrement' => true
            ),
            'active' => array(
                'type' => 'boolean',
                'default' => false
            ),
            'entity' => array(
                'type' => 'entity',
                'class' => 'MyPlugin_Entity_Bar',
            ),
            'bindings' => array(
                'type' => 'bindings',
                'class' => 'MyPlugin_Entity_Bar',
            ),
        ));
        $this->index('active_idx', array('fields' => array('active')));
    }
}

