About memorised
===============

``memorised`` is a python module containing handy ``python-memcached``
decorators and utils.
Specifically the ``memorise`` decorator allows you to quickly and simply
add memcache caching to any function or method.

Installation
============

Install ``memorised`` using the supplied setup.py::

    python setup.py

Or install ``memorised`` using easy_install::

    easy_install memorised

Usage
=====

To cache a simple unbound function, just include the ``@memorise()`` tag to the
function definition (the paranthesis are needed as the decorator needs to be
initialised at the time of binding to handle ``memorise`` specific arguements)::

    @memorise()
    def myfunction():
        return 'hello world'

You can do the same for simple instance and class methos, however for most
instance methods, e.g. when caching results for database models, you probably
want to include some form of identity to single out a method call on one
instance from another instance. You can do this by providing a list of one ore
more `parent keys`, these are the names of attributes in the parent instance
that you want to be appended to the memcache key::

    class MyModel:
        id = 1

        @memorise(parent_keys=['id'])
        def get_stats():
            return blah()

You may also wish to keep attributes in line with the value retrieved from
memcache, especially if those attributes are `pickled` or accessed directly
by other code. To do this just pass the name of the attribute to update
as a string via the ``set`` arguement::

    class MyModel:
    	def __init__(self):
	    self.a = None

        @memorise(set='a')
        def get_a():
            return self.a

``memorise()`` supports dependancy injection for the python-memcached Client
instance by passing in the ``mc`` arguement::

    mc = memcached.Client(['localhost:11211'], debug=0)

    @memorise(mc=mc)
    def myfunction()
        return 'hello world'

Alternatively you can pass in a list of memcache servers, via the ``mc_servers``
arguement, to use a new Client instance, but to use same the server pool.

For other usage examples see the unittests in ``tests.py``.
Note: if you don't have memcache running at localhost:11211, you will need to
either start it or modify the test suite to use a different memcache Client
configuration, otherwise all the tests will fail.
