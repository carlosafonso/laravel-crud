<?php
namespace Afonso\LvCrud\Controllers;

use Afonso\LvCrud\Context;
use Afonso\LvCrud\Responses\ResponseBuilderFactory;
use Afonso\LvCrud\Models\CrudModelInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Pluralizer;
use RuntimeException;

abstract class CrudController extends BaseController
{
    /**
     * The model instance related to
     * this controller.
     *
     * @var Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * The context of the current request.
     *
     * @var Afonso\LvCrud\Context
     */
    protected $ctx;

    /**
     * The response builder used to generate
     * content-type agnostic responses.
     *
     * @var Afonso\LvCrud\ResponseBuilderInterface
     */
    protected $response;

    /**
     * Whether this controller is read-only.
     *
     * Read-only controllers don't allow POSTs,
     * PUTs nor DELETEs.
     *
     * @var bool
     */
    protected $readOnly = false;

    public function __construct()
    {
        $this->model = $this->getRelatedModel();

        $this->ctx = new Context(RequestFacade::instance());

        $this->response = ResponseBuilderFactory::forRequest(RequestFacade::instance(), $this);
    }

    /*
     * CRUD methods
     */
    public function index()
    {
        $entities = $this->modifyIndexQuery($this->model->newQuery())
            ->with($this->ctx->with())
            ->paginate($this->ctx->pageSize());
        return $this->response->build($entities);
    }

    public function create()
    {
        return $this->response->build(null);
    }

    public function show($id)
    {
        $entity = $this->modifyShowQuery($this->model->newQuery())
            ->with($this->ctx->with())
            ->find($id);

        if (! $entity) {
            return $this->response->build(['error' => 'not_found'], SymfonyResponse::HTTP_NOT_FOUND);
        }

        // hook: afterShow()
        if ($hookResult = $this->afterShow($entity)) {
            return $hookResult;
        }

        return $this->response->build($entity);
    }

