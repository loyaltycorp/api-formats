<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\RequestEncoders;

use EoneoPay\ApiFormats\Interfaces\RequestEncoderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

abstract class AbstractRequestEncoder implements RequestEncoderInterface
{
    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * AbstractRequestEncoder constructor.
     *
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Decode request content to array.
     *
     * @return array
     *
     * @throws \RuntimeException
     * @throws \EoneoPay\Utils\Exceptions\InvalidXmlException
     */
    public function decode(): array
    {
        $content = $this->request->getBody()->getContents();

        if ('' === $content) {
            return [];
        }

        return $this->decodeRequestContent($content);
    }

    /**
     * Decode request content to array.
     *
     * @param string $content
     *
     * @return array
     */
    abstract protected function decodeRequestContent(string $content): array;

    /**
     * Returns HTTP Content-Type header value.
     *
     * @return string
     */
    abstract protected function getContentTypeHeader(): string;

    /**
     * Instantiate response.
     *
     * @param string $content
     * @param int|null $statusCode
     * @param array|null $headers
     *
     * @return ResponseInterface
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    protected function response(string $content, int $statusCode = null, array $headers = null): ResponseInterface
    {
        $stream = new Stream('php://temp', 'rb+');
        $stream->write($content);
        $stream->seek(0);

        return new Response($stream, $statusCode ?? 200, \array_merge($headers ?? [], [
            'Content-Type' => $this->getContentTypeHeader()
        ]));
    }
}
