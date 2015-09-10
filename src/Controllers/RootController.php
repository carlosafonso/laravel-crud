<?php
namespace Afonso\LvCrud\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class RootController extends BaseController
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
