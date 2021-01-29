<?php

namespace Sirgrimorum\CrudGenerator;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
        $query = $this->getAnteriorQuery(null, false);
        if ($query->count() > 0) {
            $anterior = $query->orderBy("id", "desc")->first();
            $occurrencesData = $anterior->get("occurrences", false, $this->config)["data"];
            $num = data_get($occurrencesData, "num", 1) + 1;
            $anteriores = data_get($occurrencesData, "anteriores", []);
            $anterior->occurrences = json_encode([
                "num" => $num,
                "anteriores" => $anteriores,
            ]);
            $anterior->save();
            return $anterior;
        } else {
            $data = $this->processCurrentError();
            if (($anterior = $this->getAnterior($data)) !== false) {
                $dataAnterior = $this->processPreviousError($anterior);
                $num = data_get($dataAnterior, "occurrences.num", 1) + 1;
                $anteriores = data_get($dataAnterior, "occurrences.anteriores", []);
                $anteriorParaDiff = Arr::except($dataAnterior, ["occurrences", "trace", "request"]);
                $anteriorParaDiff['request'] = Arr::except(Arr::get($dataAnterior, 'request', []), ["cookie", "session"]);
                if (is_array($sessionAnterior = Arr::get($dataAnterior, 'request.session', []))) {
                    $anteriorParaDiff['request']['session'] = Arr::except($sessionAnterior, ["_token"]);
                } else {
                    $anteriorParaDiff['request']['session'] = $sessionAnterior;
                }
                $dataParaDiff = collect($data)->except("occurrences", "trace", "request")->all();
                $dataParaDiff['request'] = Arr::except(Arr::get($data, 'request', []), ["cookie", "session"]);
                if (is_array($sessionData = Arr::get($data, 'request.session', []))) {
                    $dataParaDiff['request']['session'] = Arr::except($sessionData, ["_token"]);
                } else {
                    $dataParaDiff['request']['session'] = $sessionData;
                }
                $diferencia = ErrorCatcher::array_diff_recursive($anteriorParaDiff, $dataParaDiff);
                if (count($diferencia) > 0) {
                    if (!in_array($diferencia, $anteriores)) {
                        $key = Carbon::now()->toIso8601String();
                        if (isset($anteriores[$key])) {
                            $key .= "_" . Str::random(3);
                        }
                        $anteriores[$key] = $diferencia;
                    }
                }
                $dataFinal = array_merge(Arr::except($dataAnterior, ["occurrences"]), ["occurrences" => [
                    "num" => $num,
                    "anteriores" => $anteriores,
                ]]);
                return CrudGenerator::saveObjeto($this->config, new Request($dataFinal), $anterior);
            } else {
                $data["occurrences"] = [
                    "num" => 1,
                    "anteriores" => [],
                ];
                return CrudGenerator::saveObjeto($this->config, new Request($data));
            }
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
            "reportar" => 1,
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
            "url", "file", "line", "type", "exception", "message", "reportar"
        ])->all();
        $devolver["type"] = json_decode($anterior->type);
        $devolver["trace"] = $anterior->get("trace", false, $this->config)["data"];
        $devolver["request"] = $anterior->get("request", false, $this->config)["data"];
        $devolver["occurrences"] = $anterior->get("occurrences", false, $this->config)["data"];
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
        $query = $this->getAnteriorQuery($data);
        if ($query->count() > 0) {
            $anterior = $query->orderBy("id", "desc")->first();
            return $anterior;
        }
        return false;
    }

    /**
     * Get the query of previously catched errors
     * 
     * @param array $data Optional The array with all the error data processed, if null (default) will take the data from the current exception
     * @param bool $reportar Optional The status field, null (default) not take it into account
     * @return Builder
     */
    private function getAnteriorQuery($data = null, $reportar = null)
    {
        if ($data == null) {
            $data = [
                "file" => $this->exception->getFile(),
                "line" => $this->exception->getLine(),
                "exception" => get_class($this->exception),
            ];
        }
        $query = Catchederror::where("file", $data["file"])
            ->where("line", $data["line"])
            ->where("exception", $data["exception"]);
        if ($reportar !== null) {
            $query = $query->where("reportar", $reportar == true ? "1" : "0");
        }
        return $query;
    }
}
