<?php
namespace Afonso\LvCrud\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

abstract class BaseModel extends EloquentModel
{
	/**
	 * Returns whether this model is
	 * read-only.
	 *
	 * Read-only models cannot be created,
	 * updated or destroyed.
	 *
	 * @return	boolean
	 */
	public function isReadOnly()
	{
		return false;
	}
}
