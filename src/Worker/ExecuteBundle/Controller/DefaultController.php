<?php

namespace Worker\ExecuteBundle\Controller;

use Cloud\AmazonBundle\Entity\Dynamo\PhotoItemBuilder;
use Cloud\AmazonBundle\Services\Dynamo;
use Cloud\AmazonBundle\Services\Queue;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Worker\ExecuteBundle\Services\ProcessQueue;
use Worker\ExecuteBundle\Services\TransformImages;

class DefaultController extends Controller
{
    const TABLE_NAME = 'ImageProcessingDB';

    public function indexAction()
    {
        return $this->render('WorkerExecuteBundle:Default:index.html.twig');
    }

    public function filtersAction()
    {
        $imagesService = new TransformImages("processed/11.jpg");
        $images = $imagesService->applyAllFilters();
        dump($images);
        die;
        return $this->render('WorkerExecuteBundle:Default:index.html.twig');
    }

    /**
     * @return JsonResponse
     */
    public function runAction()
    {
        $queueService = new Queue();
        $dynamoService = new Dynamo(new PhotoItemBuilder(), self::TABLE_NAME);
        $processService = new ProcessQueue($queueService, $dynamoService);
        $processService->run();
        return new JsonResponse(array('done' => true));
    }
}
