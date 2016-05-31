<?php
/**
 * Created by PhpStorm.
 * User: cloud
 * Date: 01/11/2014
 * Time: 07:21
 */

class DListProcessor {

        public static function query($table_or_class,$sql){
            $data = [];
            if(class_exists($table_or_class)) {

                $class = $table_or_class;


                if (is_a($class, 'ActiveRecord\Model', true)) {

                    $data = $class::find_by_sql($sql);

                }

            }


            return $data;

        }


        public static function make_it_plain($objs_array){


            $plain = array();

            foreach($objs_array as $obj){

                $plain[] = $obj->to_array();

            }

            return $plain;

        }


        public static function getTableName($table_or_class){

            $tbl = $table_or_class;

            if(class_exists($table_or_class)){

                if(property_exists($table_or_class,'table_name')){

                    $tbl = $table_or_class::$table_name;

                }else{

                    $tbl = $table_or_class;

                }

            }else{

                $tbl = $table_or_class;

            }

            return $tbl;
        }


} 