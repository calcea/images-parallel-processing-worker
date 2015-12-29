<?php

namespace Cloud\AmazonBundle\Entity\Dynamo;

/**
 * Created by PhpStorm.
 * User: george
 * Date: 12/19/2015
 * Time: 3:22 PM
 */
abstract class DataBuilderAbstract
{
    protected $itemMappings = array();
    protected $columnsTypes = array();

    abstract public function build($data);


    abstract public function getColumnsTypes();

    protected function mapData($data)
    {
        $result = array();
        foreach ($this->itemMappings as $key => $value) {
            if (isset($data[$key])) {
                $result[$value['dynamoName']] = array($this->columnsTypes[$value['dynamoName']] => $data[$key]);
            } else if (isset($value['default'])) {
                $result[$value['dynamoName']] = array($this->columnsTypes[$value['dynamoName']] => $value['default']);
            } else {
                throw new \Exception("The field " . $key . ' is mandatory!');
            }
        }

        return $result;
    }

    public function reverseMapping($data)
    {
        $result = array();
        foreach ($this->itemMappings as $key => $itemMapping) {
            if (isset($data[$itemMapping['dynamoName']])) {
                $result[$key] = $data[$itemMapping['dynamoName']][$this->columnsTypes[$itemMapping['dynamoName']]];
            } else {
                $result[$key] = $itemMapping['default'];
            }
        }
        return $result;
    }

}