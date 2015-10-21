<?php
namespace Afonso\LvCrud\Responses;

use Illuminate\Http\Request;

use Afonso\LvCrud\Controllers\CrudController;

class ResponseBuilderFactory
{
	public static function forRequest(Request $request, CrudController $controller)
	{
		if (! $controller->supportsHtml()) {
			return new JsonResponseBuilder($request);
		}

		if (! $controller->supportsJson()) {
			return new HtmlResponseBuilder($request);
		}

		if ($request->wantsJson()) {
			return new JsonResponseBuilder($request);
		}
		return new HtmlResponseBuilder($request);
	}
}
