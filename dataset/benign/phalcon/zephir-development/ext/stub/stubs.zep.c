
#ifdef HAVE_CONFIG_H
#include "../ext_config.h"
#endif

#include <php.h>
#include "../php_ext.h"
#include "../ext.h"

#include <Zend/zend_operators.h>
#include <Zend/zend_exceptions.h>
#include <Zend/zend_interfaces.h>

#include "kernel/main.h"
#include "kernel/object.h"
#include "kernel/operators.h"
#include "kernel/memory.h"


/**
 * Tests for IDE Stubs Generation
 */
/**
 * Sample Class Description
 */
ZEPHIR_INIT_CLASS(Stub_Stubs) {

	ZEPHIR_REGISTER_CLASS(Stub, Stubs, stub, stubs, stub_stubs_method_entry, 0);

	zend_declare_property_string(stub_stubs_ce, SL("propString"), "Zephir public", ZEND_ACC_PUBLIC);

	/** @var integer - declared in Zephir DocBlock */
	zend_declare_property_long(stub_stubs_ce, SL("propIntDeclared"), 100, ZEND_ACC_PUBLIC);

	zend_declare_property_string(stub_stubs_ce, SL("propStringProtected"), "Zephir protected", ZEND_ACC_PROTECTED);

	zend_declare_property_string(stub_stubs_ce, SL("propStringPrivate"), "Zephir private", ZEND_ACC_PRIVATE);

	zephir_declare_class_constant_long(stub_stubs_ce, SL("INTEGER_CONST"), 1);

	zephir_declare_class_constant_double(stub_stubs_ce, SL("DOUBLE_CONST"), 10.24);

	zephir_declare_class_constant_string(stub_stubs_ce, SL("STRING_CONST"), "Zephir");

	return SUCCESS;

}

/**
 * Sample Method Description with Return type -> string
 */
PHP_METHOD(Stub_Stubs, testDockBlockAndReturnType) {

	zval *this_ptr = getThis();


	RETURN_MEMBER(getThis(), "propString");

}

/**
 * Sample Method Description with Return type -> string
 *
 * @return string - declared in Zephir DocBlock
 */
PHP_METHOD(Stub_Stubs, testDocBlockAndReturnTypeDeclared) {

	zval *this_ptr = getThis();


	RETURN_MEMBER(getThis(), "propStringProtected");

}

/**
 * Method with input params, mixed & int
 * testMixedInputParamsDocBlock(intOrString, int number = 1)
 * int number - not declared into DocBlock
 *
 * @param int|string $intOrString - declared in Zephir DocBlock
 * @return int|null - declared in Zephir DocBlock
 */
PHP_METHOD(Stub_Stubs, testMixedInputParamsDocBlock) {

	zend_long number;
	zval *intOrString, intOrString_sub, *number_param = NULL;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&intOrString_sub);

	zephir_fetch_params_without_memory_grow(1, 1, &intOrString, &number_param);

	if (!number_param) {
		number = 1;
	} else {
		number = zephir_get_intval(number_param);
	}


	RETURN_LONG(number);

}

/**
 * Method with input params, mixed & int
 * testMixedInputParamsDocBlockDeclared(intOrString, int number = 1)
 * all params declared into DocBlock
 *
 * @param int|string $intOrString - declared in Zephir DocBlock
 * @param int $number - declared in Zephir DocBlock
 * @return int|null - declared in Zephir DocBlock
 */
PHP_METHOD(Stub_Stubs, testMixedInputParamsDocBlockDeclared) {

	zend_long number;
	zval *intOrString, intOrString_sub, *number_param = NULL;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&intOrString_sub);

	zephir_fetch_params_without_memory_grow(1, 1, &intOrString, &number_param);

	if (!number_param) {
		number = 1;
	} else {
		number = zephir_get_intval(number_param);
	}


	RETURN_LONG(number);

}

