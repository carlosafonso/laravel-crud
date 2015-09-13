<?php
namespace Afonso\LvCrud\Responses;

interface ResponseBuilderInterface
{
	public function build($data, $code = 200);
}
