<?php

namespace Worker\ExecuteBundle\Services;

use Jaguar\Action\Blur\BoxBlur;
use Jaguar\Action\Blur\GaussianBlur;
use Jaguar\Action\Blur\PartialBlur;
use Jaguar\Action\Blur\SelectiveBlur;
use Jaguar\Action\Color;
use Jaguar\Action\Flip;
use Jaguar\Action\GraySketchy;
use Jaguar\Action\Hipass;
use Jaguar\Action\Mirror;
use Jaguar\Action\Pixelate\Average;
use Jaguar\Action\Pixelate\Mosaic;
use Jaguar\Action\Posterize;
use Jaguar\Action\Sketchy;
use Jaguar\Action\Smooth;
use Jaguar\Action\Unsharpen;
use Jaguar\Action\Watermark;
use Jaguar\Canvas;
use Jaguar\Tests\Action\Color\AntiqueTest;
use Jaguar\Transformation;

/**
 * Created by PhpStorm.
 * User: george
 * Date: 12/20/2015
 * Time: 3:07 PM
 */
class TransformImages
{

    protected $originalPath;

    public function __construct($imagePath)
    {
        $this->originalPath = $imagePath;
    }

    public function applyAllFilters()
    {
        $images = array();
        foreach ($this->getFilters() as $name => $filter) {
            $canvas = new Canvas($this->originalPath);
            $transformation = new Transformation($canvas);
            $hash = md5(rand(999, 100000) . microtime() . "12345678910");
            $filename = $name . "_" . $hash . $canvas->getExtension();
            $transformation->apply($filter)
                ->getCanvas()
                ->save("processed/" . $filename);
            $imageAbsolutePath = realpath("./processed/" . $filename);
            $images[] = array(
                'id' => $name . "_" . $hash,
                'filename' => $filename,
                'absolutePath' => $imageAbsolutePath
            );

        }

        return $images;

    }

    protected function getFilters()
    {
        return array(
            'antique' => new Color\Antique(),
            'sepia' => new Color\Sepia(),
            'black-and-white' => new Color\BlackAndWhite(),
            'boost' => new Color\Boost(),
            'negate' => new Color\Negate(),
            'grayscale' => new Color\Grayscale(),
            'average' => new Average(),
            'mosaic' => new Mosaic(),
            'gaussian-blur' => new GaussianBlur(),
            'partial-blur' => new PartialBlur(),
            'box-blur' => new BoxBlur(),
            'selective-blur' => new SelectiveBlur(),
            'flip-orizontal' => new Flip(),
            'flip-vertical' => new Flip(Flip::FLIP_VERTICAL),
            'flip-both' => new Flip(Flip::FLIP_BOTH),
            'gray-sketchy' => new GraySketchy(),
            'hipass' => new Hipass(),
            'mirror' => new Mirror(),
            'smooth' => new Smooth(),
            'posterize' => new Posterize(),
            'unsharpen' => new Unsharpen(),
            'sketchy' => new Sketchy(),
        );
    }

}