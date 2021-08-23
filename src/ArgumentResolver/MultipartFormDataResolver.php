<?php
declare(strict_types=1);

namespace App\ArgumentResolver;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * PHP's post handler doesn't parse data from content of type 'multipart/form-data' in requests other than POST (i.e. PUT).
 * So this is a workaround.
 * Used decode function from this commit:
 *  - Symfony Request fix https://github.com/symfony/symfony/pull/10381/commits/3702d7d64dbe689583427af0582e0386f13f3e33
 * Alternatives:
 *  - PECL package https://pecl.php.net/package/apfd
 */
class MultipartFormDataResolver implements ArgumentValueResolverInterface
{

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $contentType = $request->headers->get('CONTENT_TYPE');
        return 0 === strpos($contentType, 'multipart/form-data')
            && $request->isMethod('PUT')
            && $argument->getType() === Request::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {

        $request->request = $this->decodeMultiPartFormData($request);
        yield $request;
    }

    protected function decodeMultiPartFormData(Request $request): ParameterBag
    {
        /**
         * Key/value pairs of decoded form-data
         * @var array
         */
        $data = array();

        // Fetch content and determine boundary
        $rawData = $request->getContent();
        if ($rawData) {
            $boundary = substr($rawData, 0, strpos($rawData, "\r\n"));

            // Fetch and process each part
            $parts = array_slice(explode($boundary, $rawData), 1);
            foreach ($parts as $part) {
                // If this is the last part, break
                if ($part == "--\r\n") {
                    break;
                }

                // Separate content from headers
                $part = ltrim($part, "\r\n");
                list($rawHeaders, $content) = explode("\r\n\r\n", $part, 2);
                $content = substr($content, 0, strlen($content) - 2);

                // Parse the headers list
                $rawHeaders = explode("\r\n", $rawHeaders);
                $headers = array();
                foreach ($rawHeaders as $header) {
                    list($name, $value) = explode(':', $header, 2);
                    $headers[strtolower($name)] = ltrim($value, ' ');
                }

                // Parse the Content-Disposition to get the field name, etc.
                if (isset($headers['content-disposition'])) {
                    $filename = null;
                    preg_match(
                        '/^form-data; *name="([^"]+)"(?:; *filename="([^"]+)")?/',
                        $headers['content-disposition'],
                        $matches
                    );

                    $fieldName = $matches[1];
                    $fileName = (isset($matches[2]) ? $matches[2] : null);

                    // If we have no filename, save the data.
                    if ($fileName === null) {
                        $data[$fieldName] = $content;
                    }
                }
            }
        }

        return new ParameterBag($data);
    }
}