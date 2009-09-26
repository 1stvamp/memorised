"""memorised module - container for the memorise python-memcache decorator"""
__author__ = 'Wes Mason <wes [at] 1stvamp [dot] org>'
__docformat__ = 'restructuredtext en'

import memcache
from hashlib import md5
import pickle
from functools import wraps

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
        """

        def __init__(self, mc=None, mc_servers=None, parent_keys=[]):
		# Instance some default values, and customisations
                self.parent_keys = parent_keys
                if not mc:
                        if not mc_servers:
                                mc_servers = ['localhost:11211']
                        self.mc = memcache.Client(mc_servers, debug=0)
                else:
                        self.mc = mc

        def __call__(self, fn):
                @wraps(fn)
                def wrapper(*args, **kwargs):
			# Get a list of arguement names from the func_code
			# attribute on the function/method instance, so we can
			# test for the presence of self or cls, as decorator
			# wrapped instances lose frame and no longer contain a
			# reference to their parent instance/class within this
			# frame
                        argnames = fn.func_code.co_varnames[:fn.func_code.co_argcount]
                        method = False
			static = False
                        if argnames[0] == 'self' or argnames[0] == 'cls':
                                method = True
				if argnames[0] == 'cls':
					static = True

                        arg_values_hash = []
			# Grab all the keyworded and non-keyworded arguements so
			# that we can use them in the hashed memcache key
                        for i,v in (zip(argnames, args) + kwargs.items()):
                                if i != 'self':
                                        arg_values_hash.append("%s=%s" % (i,v))

                        if method:
                                keys = []
                                if len(self.parent_keys) > 0:
                                        for key in self.parent_keys:
                                                keys.append("%s=%s" % (key, getattr(args[0], key)))
                                        keys = ','.join(keys)
				if static:
                                # Get the class name from the cls argument
					class_name = arg[0]
				else:
                                # Get the class name from the self argument
					class_name = args[0].__class__.__name__
                                parent_name = "%s[%s]::" % (class_name, keys)
                        else:
                                parent_name = ''
			# Create a unique hash of the function/method call
                        key = "%s%s(%s)" % (parent_name, fn.__name__, ",".join(arg_values_hash))
                        key = md5(key).hexdigest()
			print "key: %s" % key

                        if self.mc:
				# Try and get the value from memcache
				output = self.mc.get(key)
				if not output:
					print "Not found in mc, getting value"
					# Otherwise get the value from
					# the function/method
					output = fn(*args, **kwargs)
					if output is None:
						set_value = memcache_none()
					else:
						set_value = output
					print "got '%s'" % set_value
					# And push it into memcache
					self.mc.set(key, set_value)
				if output.__class__ is memcache_none:
					# Because not-found keys return
					# a None value, we use the
					# memcache_none stub class to
					# detect these, and make a
					# distinction between them and
					# actual None values
					print "found memcache_none, returning None"
					output = None
				print "returning '%s' from mc" % output

                        else :
				# No memcache client instance available, just
				# return the output of the method
				print "no mc found"
                                output = fn(*args, **kwargs)
                        return output
                return wrapper

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

