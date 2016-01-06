<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cloud\AmazonBundle\Entity\Dynamo\PhotoItemBuilder;
use Cloud\AmazonBundle\Services\Dynamo;
use Cloud\AmazonBundle\Services\Queue;
use Cloud\AmazonBundle\Services\S3;
use Worker\ExecuteBundle\Services\ProcessQueue;

class RunWorkerCommand extends ContainerAwareCommand
{

    const TABLE_NAME = 'ImageProcessingDB';

    public function configure(){
        $this->
            setName('worker:run');
    }

    public function execute(InputInterface $input, OutputInterface $output){
        set_time_limit(3600);
        $s3Service = new S3();
        $queueService = new Queue();
        $dynamoService = new Dynamo(new PhotoItemBuilder(),self::TABLE_NAME);
        $processService = new ProcessQueue($queueService, $dynamoService, $s3Service);
        $processService->run();
    }

}