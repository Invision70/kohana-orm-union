<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Примесь используется в модели для поддержки объединенного запроса (UNION)
 *
 * @author Invision <Invision70@gmail.com>
 * @package quick-web
 * @link https://github.com/Invision70/kohana-orm-union
 */
trait Trait_Model_ORM_Union {

    /**
     * Получить заготовку модели Query Builder для UNION запроса
     * @param array $columns - общие union колонки
     *
     * @return mixed
     */
    public function union_db_builder(array $columns = array())
    {
        if ($this->_db_builder !== NULL)
        {
            return $this->_db_builder;
        }
        $this->_build(Database::SELECT);
        $select = array(
            array($this->object_name().'.id', 'id'),
            array(DB::expr("'{$this->object_name()}'"), 'object')
        );
        foreach ($columns as $column)
        {
            $select[] = array($this->object_name().'.'.$column, $column);
        }
        return $this->_db_builder->select_array($select)->from(array($this->_table_name, $this->_object_name));
    }

}