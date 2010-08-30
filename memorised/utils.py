"""memorised module - container for the memorise python-memcache decorator"""
__author__ = 'Wes Mason <wes [at] 1stvamp [dot] org>'
__docformat__ = 'restructuredtext en'
__version__ = '1.0.1'

import memcache
from hashlib import md5
import inspect

def uncache(fn, mc=None, mc_servers=None, parent_keys=[]):
        if not mc:
                if not mc_servers:
                        mc_servers = ['localhost:11211']
                mc = memcache.Client(mc_servers, debug=0)
        def wrapper(*args, **kwargs):
                argnames = fn.func_code.co_varnames[:fn.func_code.co_argcount]
                method = False
                static = False
                if hasattr(fn, 'im_self'):
                        method = True
                        if inspect.isclass(fn.im_self):
                                static = True

                arg_values_hash = []
                # Grab all the keyworded and non-keyworded arguements so
                # that we can use them in the hashed memcache key
                for i,v in (zip(argnames, args) + kwargs.items()):
                        arg_values_hash.append("%s=%s" % (i,v))

                class_name = None
                if method:
                        keys = []
                        if len(parent_keys) > 0:
                                for key in parent_keys:
                                        keys.append("%s=%s" % (key, getattr(fn.im_self, key)))
                        keys = ','.join(keys)
                        # Get the class name from the self argument
                        if inspect.isclass(fn.im_self):
                                class_name = fn.im_self.__name__
                        else:
                                class_name = fn.im_self.__class__.__name__
                        module_name = inspect.getmodule(fn.im_self).__name__
                        parent_name = "%s.%s[%s]::" % (module_name, class_name, keys)
                else:
                        # Function passed in, use the module name as the
                        # parent
                        parent_name = inspect.getmodule(fn).__name__
                # Create a unique hash of the function/method call
                key = "%s%s(%s)" % (parent_name, fn.__name__, ",".join(arg_values_hash))
                key = md5(key).hexdigest()

                if mc:
                        mc.delete(key)
                        return True
                else:
                        return False
        return wrapper
