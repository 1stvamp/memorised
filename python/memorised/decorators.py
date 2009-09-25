import memcache
from hashlib import md5
import pickle
from functools import wraps

class memorise(object):
	"""
	"""

        def __init__(self, mc=None, mc_servers=None, set_key=None, parent_keys=[]):
		# Instance some default values, and customisations
                self.set_key = set_key
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
                        getter = False
                        method = False
			static = False
                        if argnames[0] == 'self' or argnames[0] == 'cls':
                                method = True
				if argnames[0] == 'cls':
					static = True
                                if len(args) == 1:
                                        getter = True
                        else:
                                if len(args) == 0:
                                        getter = True

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

                        if self.mc:
                                if getter:
					# Try and get the value from memcache
                                        output = self.mc.get(key)
                                        if not output:
						# Otherwise get the value from
						# the function/method
                                                output = fn(*args, **kwargs)
						# And push it into memcache
                                                self.mc.set(key, output)
                                        if output.__class__ is memcache_none:
						# Because not-found keys return
						# a None value, we use the
						# memcache_none stub class to
						# detect these, and make a
						# distinction between them and
						# actual None values
                                                output = None
                                else:
					# Methods will include a 'self' arg and
					# staticmethods will include a 'cls'
					# arg, in this case skip it to get the
					# other args
                                        if method:
                                                offset = 1
                                        else:
                                                offset = 0
                                        set_value = False
					# Use self.set_key if a custom arg has
					# been defined by this set operation
                                        if self.set_key:
                                                if len(args) > offset:
							# Setter arguement passed
							# in without a keyword,
							# try to get it from *args
                                                        arg_index = argnames.index(self.set_key)
                                                        set_value = args[arg_index]
                                                if set_value == False:
							# Setter arguement
							# passed in using
							# keyword
                                                        if len(kwargs) > 0:
                                                                set_value = kwargs.get(self.set_key)
                                        else:
						# No custom arg defined for
						# setter value, so just try to
						# use the first one
                                                if len(args) > offset:
                                                        set_value = args[offset]
                                                else:
                                                        if len(kwargs) > 0:
                                                                set_value = kwargs.iteritems().pop(0)

                                        if set_value is not False:
                                                if set_value is None:
							# None value being set,
							# use special
							# memcache_none type to
							# store this
                                                        set_value = memcache_none()
					# Get the output of th setter when we
					# call it in case it returns a value
                                        output = fn(*args, **kwargs)
					# Push the setter value (not the return
					# value) into memcache
                                        self.mc.set(key, set_value)

                        else :
				# No memcache client instance available, just
				# return the output of the method
                                return fn(*args, **kwargs)
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

