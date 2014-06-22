<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Результаты запроса объединения. Итератор.
 *
 * @author Invision <Invision70@gmail.com>
 * @package quick-web
 * @link https://github.com/Invision70/kohana-orm-union
 */
class ORM_Union_Result implements Countable, Iterator, ArrayAccess {

    protected $_result;
    protected $_total_rows  = 0;
    protected $_position = 0;
    protected $_model_result = array();

    /**
     * Конструктор
     * @param Database_Result $result - результат UNION запроса
     */
    public function __construct(Database_Result $result)
    {
        $this->_result = $result;
        $this->_prepare_model($this->_result);
        $this->_total_rows = count($result);
        $this->_position = 0;
    }

    /**
     * Подготовка ORM объектов. По запросу на объект (WHERE id IN ...)
     * @param Database_Result $result
     *
     * @throws Exception
     */
    protected function _prepare_model(Database_Result $result)
    {
        $object_data = array();
        foreach ($result as $item)
        {
            if ( ! $object = Arr::get($item, 'object'))
            {
                throw new Exception('Unknown object');
            }
            if ( ! $item_id = Arr::get($item, 'id'))
            {
                throw new Exception('Unknown item id');
            }
            $object_data[$object][] = $item_id;
        }

        foreach ($object_data as $object => $items)
        {
            // @TODO - fix it
            $object_name = str_replace('_', ' ', $object);
            $object_name = ucwords(strtolower($object_name));
            $object_name = str_replace(' ', '_', $object_name);

            $orm_object = ORM::factory($object_name);
            $this->_model_result[$object] = $orm_object->where($orm_object->object_name().'.id', 'IN', $items)->find_all()->as_array('id');
        }

    }

    public function count()
    {
        return $this->_total_rows;
    }

    public function rewind()
    {
        $this->_position = 0;
    }

    public function current()
    {
        $result = $this->_result[$this->_position];
        return $this->_model_result[$result['object']][$result['id']];
    }

    public function key()
    {
        return $this->_position;
    }

    public function next()
    {
        ++$this->_position;
    }

    public function valid()
    {
        return isset($this->_result[$this->_position]);
    }

    public function offsetExists($offset)
    {
        return isset($this->_result[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->_result[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_result[] = $value;
        } else {
            $this->_result[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->_result[$offset]);
    }

}