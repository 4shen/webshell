
#ifdef HAVE_CONFIG_H
#include "../../../../ext_config.h"
#endif

#include <php.h>
#include "../../../../php_ext.h"
#include "../../../../ext.h"

#include <Zend/zend_operators.h>
#include <Zend/zend_exceptions.h>
#include <Zend/zend_interfaces.h>

#include "kernel/main.h"
#include "ext/spl/spl_heap.h"


ZEPHIR_INIT_CLASS(Stub_Oo_Extend_Spl_PriorityQueue) {

	ZEPHIR_REGISTER_CLASS_EX(Stub\\Oo\\Extend\\Spl, PriorityQueue, stub, oo_extend_spl_priorityqueue, spl_ce_SplPriorityQueue, NULL, 0);

	return SUCCESS;

}

