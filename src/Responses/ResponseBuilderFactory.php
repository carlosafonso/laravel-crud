<?php
namespace Afonso\LvCrud\Responses;

use Illuminate\Http\Request;

class ResponseBuilderFactory
{
	public static function forRequest(Request $request)
	{
		if ($request->wantsJson()) {
			return new JsonResponseBuilder($request);
		}
		return new HtmlResponseBuilder($request);
	}
}
