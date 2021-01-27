<?php

namespace Sirgrimorum\CrudGenerator;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Sirgrimorum\CrudGenerator\Models\Catchederror;
use Sirgrimorum\CrudGenerator\CrudGenerator;

class ErrorCatcher
{

    /**
     * The exception to catch
     * 
     * @var Throwable
     */
    private $exception;

    /**
     * The request available
     * 
     * @var Request
     */
    private $request;

    /**
     * The plan name.
     *
     * @var  string
     */
    protected $modelName = 'catchederror';

    /**
     * The maximum depth for args in trace.
     *
     * @var  int
     */
    protected $maxDepth;

    /**
     * The maximum traces to track for args in trace.
     *
     * @var  int
     */
    protected $maxTraces;

    /**
     * The plan config array.
     *
     * @var  array
     */
    protected $config;

    public function __construct(Throwable $exception, $maxTraces = 15, $maxDepth = 5)
    {
        $this->exception = $exception;
        $this->request = request();
        $this->config = CrudGenerator::getConfig($this->modelName);
        $this->maxDepth = $maxDepth;
        $this->maxTraces = $maxTraces;
    }

    private function getErrorType()
    {
        $calssesByType = [
            "web" => [
                "HttpExceptionInterface",
                "HttpException",
                "NotFoundHttpException",
                "SuspiciousOperationException"
            ],
            "api" => [],
            "job" => [],
            "aut" => [
                "AuthorizationException",
                "AccessDeniedHttpException",
                "AuthenticationException"
            ],
            "val" => [
                "TokenMismatchException",
                "ModelNotFoundException",
            ],
            "rou" => [
                "NotFoundHttpException",
                "SuspiciousOperationException",
                "HttpResponseException"
            ],
            "ata" => [
                "SuspiciousOperationException"
            ],
        ];
        $tipos = [];
        foreach ($calssesByType as $tipo => $clases) {
            if (in_array(class_basename(get_class($this->exception)), $clases)) {
                $tipos[] = $tipo;
            }
        }
        if ($this->request->expectsJson()) {
            array_push($tipos, "api");
        } elseif (!in_array("web", $tipos)) {
            array_push($tipos, "web");
        }
        return $tipos;
    }

    /**
     * Catch (save) an error
     * 
     * @param Throwable $exception The exception to catch
     * @return Catchederror The error catched
     */
    public static function catch(Throwable $exception)
    {
        $catcher = new ErrorCatcher($exception);
        return $catcher->save();
    }

    /**
     * Save the error
     * 
     * @return Catchederror The error catched
     */
    public function save()
    {
        $data = $this->processCurrentError();
        if (($anterior = $this->getAnterior($data)) !== false) {
            $dataAnterior = $this->processPreviousError($anterior);
            $num = data_get($dataAnterior, "occurrences.num", 1) + 1;
            $anteriores = data_get($dataAnterior, "occurrences.anteriores", []);
            $diferencia = ErrorCatcher::array_diff_recursive(collect($dataAnterior)->except("occurrences", "trace")->all(), collect($data)->except("occurrences", "trace")->all());
            if (count($diferencia) > 0) {
                if (!in_array($diferencia, $anteriores)) {
                    $key = Carbon::now()->toIso8601String();
                    if (isset($anteriores[$key])) {
                        $key .= "_" . Str::random(3);
                    }
                    $anteriores[$key] = $diferencia;
                }
            }
            $dataFinal = [
                "occurrences" => [
                    "num" => $num,
                    "anteriores" => $anteriores,
                ],
                "trace" => data_get($dataAnterior, "trace", []),
                "request" => data_get($dataAnterior, "request", []),
            ];
            return CrudGenerator::saveObjeto($this->config, new Request($dataFinal), $anterior);
        } else {
            $data["occurrences"] = [
                "num" => 1,
                "anteriores" => [],
            ];
            return CrudGenerator::saveObjeto($this->config, new Request($data));
        }
    }

