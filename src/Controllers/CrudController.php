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

		$this->response = ResponseBuilderFactory::forRequest(RequestFacade::instance());
	}

	/*
	 * CRUD methods
	 */
	public function index()
	{
		return $this->response->build($this->model->with($this->ctx->with())->get());
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
		$validator = Validator::make($data, $this->model->getValidationRules());

		if ($validator->fails()) {
			return $this->response->build(['error' => 'unprocessable_entity'], HttpStatusCodes::UNPROCESSABLE_ENTITY);
		}

		try {
			$inserted = $this->model->create($data);
			$entity = $this->model->with($this->ctx->with())->find($inserted->id);
		} catch (\Exception $e) {
			return $this->response->build(['error' => 'internal_error'], HttpStatusCodes::INTERNAL_SERVER_ERROR);
		}

		return $this->respone->build($entity, HttpStatusCodes::CREATED);
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
		$validator = Validator::make($data, $this->model->getValidationRules());

		if ($validator->fails()) {
			return $this->response->build(['error' => 'unprocessable_entity'], HttpStatusCodes::UNPROCESSABLE_ENTITY);
		}

		try {
			$entity->fill($data)->save();
			$entity = $this->model->with($this->ctx->with())->find($id);
		} catch (\Exception $e) {
			return $this->response->build(['error' => 'internal_error'], HttpStatusCodes::INTERNAL_SERVER_ERROR);
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

		try {
			$entity->delete();
		} catch (\Exception $e) {
			return $this->response->build(['error' => 'internal_error'], HttpStatusCodes::INTERNAL_SERVER_ERROR);
		}

		return $this->response->build(null, HttpStatusCodes::NO_CONTENT);
	}
	/*
	 * End of CRUD methods
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
}
