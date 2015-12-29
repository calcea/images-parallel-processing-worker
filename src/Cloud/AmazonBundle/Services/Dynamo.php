<?php
/**
 * Created by PhpStorm.
 * User: george
 * Date: 12/19/2015
 * Time: 2:59 PM
 */

namespace Cloud\AmazonBundle\Services;

use Aws\DynamoDb\DynamoDbClient;
use Cloud\AmazonBundle\Entity\Dynamo\DataBuilderAbstract;

class Dynamo
{
    const EQUAL_OPERATOR = 'EQ';
    const NOT_EQUAL = 'NE';
    const LESS_THAN_OR_EQUAL = 'LE';
    const LESS_THAN = 'LT';
    const GREATER_THAN_OR_EQUAL = 'GE';
    const GREATER_THAN = 'GT';
    const CONTAINS = 'CONTAINS';
    const NOT_CONTAINS = 'NOT_CONTAINS';

    protected $client = null;
    protected $tableName = null;
    protected $builder = null;

    public function __construct(DataBuilderAbstract $builder, $tableName)
    {
        if (empty($tableName)) {
            throw new \Exception("You must send the table name");
        }
        $this->tableName = $tableName;
        $this->builder = $builder;
        $this->client = DynamoDbClient::factory(
            array(
                'credentials' => array(
                    'key' => 'AKIAIBAN56SSVUGHIE7A',
                    'secret' => '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',
                ),
                'region' => 'us-east-1',
                'version' => 'latest',
                'scheme' => 'http'
            ));

    }

    /**
     * Add item to dynamoDB
     *
     * $item = array(
     * 'user_id' => '433',
     * 'photo_id' => '65',
     * 'path_to_s3' => 'http://amazon.s3/img.jpg',
     * 'filename' => 'img.jpg',
     * )
     *
     * @param array $item
     * @return \Aws\Result
     */
    public function addItem(array $item)
    {
        $item = $this->builder->build($item);
        return $this->client->putItem(array(
            'TableName' => $this->tableName,
            'Item' => $item
        ));
    }

    /**
     * Add bulk items to dynamo DB
     *
     * $items = array(
     * 0 => array(
     * 'user_id' => '433',
     * 'photo_id' => '65',
     * 'path_to_s3' => 'http://amazon.s3/img.jpg',
     * 'filename' => 'img.jpg',
     * ),
     * 1 => array(
     * 'user_id' => '9129',
     * 'photo_id' => '90',
     * 'filename' => 'img1.jpg',
     * 'parent' => '5',
     * 'status' => '4'
     * )
     * );
     * @param array $items
     * @return \Aws\Result
     */
    public function addItems(array $items)
    {
        $dataBuilded = array();
        $itemsAdded = array();
        $requestSample = array(
            'RequestItems' => array(
                $this->tableName => $itemsAdded
            )
        );
        foreach ($items as $item) {
            $dataBuilded[] = $this->builder->build($item);
        }
        foreach ($dataBuilded as $data) {
            $itemsAdded[] = array(
                'PutRequest' => array(
                    'Item' => $data
                )
            );
        }
        $requestSample['RequestItems'][$this->tableName] = $itemsAdded;
        return $this->client->batchWriteItem($requestSample);
    }

    /**
     * Returns the items from table by filters
     * $filters = array(
     * array(
     * 'columnName' => 'UserID',
     *  'value' => '1',
     *  'operator => Services\Dynamo::EQUAL_OPERATOR
     * ),
     * array(
     * 'columnName' => 'PhotoID',
     *  'value' => '5',
     *  'operator => Services\Dynamo::EQUAL_OPERATOR
     * ))
     * @param $filters
     */
    public function getItems($filters = array())
    {
        $columnsTypes = $this->builder->getColumnsTypes();
        $filtersBuilded = array();
        $items = array();
        $requestSample = array(
            'TableName' => $this->tableName,
            'KeyConditions' => array()
        );
        foreach ($filters as $filter) {
            $filtersBuilded[$filter['columnName']] = array(
                'AttributeValueList' => array(array(
                    $columnsTypes[trim($filter['columnName'])] => trim($filter['value'])
                )),
                'ComparisonOperator' => trim($filter['operator'])
            );
        }
        $requestSample['KeyConditions'] = $filtersBuilded;
        $result = $this->client->getIterator('Query', $requestSample);
        foreach ($result as $item) {
            $items[] = $this->builder->reverseMapping($item);
        }
        return $items;
    }

    /**
     * Update photo item
     *
     * @param $userId
     * @param $photoId
     * @param array $dataToUpdate
     * @return \Aws\Result
     */
    public function updatePhotoItem($userId, $photoId, array $dataToUpdate)
    {
        $request = $this->builder->buildUpdateRequest($userId, $photoId, $dataToUpdate);
        $request['TableName'] = $this->tableName;
        return $this->client->updateItem($request);

    }
}