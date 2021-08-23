<?php

declare(strict_types=1);

namespace App\Serializer;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProblemNormalizer implements NormalizerInterface
{
    protected $debug;
    public function __construct(bool $debug = false)
    {
        $this->debug = $debug;
    }
    
    public function normalize($object, string $format = null, array $context = []): array
    {
        $data = [
            'error' => $object->getMessage(),
            'debug' => $this->debug
        ];
        if ($this->debug) {
            $data['debug'] = [
                'class' => $object->getClass(),
                'trace' => $object->getTrace()
            ];
        }
        return $data;
    }
    
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof FlattenException;
    }
}