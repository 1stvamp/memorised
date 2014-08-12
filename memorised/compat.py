"""
Compatibility utilities for running Python 2 and 3 from the same codebase.
"""

import operator
import sys


PY3 = sys.version_info[0] == 3


if PY3:
    text_type = str

    def iteritems(d, **kw):
        return iter(d.items(**kw))

    izip = zip
    _meth_self = "__self__"
    _func_code = "__code__"
else:
    text_type = unicode

    def iteritems(d, **kw):
        return iter(d.iteritems(**kw))

    from itertools import izip
    _meth_self = "im_self"
    _func_code = "func_code"


get_method_self = operator.attrgetter(_meth_self)
get_function_code = operator.attrgetter(_func_code)
