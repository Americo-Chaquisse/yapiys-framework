<?php
/**
 * Created by PhpStorm.
 * User: cloud
 * Date: 01/11/2014
 * Time: 06:15
 */

class DTable {

    private static function json_encode_safe($array)
    {

        $internal = $array;

        if(is_string($internal)){
            return $internal;
        }

        array_walk_recursive($internal, function(&$item, $key){

            //Trata-se de uma entity
            if(is_object($item)){

                if(is_a($item,'Entity')){

                    //Converte o objecto para array
                    $item_array = $item->toArray();

                    //Converte o array utf8
                    $item = self::json_encode_safe($item_array);


                }else{


                    //Converte o objecto para array
                    $item_array = json_to_array($item);

                    //Converte o array utf8
                    $item = self::json_encode_safe($item_array);


                }

                return;

            }else if(is_array($item)){

                //Trata-se de um array

                //Converte o array para utf8
                $item = self::json_encode_safe($item);

                return;

            }

            if(!mb_detect_encoding($item, 'utf-8', true)){


                $item = utf8_encode($item);


            }

        });

        return $internal;
    }


    public static function mongoAggregate($columns,$header_cols=false,$collection=false,$find=false,$default_sort=false){

        $aggregate = array();

        if(!$header_cols) $header_cols = $columns;

        $query = self::getFilter();
        $pageNum = self::getPage();
        $per = self::getLimit();

        $aggregate['$match'] = self::mongoFind($query,$columns,$find);

        $projection = array();

        foreach ($columns as $column_name) {
            
            $projection[$column_name] = 1;


        }

        $aggregate['$project'] = $projection;

        $aggregate['$limit']=$per;

        if($pageNum==0||$pageNum<1){

            $pageNum=1;

        }

        if($pageNum){

            $aggregate['$skip'] = ($pageNum-1)*$per;

        }


        //Ordering
        if(DTable::isOrderingActive()){


            $orderby = DTable::getOrderingColumn($header_cols);
            $order = DTable::getOrderingOrder();

            if($orderby&&$order===1||$order===0){

                if($order==0){

                    $order=-1;

                }

                //Adds the sort stage to the aggregate
                $aggregate['$sort']=array($orderby=>$order);

            }


        }else{

            //Ordering was not defined
            if($default_sort){

                $aggregate['$sort'] = $default_sort; 

            }


        }


        //Output the data
        if($collection){

            $limit = $aggregate['$limit'];
            $skip = $aggregate['$skip'];
            $match = $aggregate['$match'];
            $project = $aggregate['$project'];

            $matches = $collection->count($match);

            $q = array(array('$project'=>$project),array('$match' => $match),array('$skip' => $skip), array('$limit' => $limit));


            //Sorting is active
            if(isset($aggregate['$sort'])){

                array_unshift($q,array('$sort'=>$aggregate['$sort']));

            }

            $found = $collection->aggregate($q);

            $items = $found['result'];

            DTable::out($items, $matches);

        }

        return $aggregate;

    }

    public static function getRange(){

        $limit = self::getLimit();
        $page = self::getPage();

        $start = $limit*($page-1);
        $end = $limit*$page;

        return array('start'=>$start,'end'=>$end);


    }

    public static function mongofind($query,$colummns,$append=false){

        $query_parts = explode(' ',$query);
        $ors = array();

        foreach ($query_parts as $query_part){

           $part_query = array();
                
           foreach ($colummns as $column) {
                

                $reg = new MongoRegex("/$query_part/i");
                $part_query[] =array($column=>$reg);

           }


           $ors[] = array('$or'=>$part_query); 

        }

        $q = array('$and'=>$ors);

        if(is_array($append)){

            //Append the find query
            $q['$and'][] = $append;
            //print_r($q);
            //exit();

        }

        return $q;       

    }


    public static function out($data,$matches=false,$universe=0){

    
        if(isset($data['total'])&&isset($data['data'])){

            $total = $data['total'];
            $all_data = $data['data'];

            self::out($all_data,$total);

        }else {

            $range = self::getRange();

            if($matches<$range['end']){

                $range['end']=$matches;

            }

            if($range['end']!=0&&$range['start']==0){

                $range['start']=1;

            }
            $safe_data = self::json_encode_safe($data);
            echo json_encode(array('data' => $safe_data, 'universe' => $universe, 'matches' => $matches,'range'=>$range));

        }

    }

    public static function paginate($sql){

        return $sql.' '.self::_getLimit();

    }


    public static function getFilter(){

        return addslashes(filter_input(INPUT_GET,'q',FILTER_SANITIZE_STRING));

    }


    public static function getLimit(){

        return filter_input(INPUT_GET,'limit',FILTER_VALIDATE_INT);

    }



    public static function getPage(){

        return filter_input(INPUT_GET,'page',FILTER_VALIDATE_INT);

    }