    /**
     * Return an array with the things that array2 has different to $array1
     * 
     * @param array $array1
     * @param array $array2
     * 
     * @return array
     */
    private static function array_diff_recursive($array1, $array2)
    {
        $result = [];
        if (is_array($array2) && is_array($array1)) {
            foreach ($array2 as $key => $val) {
                if (isset($array1[$key])) {
                    if (is_array($val) && is_array($array1[$key])) {
                        $auxResult = ErrorCatcher::array_diff_recursive($array1[$key], $val);
                        if (count($auxResult) > 0) {
                            $result[$key] = $auxResult;
                        }
                    } elseif ($val != $array1[$key]) {
                        $result[$key] = $val;
                    }
                } else {
                    $result[$key] = $val;
                }
            }
        } elseif ($array2 != $array1) {
            return $array2;
        }
        return $result;
    }

    /**
     * Get an array with the processed data of the current Throwable error
     * 
     * @return array
     */
    private function processCurrentError()
    {
        $traces = [];
        foreach ($this->exception->getTrace() as $key => $trace) {
            $args = $this->getArgs(Arr::get($trace, "args", []), 0);
            $newTrace = Arr::except($trace, ["args"]);
            $newTrace["args"] = $args;
            $traces[$key] = $newTrace;
            if (count($traces) >= $this->maxTraces) {
                break;
            }
        }
        $session = "Not set";
        if ($this->request->hasSession()) {
            $session = $this->request->session()->all();
        }
        return [
            "url" => $this->request->url(),
            "file" => $this->exception->getFile(),
            "line" => $this->exception->getLine(),
            "type" => $this->getErrorType(),
            "exception" => get_class($this->exception),
            "message" => $this->exception->getMessage(),
            "trace" => $traces,
            "request" => [
                "path" => $this->request->path(),
                "query" => $this->request->query(),
                "method" => $this->request->method(),
                "data" => $this->request->all(),
                "session" => $session,
                "cookie" => $this->request->cookie(),
                "ip" => $this->request->ip(),
                "header" => collect($this->request->header())->only([
                    "user-agent",
                    "referer",
                    "accept-language",
                ])->all(),
                "accepts" => $this->request->getAcceptableContentTypes(),
                "server" => collect($this->request->server())->only([
                    "REDIRECT_STATUS",
                    "DOCUMENT_ROOT",
                    "REQUEST_SCHEME",
                    "REDIRECT_URL",
                    "REDIRECT_QUERY_STRING"
                ])->all(),
            ],
        ];
    }

    private function getArgs($arg, $depth)
    {
        if (is_int($arg) || is_string($arg)) {
            return $arg;
        } elseif ($arg === true || $arg === false || $arg === null) {
            return $arg;
        } elseif (is_array($arg) && $depth <= $this->maxDepth) {
            $args = [];
            foreach ($arg as $key => $newArg) {
                $args[$key] = $this->getArgs($newArg, $depth + 1);
            }
            return $args;
        } elseif (is_object($arg)) {
            if ($arg instanceof Request && $depth <= $this->maxDepth) {
                $args = [];
                foreach ($arg->all() as $key => $newArg) {
                    $args[$key] = $this->getArgs($newArg, $depth + 1);
                }
                return $args;
            } elseif ($arg instanceof Model && $depth <= $this->maxDepth) {
                $args = [];
                foreach ($arg->toArray() as $key => $newArg) {
                    $args[$key] = $this->getArgs($newArg, $depth + 1);
                }
                return $args;
            } else {
                return get_class($arg);
            }
        } else {
            return gettype($arg);
        }
    }

    /**
     * Get an array with the processed data of a previous Catchederror
     * 
     * @return array
     */
    private function processPreviousError(Catchederror $anterior)
    {
        $devolver = collect($anterior->toArray())->only([
            "url", "file", "line", "type", "exception", "message"
        ])->all();
        $devolver["type"] = json_decode($anterior->type);
        $devolver["trace"] = $anterior->get("trace", false)["data"];
        $devolver["request"] = $anterior->get("request", false)["data"];
        $devolver["occurrences"] = $anterior->get("occurrences", false)["data"];
        return $devolver;
    }

    /**
     * Get a previously catched error
     * 
     * @param array $data The array with all the error data processed
     * @return Catchederror|bool The previously Catched Error or false if none found
     */
    private function getAnterior($data)
    {
        $query = Catchederror::where("file", $data["file"])
            ->where("line", $data["line"])
            ->where("exception", $data["exception"]);
        if ($query->count() > 0) {
            $anterior = $query->orderBy("id", "desc")->first();
            return $anterior;
        }
        return false;
    }
}
