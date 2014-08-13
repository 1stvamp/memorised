"""memorised module - container for the memorise python-memcache decorator"""
__author__ = 'Wes Mason <wes [at] 1stvamp [dot] org>'
__docformat__ = 'restructuredtext en'
__version__ = '1.0.1'

from functools import wraps
from hashlib import md5
import inspect
import itertools

import memcache

from memorised import compat


class memorise(object):
        """Decorate any function or class method/staticmethod with a memcace
        enabled caching wrapper. Similar to the memoise pattern, this will push
        mutator operators into memcache.Client.set(), and pull accessor
        operations from memcache.Client.get().
        An MD5 hash of values, such as attributes on the parent instance/class,
        and arguements, is used as a unique key in memcache.

        :Parameters:
          `mc` : memcache.Client
            The memcache client instance to use.
          `mc_servers` : list
            A list of memcache servers to use in the cluster.
          `parent_keys` : list
            A list of attributes in the parent instance or class to use for
            key hashing.
          `set` : string
            An attribute present in the parent instance or class to set
            to the same value as the cached return value. Handy for keeping
            models in line if attributes are accessed directly in other
            places, or for pickling instances.
          `ttl` : integer
            Tells memcached the time which this value should expire.
            We default to 0 == cache forever. None is turn off caching.
          `update` : boolean
            If `invalidate` is False, Refresh ttl value in cache.
            If `invalidate` is True, set the cache value to `value`
          `invalidate` : boolean
            Invalidates key
          `value` : object
            used only if invalidate == True and update == True
            set the cached value to `value`
        """

        def __init__(self, mc=None, mc_servers=None, parent_keys=[], set=None, ttl=0, update=False,
                     invalidate=False, value=None):
                # Instance some default values, and customisations
                self.parent_keys = parent_keys
                self.set = set
                self.ttl = ttl
                self.update = update
                self.invalidate = invalidate
                self.value = value

                if not mc:
                        if not mc_servers:
                                mc_servers = ['localhost:11211']
                        self.mc = memcache.Client(mc_servers, debug=0)
                else:
                        self.mc = mc

        def __call__(self, fn):
                @wraps(fn)
                def wrapper(*args, **kwargs):
                        key = self.key(fn, args, kwargs)
                        if self.mc:
                                # Try and get the value from memcache
                                if self.invalidate and self.update:
                                    output = self.value
                                else:
                                    output = (not self.invalidate) and self.get_cache(key)
                                exist = True
                                if output is None:
                                        exist = False
                                        # Otherwise get the value from
                                        # the function/method
                                        output = self.call_function(fn, args, kwargs)
                                if self.update or not exist:
                                        if output is None:
                                                set_value = memcache_none()
                                        else:
                                                set_value = output
                                        self.set_cache(key, set_value)
                                if output.__class__ is memcache_none:
                                        # Because not-found keys return
                                        # a None value, we use the
                                        # memcache_none stub class to
                                        # detect these, and make a
                                        # distinction between them and
                                        # actual None values
                                        output = None
                                if self.set:
                                        # Set an attribute of the parent
                                        # instance/class to the output value,
                                        # this can help when other code
                                        # accesses attribures directly, or you
                                        # want to pickle the instance
                                        set_attr = getattr(fn.__class__, self.set)
                                        set_attr = output

                        else:
                                # No memcache client instance available, just
                                # return the output of the method
                                output = self.call_function(fn, args, kwargs)
                        return output
                return wrapper

        def call_function(self, fn, args, kwargs):
            return fn(*args, **kwargs)

        def key(self, fn, args, kwargs):
                # Get a list of arguement names from the func_code
                # attribute on the function/method instance, so we can
                # test for the presence of self or cls, as decorator
                # wrapped instances lose frame and no longer contain a
                # reference to their parent instance/class within this
                # frame
                func_code = compat.get_function_code(fn)
                argnames = func_code.co_varnames[:func_code.co_argcount]
                method = False
                static = False
                if len(argnames) > 0:
                        if argnames[0] == 'self' or argnames[0] == 'cls':
                                method = True
                                if argnames[0] == 'cls':
                                        static = True

                arg_values_hash = []
                # Grab all the keyworded and non-keyworded arguements so
                # that we can use them in the hashed memcache key
                for i, v in sorted(itertools.chain(compat.izip(argnames, args),
                                                   compat.iteritems(kwargs))):
                        if i != 'self':
                                if i != 'cls':
                                        arg_values_hash.append("%s=%s" % (i, v))

                class_name = None
                if method:
                        keys = []
                        if len(self.parent_keys) > 0:
                                for key in self.parent_keys:
                                        keys.append("%s=%s" % (key, getattr(args[0], key)))
                        keys = ','.join(keys)
                        if static:
                                # Get the class name from the cls argument
                                class_name = args[0].__name__
                        else:
                                # Get the class name from the self argument
                                class_name = args[0].__class__.__name__
                        module_name = inspect.getmodule(args[0]).__name__
                        parent_name = "%s.%s[%s]::" % (module_name, class_name, keys)
                else:
                        # Function passed in, use the module name as the
                        # parent
                        parent_name = inspect.getmodule(fn).__name__
                # Create a unique hash of the function/method call
                key = "%s%s(%s)" % (parent_name, fn.__name__, ",".join(arg_values_hash))
                key = key.encode('utf8') if isinstance(key, compat.text_type) else key
                key = md5(key).hexdigest()
                return key

        def get_cache(self, key):
            return self.mc.get(key)

        def set_cache(self, key, value):
            if self.ttl is not None:
                    self.mc.set(key, value, time=self.ttl)
            else:
                    self.mc.set(key, value)


class memcache_none:
        """Stub class for storing None values in memcache,
        so we can distinguish between None values and not-found
        entries.
        """
        pass


if __name__ == '__main__':
        # Run unit tests
        from memorised import tests
        tests.run()
