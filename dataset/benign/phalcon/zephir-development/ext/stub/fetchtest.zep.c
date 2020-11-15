
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
#include "kernel/memory.h"
#include "kernel/array.h"
#include "kernel/operators.h"


/**
 * Fetch statement tests
 */
ZEPHIR_INIT_CLASS(Stub_FetchTest) {

	ZEPHIR_REGISTER_CLASS(Stub, FetchTest, stub, fetchtest, stub_fetchtest_method_entry, 0);

	zend_declare_property_null(stub_fetchtest_ce, SL("values"), ZEND_ACC_PROTECTED);

	return SUCCESS;

}

PHP_METHOD(Stub_FetchTest, setValues) {

	zval *values, values_sub;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&values_sub);

	zephir_fetch_params_without_memory_grow(1, 0, &values);



	zephir_update_property_zval(this_ptr, ZEND_STRL("values"), values);
	RETURN_THISW();

}

PHP_METHOD(Stub_FetchTest, getValues) {

	zval *this_ptr = getThis();


	RETURN_MEMBER(getThis(), "values");

}

PHP_METHOD(Stub_FetchTest, testFetchArray1) {

	zval *a, a_sub, *b, b_sub, c;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&a_sub);
	ZVAL_UNDEF(&b_sub);
	ZVAL_UNDEF(&c);

	zephir_fetch_params_without_memory_grow(2, 0, &a, &b);



	RETURN_BOOL(zephir_array_isset_fetch(&c, a, b, 1));

}

PHP_METHOD(Stub_FetchTest, testFetchArray2) {

	zephir_method_globals *ZEPHIR_METHOD_GLOBALS_PTR = NULL;
	zval *a = NULL, a_sub, *b, b_sub;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&a_sub);
	ZVAL_UNDEF(&b_sub);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 2, 0, &a, &b);

	ZEPHIR_SEPARATE_PARAM(a);


	ZEPHIR_OBS_NVAR(a);
	RETURN_MM_BOOL(zephir_array_isset_fetch(a, a, b, 0));

}

PHP_METHOD(Stub_FetchTest, testFetchArray3) {

	zend_long b;
	zval *a, a_sub, *b_param = NULL, c;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&a_sub);
	ZVAL_UNDEF(&c);

	zephir_fetch_params_without_memory_grow(2, 0, &a, &b_param);

	b = zephir_get_intval(b_param);


	RETURN_BOOL(zephir_array_isset_long_fetch(&c, a, b, 1));

}

PHP_METHOD(Stub_FetchTest, testFetchArray4) {

	zephir_method_globals *ZEPHIR_METHOD_GLOBALS_PTR = NULL;
	zend_long b;
	zval *a = NULL, a_sub, *b_param = NULL;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&a_sub);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 2, 0, &a, &b_param);

	ZEPHIR_SEPARATE_PARAM(a);
	b = zephir_get_intval(b_param);


	ZEPHIR_OBS_NVAR(a);
	RETURN_MM_BOOL(zephir_array_isset_long_fetch(a, a, b, 0));

}

PHP_METHOD(Stub_FetchTest, testFetchArray5) {

	zephir_method_globals *ZEPHIR_METHOD_GLOBALS_PTR = NULL;
	zval b;
	zval *a, a_sub, *b_param = NULL, c;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&a_sub);
	ZVAL_UNDEF(&c);
	ZVAL_UNDEF(&b);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 2, 0, &a, &b_param);

	zephir_get_strval(&b, b_param);


	RETURN_MM_BOOL(zephir_array_isset_fetch(&c, a, &b, 1));

}

PHP_METHOD(Stub_FetchTest, testFetchArray6) {

	zephir_method_globals *ZEPHIR_METHOD_GLOBALS_PTR = NULL;
	zval b;
	zval *a = NULL, a_sub, *b_param = NULL;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&a_sub);
	ZVAL_UNDEF(&b);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 2, 0, &a, &b_param);

	ZEPHIR_SEPARATE_PARAM(a);
	zephir_get_strval(&b, b_param);


	ZEPHIR_OBS_NVAR(a);
	RETURN_MM_BOOL(zephir_array_isset_fetch(a, a, &b, 0));

}

