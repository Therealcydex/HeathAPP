<?php
namespace GuzzleHttp\Handler;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\RequestInterface;

/**
 * Creates curl resources from a request
 */
class CurlFactory implements CurlFactoryInterface
{
    /** @var array */
    private $handles = []; // Initialisation à un tableau vide

    /** @var int Total number of idle handles to keep in cache */
    private $maxHandles;

    /**
     * @param int $maxHandles Maximum number of idle handles.
     */
    public function __construct($maxHandles)
    {
        $this->maxHandles = $maxHandles;
        $this->handles = []; // Assurer que handles est bien un tableau
    }

    public function create(RequestInterface $request, array $options)
    {
        if (isset($options['curl']['body_as_string'])) {
            $options['_body_as_string'] = $options['curl']['body_as_string'];
            unset($options['curl']['body_as_string']);
        }

        $easy = new EasyHandle;
        $easy->request = $request;
        $easy->options = $options;
        $conf = $this->getDefaultConf($easy);
        $this->applyMethod($easy, $conf);
        $this->applyHandlerOptions($easy, $conf);
        $this->applyHeaders($easy, $conf);
        unset($conf['_headers']);

        // Add handler options from the request configuration options
        if (isset($options['curl'])) {
            $conf = array_replace($conf, $options['curl']);
        }

        $conf[CURLOPT_HEADERFUNCTION] = $this->createHeaderFn($easy);
        $easy->handle = !empty($this->handles)
            ? array_pop($this->handles)
            : curl_init();
        curl_setopt_array($easy->handle, $conf);

        return $easy;
    }

    public function release(EasyHandle $easy)
    {
        $resource = $easy->handle;
        unset($easy->handle);

        if (is_array($this->handles) && count($this->handles) >= $this->maxHandles) {
            curl_close($resource);
        } else {
            // Remove all callback functions as they can hold onto references
            curl_setopt($resource, CURLOPT_HEADERFUNCTION, null);
            curl_setopt($resource, CURLOPT_READFUNCTION, null);
            curl_setopt($resource, CURLOPT_WRITEFUNCTION, null);
            curl_setopt($resource, CURLOPT_PROGRESSFUNCTION, null);
            curl_reset($resource);
            $this->handles[] = $resource;
        }
    }

    public static function finish(
        callable $handler,
        EasyHandle $easy,
        CurlFactoryInterface $factory
    ) {
        if (isset($easy->options['on_stats'])) {
            self::invokeStats($easy);
        }

        if (!$easy->response || $easy->errno) {
            return self::finishError($handler, $easy, $factory);
        }

        $factory->release($easy);

        $body = $easy->response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        return new FulfilledPromise($easy->response);
    }

    private static function invokeStats(EasyHandle $easy)
    {
        $curlStats = curl_getinfo($easy->handle);
        $stats = new TransferStats(
            $easy->request,
            $easy->response,
            $curlStats['total_time'],
            $easy->errno,
            $curlStats
        );
        call_user_func($easy->options['on_stats'], $stats);
    }

    private static function finishError(
        callable $handler,
        EasyHandle $easy,
        CurlFactoryInterface $factory
    ) {
        $ctx = [
            'errno' => $easy->errno,
            'error' => curl_error($easy->handle),
        ] + curl_getinfo($easy->handle);
        $factory->release($easy);

        if (empty($easy->options['_err_message'])
            && (!$easy->errno || $easy->errno == 65)
        ) {
            return self::retryFailedRewind($handler, $easy, $ctx);
        }

        return self::createRejection($easy, $ctx);
    }

    private static function createRejection(EasyHandle $easy, array $ctx)
    {
        static $connectionErrors = [
            CURLE_OPERATION_TIMEOUTED  => true,
            CURLE_COULDNT_RESOLVE_HOST => true,
            CURLE_COULDNT_CONNECT      => true,
            CURLE_SSL_CONNECT_ERROR    => true,
            CURLE_GOT_NOTHING          => true,
        ];

        if ($easy->onHeadersException) {
            return new RejectedPromise(
                new RequestException(
                    'An error was encountered during the on_headers event',
                    $easy->request,
                    $easy->response,
                    $easy->onHeadersException,
                    $ctx
                )
            );
        }

        $message = sprintf(
            'cURL error %s: %s (%s)',
            $ctx['errno'],
            $ctx['error'],
            'see http://curl.haxx.se/libcurl/c/libcurl-errors.html'
        );

        $error = isset($connectionErrors[$easy->errno])
            ? new ConnectException($message, $easy->request, null, $ctx)
            : new RequestException($message, $easy->request, $easy->response, null, $ctx);

        return new RejectedPromise($error);
    }

    private function getDefaultConf(EasyHandle $easy)
    {
        return [
            '_headers'             => $easy->request->getHeaders(),
            CURLOPT_CUSTOMREQUEST  => $easy->request->getMethod(),
            CURLOPT_URL            => (string) $easy->request->getUri(),
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HEADER         => false,
            CURLOPT_CONNECTTIMEOUT => 150,
            CURLOPT_HTTP_VERSION   => $this->getHttpVersion($easy),
        ];
    }

    private function getHttpVersion(EasyHandle $easy)
    {
        $version = $easy->request->getProtocolVersion();
        return $version == 1.1 ? CURL_HTTP_VERSION_1_1 :
               ($version == 2.0 ? CURL_HTTP_VERSION_2_0 : CURL_HTTP_VERSION_1_0);
    }

    private function applyHeaders(EasyHandle $easy, array &$conf)
    {
        foreach ($conf['_headers'] as $name => $values) {
            foreach ($values as $value) {
                $conf[CURLOPT_HTTPHEADER][] = "$name: $value";
            }
        }

        if (!$easy->request->hasHeader('Accept')) {
            $conf[CURLOPT_HTTPHEADER][] = 'Accept:';
        }
    }

    private function createHeaderFn(EasyHandle $easy)
    {
        return function ($ch, $h) use ($easy, &$startingResponse) {
            $value = trim($h);
            if ($value === '') {
                $startingResponse = true;
                $easy->createResponse();
            } elseif ($startingResponse) {
                $startingResponse = false;
                $easy->headers = [$value];
            } else {
                $easy->headers[] = $value;
            }
            return strlen($h);
        };
    }
}
