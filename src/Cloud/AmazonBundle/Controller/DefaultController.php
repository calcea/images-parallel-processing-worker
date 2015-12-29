<?php

namespace Cloud\AmazonBundle\Controller;

use Cloud\AmazonBundle\Entity\Dynamo\PhotoItemBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Cloud\AmazonBundle\Services;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $obj = new Services\Queue();
//        $sendersObj = $obj->sendMessage("AWS");
        $messages = $obj->getMessage();
//        dump($messages);
//        die();
        if (isset($messages[0]['receipt'])) {
            $deleted = $obj->deleteMessage($messages[0]['receipt']);
            dump($deleted);
            die('asd');
        }
        return $this->render('CloudAmazonBundle:Default:index.html.twig');
    }

    public function testAction()
    {
        $items = array(
            0 => array(
                'user_id' => '433',
                'photo_id' => '65',
                'path_to_s3' => 'http://amazon.s3/img.jpg',
                'filename' => 'img.jpg',
            ),
            1 => array(
                'user_id' => '9129',
                'photo_id' => '90',
                'filename' => 'img1.jpg',
                'parent' => '5',
                'status' => '4'
            )
        );
        $filters = array(
            array(
                'columnName' => 'UserID',
                'value' => '99',
                'operator' => Services\Dynamo::EQUAL_OPERATOR
            ),
            array(
                'columnName' => 'PhotoID',
                'value' => '90',
                'operator' => Services\Dynamo::EQUAL_OPERATOR
            ));
        $dataToUpdate = array('Status' => '2');
        $builder = new PhotoItemBuilder();
        $obj = new Services\Dynamo($builder, "ImageProcessingDB");
        $obj->updatePhotoItem('433', '65', $dataToUpdate);
//        $obj->getItems($filters);
//        $obj->addItem($items[0]);
        die;
        return $this->render('CloudAmazonBundle:Default:index.html.twig');
    }
}
