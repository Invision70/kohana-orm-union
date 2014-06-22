kohana-orm-union
================

Kohana 3.3 module orm-union 

### Installation

1. Download the module into your modules subdirectory
2. Enable the module in your bootstrap file

### Example

## ORM

#### Trait

    class Model_Forwarding extends ORM {
        use Trait_Model_ORM_Union;
    ...

and 

    class Model_Order extends ORM {
        use Trait_Model_ORM_Union;
    ...

## Use

        $order = ORM::factory('Order')->where('user_id', '=', 1);
        $forwarding = ORM::factory('Forwarding')->where('user_id', '=', 1);
        
        // initial arguments: (union ORM objects, select columns for SQL UNION ALL)
        $union = ORM_Union::initial([$order, $forwarding],  ['created'])
                           ->order_by('created', 'desc'); // Query Builder for SQL UNION ALL
        $total =  $union->count_all();
        $result = $union->limit(10)->find_all();
        
        foreach ($result as $object)
        {
            var_dump(get_class($object)); // ORM loaded object
        }

        // result
        string(11) "Model_Order"
        string(16) "Model_Forwarding"
        string(11) "Model_Order"
        string(11) "Model_Order"
        string(11) "Model_Order"
        string(16) "Model_Forwarding"
        string(11) "Model_Order"
        string(16) "Model_Forwarding"
        string(16) "Model_Forwarding"
        string(11) "Model_Order"

        // 4 SQL query (3 without count_all)
 
* Working with pagination
