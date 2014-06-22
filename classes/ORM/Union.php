<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Объединение ORM объектов на основе UNION запроса
   <code>
   $order = ORM::factory('Order')->where('user_id', '=', $this->user);
   $forwarding = ORM::factory('Forwarding')->where('user_id', '=', $this->user);

   $union = ORM_Union::initial([$order, $forwarding], ['created'])->order_by('created', 'desc');
   $result = $union->find_all();
   </code>
 *
 * @author Invision <Invision70@gmail.com>
 * @package quick-web
 * @link https://github.com/Invision70/kohana-orm-union
 */
class ORM_Union {

    /**
     * Объекты ORM
     * @var array
     */
    protected $_objects = array();

    /**
     * Общие колонки UNION таблиц
     * @var array
     */
    protected $_union_columns = array();
    /**
     * ORM Union Query Builder
     * @var Database_Query_Builder
     */
    protected $_builder;

    /**
     * Инициализация
     * @param $objects
     * @param $union_columns
     * @return ORM_Union
     */
    public static function initial(array $objects = array(), array $union_columns = array())
    {
        return new self($objects, $union_columns);
    }

    /**
     * Конструктор
     * @param array $objects
     * @param array $union_columns
     */
    public function __construct(array $objects = array(), array $union_columns = array())
    {
        $this->_union_columns = $union_columns;
        foreach($objects as $object)
        {
            $this->set_object($object);
        }
    }

    /**
     * Подготовка запроса UNION Query Builder
     * @param       $name
     * @param array $args
     *
     * @return mixed
     * @throws Exception
     */
    public function __call($name, $args = [])
    {
        $builder = $this->_builder();
        if (method_exists($builder, $name))
        {
            call_user_func_array(array($builder, $name), $args);
        }

        return $this;
    }

    /**
     * Добавить ORM Union объект
     * @param ORM $object
     * @throws Exception
     * @return $this
     */
    public function set_object(ORM $object)
    {
        if ( ! isset(class_uses($object)['Trait_Model_ORM_Union']))
        {
            throw new Exception('Invalid union ORM object '.$object->object_name());
        }
        $this->_objects[$object->object_name()] = $object;
        return $this;
    }

    /**
     * Конструктор UNION Query Builder на основе ORM объектов
     * @return Database_Query_Builder_Select
     */
    protected function _builder()
    {
        if ($this->_builder !== NULL)
        {
            return $this->_builder;
        }

        $builder = NULL;
        foreach ($this->_objects as $object)
        {
            $object_builder = $object->union_db_builder($this->_union_columns);
            if ($builder === NULL)
            {
                $builder = $object_builder;
            }
            else
            {
                $builder->union($object_builder);
            }
        }
        return $this->_builder = DB::select()->from(array($builder, 'build'));
    }

    /**
     * Количество записей
     * @return int
     */
    public function count_all()
    {
        return DB::select(array(DB::expr('COUNT(*)'), 'c'))->from(array($this->_builder(), 'total'))->execute()->get('c');
    }

    /**
     * Получить данные
     * @return ORM_Union_Result
     */
    public function find_all()
    {
        return new ORM_Union_Result($this->_builder()->execute());
    }
}