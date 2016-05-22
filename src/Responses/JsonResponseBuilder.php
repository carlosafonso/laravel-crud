<?php
namespace Afonso\LvCrud\Responses;

use Illuminate\Support\Facades\Response;

class JsonResponseBuilder extends ResponseBuilder /*implements ResponseBuilderInterface*/
{
    public function build($data, $code = 200)
    {
        return Response::json($data, $code);
    }
}
