<?php
/**
 * Created by PhpStorm.
 * User: george
 * Date: 12/20/2015
 * Time: 6:04 PM
 */

namespace Worker\ExecuteBundle\Services;


use Cloud\AmazonBundle\Services\Dynamo;
use Cloud\AmazonBundle\Services\Queue;
use Cloud\AmazonBundle\Services\S3;

class ProcessQueue
{

    const MESSAGES_SEPARATOR = '-_-';
    const STATUS_NEW = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_ERROR = 3;
    const STATUS_DONE = 4;
    const PATH_TO_IMAGE = './processed/';

    protected $queueService = null;
    protected $dynamoService = null;
    protected $s3Service = null;

    public function __construct(Queue $queueService, Dynamo $dynamoService, S3 $s3Service)
    {
        $this->queueService = $queueService;
        $this->dynamoService = $dynamoService;
        $this->s3Service = $s3Service;
    }

    public function run()
    {
        while (true) {
            $messages = $this->queueService->getMessage();
            if (empty($messages)) {
                continue;
            }
            foreach ($messages as $message) {
                $explode = array();
                $explode = explode(self::MESSAGES_SEPARATOR, $message['body']);
                $userId = $explode[0];
                $photoId = $explode[1];
                if(empty($userId) || empty($photoId)){

                }
                $images = $this->dynamoService->getItems($this->getDynamoFilters($userId, $photoId));
                $dataToSave = array();
                foreach ($images as $image) {
                    if ((int)$image['status'] != self::STATUS_NEW) {
                        continue;
                    }
                    try {
                        $this->dynamoService->updatePhotoItem($userId, $photoId, array('Status' => (string)self::STATUS_IN_PROGRESS));
                        $dataToSave = $this->processImage($image);
                        $this->dynamoService->addItems($dataToSave);
                        $this->dynamoService->updatePhotoItem($userId, $photoId, array('Status' => (string)self::STATUS_DONE));
                    } catch (\Exception $e) {
                        $this->dynamoService->updatePhotoItem($userId, $photoId, array('Status' => (string)self::STATUS_ERROR));
                    }
                }
                $this->queueService->deleteMessage($message['receipt']);

            }
        }
    }


    protected function processImage(array $image)
    {
        if (!isset($image['path_to_s3']) || empty($image['path_to_s3'])) {
            return;
        }

        if (copy($image['path_to_s3'], self::PATH_TO_IMAGE . $image['filename'])) {
            $imagePath = self::PATH_TO_IMAGE . $image['filename'];
            $transformService = new TransformImages($imagePath);
            $filteredImages = $transformService->applyAllFilters();
            $dataToSave = array();
            foreach ($filteredImages as $filteredImage) {
                $s3Path = $this->s3Service->uploadPhoto($filteredImage['absolutePath'], $filteredImage['id']);
                $dataToSave[] = array(
                    'user_id' => (string)$image['user_id'],
                    'photo_id' => (string)$filteredImage['id'],
                    'filename' => (string)$filteredImage['filename'],
                    'path_to_s3' => (string)$s3Path,
                    'status' => (string)self::STATUS_DONE,
                    'parent' => (string)$image['photo_id']
                );
            }
            return $dataToSave;
        }
    }

    protected function getDynamoFilters($userId, $photoId)
    {

        return array(
            array(
                'columnName' => 'UserID',
                'value' => (string)$userId,
                'operator' => Dynamo::EQUAL_OPERATOR
            ),
            array(
                'columnName' => 'PhotoID',
                'value' => (string)$photoId,
                'operator' => Dynamo::EQUAL_OPERATOR
            ));
    }
}
