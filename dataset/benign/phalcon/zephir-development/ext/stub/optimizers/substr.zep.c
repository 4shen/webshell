
#ifdef HAVE_CONFIG_H
#include "../../ext_config.h"
#endif

#include <php.h>
#include "../../php_ext.h"
#include "../../ext.h"

#include <Zend/zend_operators.h>
#include <Zend/zend_exceptions.h>
#include <Zend/zend_interfaces.h>

#include "kernel/main.h"
#include "kernel/operators.h"
#include "kernel/string.h"
#include "kernel/memory.h"
#include "kernel/object.h"


ZEPHIR_INIT_CLASS(Stub_Optimizers_Substr) {

	ZEPHIR_REGISTER_CLASS(Stub\\Optimizers, Substr, stub, optimizers_substr, stub_optimizers_substr_method_entry, 0);

	return SUCCESS;

}

PHP_METHOD(Stub_Optimizers_Substr, testTwoArguments) {

	zval *str, str_sub, *start, start_sub;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&str_sub);
	ZVAL_UNDEF(&start_sub);

	zephir_fetch_params_without_memory_grow(2, 0, &str, &start);



	zephir_substr(return_value, str, zephir_get_intval(start), 0, ZEPHIR_SUBSTR_NO_LENGTH);
	return;

}

PHP_METHOD(Stub_Optimizers_Substr, testThreeArguments) {

	zval *str, str_sub, *start, start_sub, *offset, offset_sub;
	zval *this_ptr = getThis();

	ZVAL_UNDEF(&str_sub);
	ZVAL_UNDEF(&start_sub);
	ZVAL_UNDEF(&offset_sub);

	zephir_fetch_params_without_memory_grow(3, 0, &str, &start, &offset);



	zephir_substr(return_value, str, zephir_get_intval(start), zephir_get_intval(offset), 0);
	return;

}