    public function store(Request $request)
    {
        if ($this->readOnly) {
            return $this->response->build(['error' => 'method_not_allowed'], SymfonyResponse::HTTP_METHOD_NOT_ALLOWED);
        }

        $data = $request->all();

        // hook: beforeValidate()
        if ($hookResult = $this->beforeValidate($request, $data)) {
            return $hookResult;
        }

        // hook: beforeStoreValidate()
        if ($hookResult = $this->beforeStoreValidate($request, $data)) {
            return $hookResult;
        }

        $validator = Validator::make($data, $this->model->getValidationRules());

        if ($validator->fails()) {
            return $this->response->build(
                ['error' => 'unprocessable_entity', 'validation_errors' => $validator->errors()->all()],
                SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // hook: beforeInsert()
        if ($hookResult = $this->beforeInsert($request, $data)) {
            return $hookResult;
        }

        $inserted = $this->model->create($data);
        $entity = $this->model->with($this->ctx->with())->find($inserted->id);

        // hook: afterInsert()
        if ($hookResult = $this->afterInsert($request, $entity)) {
            return $hookResult;
        }

        return $this->response->build($entity, SymfonyResponse::HTTP_CREATED);
    }

    public function edit($id)
    {
        $entity = $this->model->with($this->ctx->with())->find($id);
        if (! $entity) {
            return $this->response->build(['error' => 'not_found'], SymfonyResponse::HTTP_NOT_FOUND);
        }

        return $this->response->build($entity);
    }

    public function update(Request $request, $id)
    {
        if ($this->readOnly) {
            return $this->response->build(['error' => 'method_not_allowed'], SymfonyResponse::HTTP_METHOD_NOT_ALLOWED);
        }

        $entity = $this->model->find($id);
        if (! $entity) {
            return $this->response->build(['error' => 'not_found'], SymfonyResponse::HTTP_NOT_FOUND);
        }

        $data = $request->all();

        // hook: beforeValidate()
        if ($hookResult = $this->beforeValidate($request, $data)) {
            return $hookResult;
        }

        // hook: beforeUpdateValidate()
        if ($hookResult = $this->beforeUpdateValidate($request, $id, $data)) {
            return $hookResult;
        }

        $validator = Validator::make($data, $this->model->getValidationRules($entity->id));

        if ($validator->fails()) {
            return $this->response->build(
                ['error' => 'unprocessable_entity', 'validation_errors' => $validator->errors()->all()],
                SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // hook: beforeUpdate()
        if ($hookResult = $this->beforeUpdate($request, $data, $id)) {
            return $hookResult;
        }

        $entity->fill($data)->save();
        $entity = $this->model->with($this->ctx->with())->find($id);

        // hook: afterUpdate()
        if ($hookResult = $this->afterUpdate($request, $entity)) {
            return $hookResult;
        }

        return $this->response->build($entity, SymfonyResponse::HTTP_OK);
    }

    public function destroy($id)
    {
        if ($this->readOnly) {
            return $this->response->build(['error' => 'method_not_allowed'], SymfonyResponse::HTTP_METHOD_NOT_ALLOWED);
        }

        $entity = $this->model->find($id);
        if (! $entity) {
            return $this->response->build(['error' => 'not_found'], SymfonyResponse::HTTP_NOT_FOUND);
        }

        $entity->delete();

        return $this->response->build(null, SymfonyResponse::HTTP_NO_CONTENT);
    }
    /*
     * End of CRUD methods
     */

    /*
     * Hooks
     */

    /**
     * This hook runs after fetching a single
     * entity, as long as it exists.
     *
     * Returning a non-null value will cause the
     * controller method to automatically return
     * with that value.
     */
    public function afterShow($entity)
    {
        //
    }

    /**
     * This hook runs before validation occurs
     * both on insertions an updates.
     *
     * Returning a non-null value will cause the
     * controller method to automatically return
     * with that value.
     */
    public function beforeValidate(Request $request, &$data)
    {
        //
    }

    /**
     * This hook runs before validation occurs
     * on insertions, but after the beforeValidate()
     * hook has run.
     *
     * Returning a non-null value will cause the
     * controller method to automatically return
     * with that value.
     */
    public function beforeStoreValidate(Request $request, &$data)
    {
        //
    }

    /**
     * This hook runs before validation occurs
     * on updates, but after the beforeValidate()
     * hook has run.
     *
     * Returning a non-null value will cause the
     * controller method to automatically return
     * with that value.
     */
    public function beforeUpdateValidate(Request $request, $id, &$data)
    {
        //
    }

    /**
     * This hook runs before insertion occurs, but
     * after validation has passed.
     *
     * Returning a non-null value will cause the
     * controller method to automatically return
     * with that value.
     */
    public function beforeInsert(Request $request, &$data)
    {
        //
    }

    /**
     * This hook runs after insertion occurs, as long
     * as it succeeds.
     */
    public function afterInsert(Request $request, $entity)
    {
        //
    }

    /**
     * This hook runs before updating occurs, but
     * after validation has passed.
     *
     * Returning a non-null value will cause the
     * controller method to automatically return
     * with that value.
     */
    public function beforeUpdate(Request $request, &$data, $id)
    {
        //
    }

    /**
     * This hook runs after updating occurs, as long
     * as it succeeds.
     */
    public function afterUpdate(Request $request, $entity)
    {
        //
    }

    /**
     * Returns a new query builder instance,
     * optionally based on the instance received
     * as a parameter, to be used in the query
     * that fetches a list of resources (index).
     */
    public function modifyIndexQuery(Builder $q)
    {
        return $q;
    }

    /**
     * Returns a new query builder instance,
     * optionally based on the instance received
     * as a parameter, to be used in the query
     * that fetches a single resources (show).
     */
    public function modifyShowQuery(Builder $q)
    {
        return $q;
    }

    /*
     * End of hooks
     */

    /**
     * Returns an instance of the model
     * related to this controller. If the model
     * does not implement the CrudModelInterface
     * it will rise an exception.
     *
     * @throws RuntimeException if the related model does
     * not implement the CrudModelInterface.
     *
     * @return  Illuminate\Database\Eloquent\Model
     */
    public function getRelatedModel()
    {
        $class = $this->getRelatedModelClass();
        $instance = new $class;
        if ($instance instanceof CrudModelInterface) {
            return $instance;
        }

        throw new RuntimeException("The related model must implement CrudModelInterface");
    }

    /**
     * Returns the fully qualified class
     * of the model associated with this
     * controller.
     *
     * This method assumes that the model
     * class is the singularized form of
     * the controller's name minus the
     * 'Controller' suffix.
     *
     * It will also assume that the model class
     * is namespaced under 'App', unless specified
     * otherwise. A different namespace can
     * be set by overriding getModelNamespace().
     *
     * @return  string
     */
    public function getRelatedModelClass()
    {
        $fqController = explode('\\', static::class);
        $model = Pluralizer::singular(str_replace('Controller', '', end($fqController)));

        $ns = $this->getModelNamespace() ? : 'App';
        return $ns . '\\' . $model;
    }

    /**
     * Returns the specific namespace of the
     * model related to this controller.
     *
     * Override this function if your model does
     * not follow the namespacing conventions used
     * by this controller.
     *
     * @return  string
     */
    protected function getModelNamespace()
    {
        return null;
    }

    /**
     * Returns whether this controller instance
     * supports JSON responses.
     *
     * @return  boolean
     */
    public function supportsJson()
    {
        return true;
    }

    /**
     * Returns whether this controller instance
     * supports HTML responses.
     *
     * @return  boolean
     */
    public function supportsHtml()
    {
        return true;
    }
}
