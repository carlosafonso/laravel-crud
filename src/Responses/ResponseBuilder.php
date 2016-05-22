<?php
namespace Afonso\LvCrud\Responses;

use Illuminate\Http\Request;

abstract class ResponseBuilder implements ResponseBuilderInterface
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