PHP_METHOD(Stub_FetchTest, testFetchObject1) {

	zephir_method_globals *ZEPHIR_METHOD_GLOBALS_PTR = NULL;
	zval *a, a_sub, *b, b_sub, c;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&a_sub);
	ZVAL_UNDEF(&b_sub);
	ZVAL_UNDEF(&c);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 2, 0, &a, &b);



	ZEPHIR_OBS_VAR(&c);
	RETURN_MM_BOOL(zephir_fetch_property_zval(&c, a, b, PH_SILENT_CC));

}

PHP_METHOD(Stub_FetchTest, testFetchObject2) {

	zephir_method_globals *ZEPHIR_METHOD_GLOBALS_PTR = NULL;
	zval *a = NULL, a_sub, *b, b_sub;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&a_sub);
	ZVAL_UNDEF(&b_sub);

	ZEPHIR_MM_GROW();
	zephir_fetch_params(1, 2, 0, &a, &b);

	ZEPHIR_SEPARATE_PARAM(a);


	ZEPHIR_OBS_NVAR(a);
	RETURN_MM_BOOL(zephir_fetch_property_zval(a, a, b, PH_SILENT_CC));

}

PHP_METHOD(Stub_FetchTest, testFetchPost) {

	zval *b, b_sub, _POST, c;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&b_sub);
	ZVAL_UNDEF(&_POST);
	ZVAL_UNDEF(&c);

	zephir_get_global(&_POST, SL("_POST"));
	zephir_fetch_params_without_memory_grow(1, 0, &b);



	if (!(zephir_array_isset_fetch(&c, &_POST, b, 1))) {
		RETURN_BOOL(0);
	}
	RETURN_CTORW(&c);

}

PHP_METHOD(Stub_FetchTest, hasValue) {

	zephir_method_globals *ZEPHIR_METHOD_GLOBALS_PTR = NULL;
	zval *name_param = NULL, _POST, _0$$4;
	zval name;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&name);
	ZVAL_UNDEF(&_POST);
	ZVAL_UNDEF(&_0$$4);

	ZEPHIR_MM_GROW();
	zephir_get_global(&_POST, SL("_POST"));
	zephir_fetch_params(1, 1, 0, &name_param);

	zephir_get_strval(&name, name_param);


	if (zephir_array_isset(&_POST, &name)) {
		RETURN_MM_BOOL(1);
	} else {
		zephir_read_property(&_0$$4, this_ptr, ZEND_STRL("values"), PH_NOISY_CC | PH_READONLY);
		if (zephir_array_isset(&_0$$4, &name)) {
			RETURN_MM_BOOL(1);
		}
	}
	RETURN_MM_BOOL(0);

}

PHP_METHOD(Stub_FetchTest, getValue) {

	zephir_method_globals *ZEPHIR_METHOD_GLOBALS_PTR = NULL;
	zval *name_param = NULL, _POST, value, _0$$3;
	zval name;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&name);
	ZVAL_UNDEF(&_POST);
	ZVAL_UNDEF(&value);
	ZVAL_UNDEF(&_0$$3);

	ZEPHIR_MM_GROW();
	zephir_get_global(&_POST, SL("_POST"));
	zephir_fetch_params(1, 1, 0, &name_param);

	zephir_get_strval(&name, name_param);


	ZEPHIR_OBS_VAR(&value);
	if (!(zephir_array_isset_fetch(&value, &_POST, &name, 0))) {
		ZEPHIR_OBS_NVAR(&value);
		zephir_read_property(&_0$$3, this_ptr, ZEND_STRL("values"), PH_NOISY_CC | PH_READONLY);
		if (!(zephir_array_isset_fetch(&value, &_0$$3, &name, 0))) {
			RETURN_MM_NULL();
		}
	}
	RETURN_CCTOR(&value);

}

