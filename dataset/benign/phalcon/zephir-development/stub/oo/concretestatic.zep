namespace Stub\Oo;

class ConcreteStatic extends AbstractStatic
{
	public static function parentFunction()
	{
		return __METHOD__;
	}

	public static function childFunction()
	{
		return self::parentFunction();
	}
}
