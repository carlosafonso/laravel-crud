<?php
namespace Afonso\LvCrud\Controllers;

use Evalua\Toolbox\Constants\HttpStatusCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Pluralizer;

use Afonso\LvCrud\Context;
use Afonso\LvCrud\Responses\ResponseBuilderFactory;

abstract class CrudController extends RootController
{
	/**
	 * The model instance related to
	 * this controller.
	 *
	 * @var	Afonso\LvCrud\BaseModel
	 */
	protected $model;

	/**
	 * The context of the current request.
	 *
	 * @var	Afonso\LvCrud\Context
	 */
	protected $ctx;

	/**
	 * The response builder used to generate
	 * content-type agnostic responses.
	 *
	 * @var	Afonso\LvCrud\ResponseBuilderInterface
	 */
	protected $response;

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
		$entities = $this->model->with($this->ctx->with())
			->paginate($this->ctx->pageSize());
		return $this->response->build($entities);
	}

	public function create()
	{
		return $this->response->build(null);
	}

	public function show($id)
	{
		$entity = $this->model->with($this->ctx->with())->find($id);
		if (! $entity) {
			return $this->response->build(['error' => 'not_found'], HttpStatusCodes::NOT_FOUND);
		}

		return $this->response->build($entity);
	}

	public function store(Request $request)
	{
		if ($this->model->isReadOnly()) {
			return $this->response->build(['error' => 'method_not_allowed'], HttpStatusCodes::METHOD_NOT_ALLOWED);
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
			return $this->response->build(['error' => 'unprocessable_entity'], HttpStatusCodes::UNPROCESSABLE_ENTITY);
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

		return $this->response->build($entity, HttpStatusCodes::CREATED);
	}

	public function edit($id)
	{
		$entity = $this->model->with($this->ctx->with())->find($id);
		if (! $entity) {
			return $this->response->build(['error' => 'not_found'], HttpStatusCodes::NOT_FOUND);
		}

		return $this->response->build($entity);
	}

	public function update(Request $request, $id)
	{
		if ($this->model->isReadOnly()) {
			return $this->response->build(['error' => 'method_not_allowed'], HttpStatusCodes::METHOD_NOT_ALLOWED);
		}

		$entity = $this->model->find($id);
		if (! $entity) {
			return $this->response->build(['error' => 'not_found'], HttpStatusCodes::NOT_FOUND);
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

		$validator = Validator::make($data, $this->model->getValidationRules());

		if ($validator->fails()) {
			return $this->response->build(['error' => 'unprocessable_entity'], HttpStatusCodes::UNPROCESSABLE_ENTITY);
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

		return $this->response->build($entity, HttpStatusCodes::OK);
	}

	public function destroy($id)
	{
		if ($this->model->isReadOnly()) {
			return $this->response->build(['error' => 'method_not_allowed'], HttpStatusCodes::METHOD_NOT_ALLOWED);
		}

		$entity = $this->model->find($id);
		if (! $entity) {
			return $this->response->build(['error' => 'not_found'], HttpStatusCodes::NOT_FOUND);
		}

		$entity->delete();

		return $this->response->build(null, HttpStatusCodes::NO_CONTENT);
	}
	/*
	 * End of CRUD methods
	 */

	/*
	 * Hooks
	 */

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

	/*
	 * End of hooks
	 */

	/**
	 * Returns an instance of the model
	 * related to this controller.
	 *
	 * @return	Illuminate\Database\Eloquent\Model
	 */
	public function getRelatedModel()
	{
		$class = $this->getRelatedModelClass();
		return new $class;
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
	 * Additionally, if the controller class
	 * is namespaced under 'Controllers' this
	 * method will automatically replace it
	 * with 'Models'.
	 *
	 * E.g., if the controller's classname
	 * is 'App\Controllers\FoosController' then
	 * this method will return
	 * 'App\Models\Foo'.
	 *
	 * @return	string
	 */
	public function getRelatedModelClass()
	{
		$fqController = explode('\\', static::class);
		$model = Pluralizer::singular(str_replace('Controller', '', end($fqController)));

		if (($ns = $this->getModelNamespace()) !== null) {
			return $ns . '\\' . $model;
		}

		$fqController[count($fqController) - 1] = $model;
		if (($idx = array_search('Controllers', $fqController))	!== false) {
			$fqController[$idx] = 'Models';
		}
		return implode('\\', $fqController);
	}

	/**
	 * Returns the specific namespace of the
	 * model related to this controller.
	 *
	 * Override this function if your model does
	 * not follow the namespacing conventions used
	 * by this controller.
	 *
	 * @return	string
	 */
	protected function getModelNamespace()
	{
		return null;
	}

	/**
	 * Returns whether this controller instance
	 * supports JSON responses.
	 *
	 * @return	boolean
	 */
	public function supportsJson()
	{
		return true;
	}

	/**
	 * Returns whether this controller instance
	 * supports HTML responses.
	 *
	 * @return	boolean
	 */
	public function supportsHtml()
	{
		return true;
	}
}