    public static function buildFindQuery($table_or_class,$find_by_cols,$header_cols=false,$ands=array()){

        if(!$header_cols){

            $header_cols=$find_by_cols;

        }

        $connection_name = $table_or_class::$connection;
        $connection = ActiveRecord\Connection::instance($connection_name);
        $q = self::getFilter();
        $columns = implode(',',$find_by_cols);
        $tbl = DTableProcessor::getTableName($table_or_class);


        $execs = explode(' ',$q);

        $i=0;

        $query = false;

        $params_to_bind = array();

        foreach($execs as $ex){

            $params_to_bind[] = '%'.$ex.'%';


            if($i==0){

                $query =  "SELECT * FROM $tbl WHERE CONCAT($columns) LIKE ?";

            }else{

                $query =  "SELECT * FROM ($query) AS q$i WHERE CONCAT($columns) LIKE ?";


            }

            $i++;
        }


        foreach($ands as $and){

            $query = $query.' AND '.$and;

        }


        //ORDER BY PARAMETERS
        if(isset($_GET['ordering_by'])&&isset($_GET['ordering'])){

            $order = false;
            $columnName = DTable::getOrderingColumn($header_cols);
            $ordering = DTable::getOrderingOrder();



            if($ordering!==FALSE){

                if($ordering==0){

                    $order = "DESC";

                }else if($ordering==1){

                    $order = "ASC";

                }

            }


            if($order&&$columnName){

                $query = $query." ORDER BY $columnName $order";


            }


        }


        return array($query,$params_to_bind);

    }

    private function isOrderingActive(){

        return  isset($_GET['ordering_by'])&&isset($_GET['ordering']);

    }

    private function getOrderingColumn($header_cols){

        $columnName = false;

        if(isset($_GET['ordering_by'])){

            $columnIndex = filter_input(INPUT_GET,'ordering_by',FILTER_VALIDATE_INT);

            if($columnIndex!==FALSE){

                if($columnIndex<=count($header_cols)-1){

                    $columnName = $header_cols[$columnIndex];

                }

            }

        }

        return $columnName;

    }

    private function getOrderingOrder(){

        if(isset($_GET['ordering'])){

            $ordering = filter_input(INPUT_GET,'ordering',FILTER_VALIDATE_INT);

            if($ordering!==FALSE){

                return $ordering;

            }

        }

        return false;

    }


    public static function fetchFromSql($sql,$nolimit_sql,$values_to_bind,$model,$replacable='*')
    {

        $connection_name = $model::$connection;
        $table_name = $model::$table_name;
        $connection = ActiveRecord\Connection::instance($connection_name);

        $matches = 0;
        $total = 0;
        $data = array();

        $count_query = $sql;

        if($nolimit_sql){


            $order_by_excluded_from_no_limit_sql = explode('ORDER BY',$nolimit_sql)[0];
            $count_query = 'SELECT COUNT(*) as total '.substr($order_by_excluded_from_no_limit_sql,9);


        }



        $result_set = $connection->query($count_query,$values_to_bind);
        $matches = $result_set->fetchAll()[0]['total'];


        $connection->query_and_fetch("Select count(*) as total from $table_name",function($values) use(&$total){

            $total = $values['total'];

        });





        $result_set = $connection->query($sql,$values_to_bind);
        $data = $result_set->fetchAll();


        self::out($data,$matches,0);


    }

    public static function fetchData($table_or_class,$columns,$header = false,$ands = array()){

        $query = self::buildFindQuery($table_or_class,$columns,$header,$ands);

        $bind = $query[1];
        $sql = $query[0];

        $order_by_excluded_from__sql = explode('ORDER BY',$sql)[0];

        $count_query = "SELECT COUNT(*) as total from ($order_by_excluded_from__sql) as tbl";

        $limit_sql = self::_getLimit();
        $page_sql = $sql.' '.$limit_sql;

        $connection_name = $table_or_class::$connection;
        $connection = ActiveRecord\Connection::instance($connection_name);


        $counted_pdo_statement = $connection->query($count_query,$bind);


        $count_data = $counted_pdo_statement->fetchAll();



        $page_data_pdo_statement = $connection->query($page_sql,$bind);
        
        $page_data = $page_data_pdo_statement->fetchAll();

        $response = array();

        $total = $count_data[0]['total'];

        if(is_null($total)){

            $total =0;

        }


        $response['total']=$total;
        $response['data']=$page_data;


        self::out($response['data'],$total,0);


    }


    public static function _getLimit($string=true,$limit_string=true){

        $q = filter_input(INPUT_GET,'q',FILTER_SANITIZE_STRING);
        $page = filter_input(INPUT_GET,'page',FILTER_VALIDATE_INT) -1;
        $limit = filter_input(INPUT_GET,'limit',FILTER_VALIDATE_INT);

        $lA = $page * $limit;

        $lB = $limit;

        if($string){

            if($limit_string){

                return "limit $lA,$lB";

            }else{

                return "$lA,$lB";

            }


        }else{

            return array($lA,$lB);

        }

    }


}
