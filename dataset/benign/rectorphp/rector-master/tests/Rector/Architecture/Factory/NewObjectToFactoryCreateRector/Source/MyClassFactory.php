<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Source;

final class MyClassFactory
{
	public function create(string $argument): MyClass
	{
		return new MyClass($argument);
	}
}
