<?php

namespace Sirgrimorum\CrudGenerator;

use Carbon\Carbon;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Sirgrimorum\CrudGenerator\Models\CatchedError;

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
    protected $modelName = 'catchedError';

    /**
     * The plan config array.
     *
     * @var  array
     */
    protected $config;

    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
        $this->request = request();
        $this->config = CrudGenerator::getConfig($this->modelName);
    }

    private function getErrorType()
    {
        $calssesByType = collect([
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
        ]);
        $tipos = $calssesByType->map(function ($item, $key) {
            if (in_array(class_basename(get_class($this->exception)), $item)) {
                return $key;
            }
            return null;
        })->all();
        if ($this->request->expectsJson()) {
            array_push($tipos, "api");
        } elseif (!isset($tipos["web"])) {
            array_push($tipos, "web");
        }
        return $tipos;
    }

    /**
     * Catch (save) an error
     * 
     * @param Throwable $exception The exception to catch
     * @return CatchedError The error catched
     */
    public static function catch(Throwable $exception){
        $catcher = new ErrorCatcher($exception);
        return $catcher->save();
    }

    /**
     * Save the error
     * 
     * @return CatchedError The error catched
     */
    public function save()
    {
        $data = $this->processCurrentError();
        if (($anterior = $this->getAnterior($data)) !== false) {
            $dataAnterior = $this->processPreviousError($anterior);
            $num = data_get($dataAnterior, "occurrences.num", 1) + 1;
            $anteriores = data_get($dataAnterior, "occurrences.anteriores", []);
            $dataAnterior = collect($dataAnterior)->except("occurrences");
            $diferencia = $dataAnterior->diff($data)->all();
            if (!in_array($diferencia, $anteriores)) {
                $key = Carbon::now()->toIso8601String();
                if (isset($anteriores[$key])) {
                    $key .= "_" . Str::random(3);
                }
                $anteriores[$key] = $diferencia;
            }
            $dataFinal["occurrences"] = [
                "num" => $num,
                "anteriores" => $anteriores,
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
     * Get an array with the processed data of the current Throwable error
     * 
     * @return array
     */
    private function processCurrentError(){
        return [
            "url" => $this->request->url(),
            "file" => $this->exception->getFile(),
            "line" => $this->exception->getLine(),
            "type" => $this->getErrorType(),
            "exception" => get_class($this->exception),
            "message" => $this->exception->getMessage(),
            "trace" => $this->exception->getTrace(),
            "request" => [
                "path" => $this->request->path(),
                "query" => $this->reqeust->query(),
                "method" => $this->request->method(),
                "data" => $this->request->all(),
                "session" => $this->request->session()->all(),
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

    /**
     * Get an array with the processed data of a previous CatchedError
     * 
     * @return array
     */
    private function processPreviousError(CatchedError $anterior){
        $devolver = collect($anterior->toArray())->only([
            "ulr", "file", "line", "type", "exception", "message"
        ])->all();
        $devolver["trace"] = $anterior->get("trace", false)["data"];
        $devolver["request"] = $anterior->get("request", false)["data"];
        $devolver["occurrences"] = $anterior->get("occurrences", false)["data"];
        return $devolver;
    }

    /**
     * Get a previously catched error
     * 
     * @param array $data The array with all the error data processed
     * @return CatchedError|bool The previously Catched Error or false if none found
     */
    private function getAnterior($data)
    {
        $query = CatchedError::where("file", $data["file"])
            ->where("line", $data["line"])
            ->where("exception", $data["exception"]);
        if ($query->count() > 0) {
            $anterior = $query->orderBy("id","desc")->first();
            return $anterior;
        }
        return false;
    }
}
