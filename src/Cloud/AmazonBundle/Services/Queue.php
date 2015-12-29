<?php
namespace Cloud\AmazonBundle\Services;

use Aws\Sqs\SqsClient;


class Queue
{

    protected $client = null;
    const QUEUE_URL = 'http://sqs.us-east-1.amazonaws.com/675072056297/ImageProcessingQueue';

    public function __construct()
    {
        $this->client = SqsClient::factory(
            array(
                'credentials' => array(
                    'key' => 'AKIAIBAN56SSVUGHIE7A',
                    'secret' => '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',
                ),
                'region' => 'us-east-1',
                'version' => 'latest'
            ));

    }

    public function sendMessage($messageBody = '')
    {
        $response = $this->client->sendMessage(
            array(
                'QueueUrl' => self::QUEUE_URL,
                'MessageBody' => $messageBody
            )
        );
        return $response;
    }

    public function getMessage()
    {
        $data = array();
        $result = $this->client->receiveMessage(array(
            'QueueUrl' => self::QUEUE_URL
        ));
        $resultArray = $result->toArray();
        if (!isset($resultArray['Messages'])) {
            return array();
        }
        foreach ($resultArray['Messages'] as $message) {
            $data[] = array('body' => $message['Body'], 'receipt' => $message['ReceiptHandle']);
        }
        return $data;
    }

    public function deleteMessage($receiptHandle)
    {
        $result = $this->client->deleteMessage(array(
            'QueueUrl' => self::QUEUE_URL,
            'ReceiptHandle' => $receiptHandle
        ));
        return $result->toArray();
    }

}