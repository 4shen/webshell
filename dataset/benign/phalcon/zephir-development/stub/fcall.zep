
/**
 * Function calls
 *
 * Test global functions declaration.
 */

function zephir_global_method_test(var str)
{
	/**
	 * Simple comment
	 */
	return new Fcall()->testStrtokVarBySlash(str);
}

function zephir_global_method_with_type_casting(<\stdClass> variable)
{
	return variable;
}

namespace Stub;

use Stub\Oo\PropertyAccess;

/**
 * Test function declaration in namespace
 */
function zephir_namespaced_method_test(var str)
{
	return new Fcall()->testCall5(str, 5);
}

function test_call_relative_object_hint(<PropertyAccess> a) -> boolean
{
	return true;
}

function zephir_namespaced_method_with_type_casting(<\stdClass> variable)
{
	return variable;
}

function test_call_object_hint(<\Stub\Oo\PropertyAccess> a) -> boolean
{
	return true;
}

class Fcall
{
	public function testCall1() -> int
	{
		return strpos("hello", "h");
	}

	public function testCall2() -> int
	{
		loop {
			return mt_rand(0, 100);
		}
	}

	public function testCall3()
	{
		var handle, handle2, buffer;

		let handle = fopen("inputfile.txt", "r"), handle2 = fopen("outputfile.txt", "w");
		if handle {
			loop {
				let buffer = fgets(handle, 4096);
				if buffer === false {
					break;
				}
				fwrite(handle2, buffer);
			}
			fclose(handle);
			fclose(handle2);
		}
	}

	public function testCall4()
	{
		var handle, handle2, buffer;

		let handle = fopen(mode: "r", filename: "inputfile.txt"),
			handle2 = fopen(filename: "outputfile.txt", mode: "w");
		if handle {
			loop {
				let buffer = fgets(handle, 4096);
				if buffer === false {
					break;
				}
				fwrite(handle2, buffer);
			}
			fclose(handle);
			fclose(handle2);
		}
	}

	public function testCall5(var a, var b)
	{
		return str_repeat(a, b);
	}

	public function testCall6()
	{
		return rand();
	}

	public function testCall7()
	{
		memory_get_usage();
	}

	public function zvalFcallWith1Parameter(var callback, var param1 = null)
	{
		{callback}(param1);
	}

	public function testCall8(var a, var b)
	{
		var x;
		let x = str_repeat(a, b);
		return x;
	}

	public function testCall1FromVar() -> int
	{
		var funcName;
		let funcName = "strpos";
		return {funcName}("hello", "l");
	}

	public function testStrtokFalse() -> bool
	{
		return strtok("/");
	}

	public function testStrtokVarBySlash(var value) -> string
	{
		return strtok(value, "/");
	}

	public function testFunctionGetArgs(var param1, var param2) -> array
	{
		return func_get_args();
	}

	public function testFunctionGetArgsAllExtra() -> array
	{
		return func_get_args();
	}

	public static function testStaticFunctionGetArgsAllExtra() -> array
	{
		return func_get_args();
	}

	public function testFunctionGetArg(var param1, var param2) -> array
	{
		return [func_get_arg(0), func_get_arg(1)];
	}

	public function testFunctionGetArgAllExtra() -> array
	{
		return [func_get_arg(0), func_get_arg(1)];
	}

	public static function testStaticFunctionGetArgAllExtra() -> array
	{
		return [func_get_arg(0), func_get_arg(1)];
	}

	public function testArrayFill() -> array
	{
		var v1, v2;
		let v1 = array_fill(0, 5, "?");
		let v2 = array_fill(0, 6, "?");
		return [v1, v2];
	}
}
