
extern zend_class_entry *stub_exists_ce;

ZEPHIR_INIT_CLASS(Stub_Exists);

PHP_METHOD(Stub_Exists, testClassExists);
PHP_METHOD(Stub_Exists, testInterfaceExists);
PHP_METHOD(Stub_Exists, testMethodExists);
PHP_METHOD(Stub_Exists, testFileExists);

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_stub_exists_testclassexists, 0, 1, _IS_BOOL, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_stub_exists_testclassexists, 0, 1, _IS_BOOL, NULL, 0)
#endif
	ZEND_ARG_INFO(0, className)
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, autoload, _IS_BOOL, 0)
#else
	ZEND_ARG_INFO(0, autoload)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_stub_exists_testinterfaceexists, 0, 1, _IS_BOOL, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_stub_exists_testinterfaceexists, 0, 1, _IS_BOOL, NULL, 0)
#endif
	ZEND_ARG_INFO(0, interfaceName)
#if PHP_VERSION_ID >= 70200
	ZEND_ARG_TYPE_INFO(0, autoload, _IS_BOOL, 0)
#else
	ZEND_ARG_INFO(0, autoload)
#endif
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_stub_exists_testmethodexists, 0, 2, _IS_BOOL, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_stub_exists_testmethodexists, 0, 2, _IS_BOOL, NULL, 0)
#endif
	ZEND_ARG_INFO(0, obj)
	ZEND_ARG_INFO(0, methodName)
ZEND_END_ARG_INFO()

#if PHP_VERSION_ID >= 70200
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_stub_exists_testfileexists, 0, 1, _IS_BOOL, 0)
#else
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_stub_exists_testfileexists, 0, 1, _IS_BOOL, NULL, 0)
#endif
	ZEND_ARG_INFO(0, fileName)
ZEND_END_ARG_INFO()

ZEPHIR_INIT_FUNCS(stub_exists_method_entry) {
	PHP_ME(Stub_Exists, testClassExists, arginfo_stub_exists_testclassexists, ZEND_ACC_PUBLIC)
	PHP_ME(Stub_Exists, testInterfaceExists, arginfo_stub_exists_testinterfaceexists, ZEND_ACC_PUBLIC)
	PHP_ME(Stub_Exists, testMethodExists, arginfo_stub_exists_testmethodexists, ZEND_ACC_PUBLIC)
	PHP_ME(Stub_Exists, testFileExists, arginfo_stub_exists_testfileexists, ZEND_ACC_PUBLIC)
	PHP_FE_END
};
