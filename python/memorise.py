import memcache
from functools import wraps

class memorise(object):
        def __init__(self, fn=None, mc=None, mc_servers=None, set_key=None):
                self.fn = fn
                self.set_key = set_key
                if not mc:
                        if not mc_servers:
                                mc_servers = ['localhost:11211']
                        self.mc = memcache.Client(mc_servers, debug=0)
                else:
                        self.mc = mc
                        
        def __call__(self, fn_self_or_fn=None, *args, **kwargs):
                first_arg = None
                if self.fn:
                        fn = self.fn
                        first_arg = fn_self_or_fn
                else:
                        fn = fn_self_or_fn
                argnames = fn.func_code.co_varnames[:fn.func_code.co_argcount]
                getter = False
                method = False
                if argnames[0] == 'self':
                        method = True
                        if len(args) == 1:
                                getter = True
                else:
                        if len(args) == 0:
                                getter = True

                start = 0
                arg_values_hash = []
                for i,v in (zip(argnames, args) + kwargs.items()):
                        if i != 'self':
                                arg_values_hash.append("%s=%s" % (i,v))

                if method:
                        # Get the class name from the self argument
                        try:
                                fn_self = args[0]
                        except IndexError:
                                fn_self = first_arg
                        parent_name = "%s::" % fn_self.__class__.__name__
                else:
                        parent_name = ''
                key = "%s%s(%s)" % (parent_name, fn.__name__, ",".join(arg_values_hash))
                print "key: %s" % key
                if self.mc:
                        if getter:
                                output = self.mc.get(key)
                                if not output:
                                        output = fn(*args, **kwargs)
                                        self.mc.set(key, output)
                                if output.__class__ is memcache_none:
                                        output = None
                        else:
                                if method:
                                        offset = 1
                                else:
                                        offset = 0
                                set_value = False
                                if self.set_key:
                                        if len(args) > offset:
                                                arg_index = argnames.index(self.set_key)
                                                set_value = args[arg_index]
                                        if set_value == False:
                                                if len(kwargs) > 0:
                                                        set_value = kwargs.get(self.set_key)
                                else:
                                        if len(args) > 1:
                                                set_value = args[1]
                                        else:
                                                if len(kwargs) > 0:
                                                        set_value = kwargs.iteritems().pop(0)

                                if set_value is not False:
                                        if set_value is None:
                                                set_value = memcache_none()
                                output = fn(*args, **kwargs)
                                self.mc.set(key, set_value)

                else :
                        return fn(*args, **kwargs)
                return output

class memcache_none:
        """Stub class for storing None values in memcache,
        so we can distinguish between None values and not-found
        entries.
        """
        pass


class Test:
        def __init__(self):
                self.t = None

        
        @memorise(set_key='t')
        def set_t(self, t):
                self.t = t
                return True

        @memorise
        def get_t(self):
                return self.t


test = Test()
print test.set_t(t='test')
print test.get_t()
